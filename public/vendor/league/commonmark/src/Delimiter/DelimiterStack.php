<?php

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (https://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * Additional emphasis processing code based on commonmark-java (https://github.com/atlassian/commonmark-java)
 *  - (c) Atlassian Pty Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Delimiter;

use League\CommonMark\Delimiter\Processor\DelimiterProcessorCollection;
use League\CommonMark\Node\Inline\AdjacentTextMerger;

final class DelimiterStack
{
    /** @psalm-readonly-allow-private-mutation */
    private ?DelimiterInterface $top = null;

    public function push(DelimiterInterface $newDelimiter): void
    {
        $newDelimiter->setPrevious($this->top);

        if ($this->top !== null) {
            $this->top->setNext($newDelimiter);
        }

        $this->top = $newDelimiter;
    }

    private function findEarliest(?DelimiterInterface $stackBottom = null): ?DelimiterInterface
    {
        $delimiter = $this->top;
        while ($delimiter !== null && $delimiter->getPrevious() !== $stackBottom) {
            $delimiter = $delimiter->getPrevious();
        }

        return $delimiter;
    }

    public function removeDelimiter(DelimiterInterface $delimiter): void
    {
        if ($delimiter->getPrevious() !== null) {
            /** @psalm-suppress PossiblyNullReference */
            $delimiter->getPrevious()->setNext($delimiter->getNext());
        }

        if ($delimiter->getNext() === null) {
            $this->top = $delimiter->getPrevious();
        } else {
            /** @psalm-suppress PossiblyNullReference */
            $delimiter->getNext()->setPrevious($delimiter->getPrevious());
        }
    }

    private function removeDelimiterAndNode(DelimiterInterface $delimiter): void
    {
        $delimiter->getInlineNode()->detach();
        $this->removeDelimiter($delimiter);
    }

    private function removeDelimitersBetween(DelimiterInterface $opener, DelimiterInterface $closer): void
    {
        $delimiter = $closer->getPrevious();
        while ($delimiter !== null && $delimiter !== $opener) {
            $previous = $delimiter->getPrevious();
            $this->removeDelimiter($delimiter);
            $delimiter = $previous;
        }
    }

    public function removeAll(?DelimiterInterface $stackBottom = null): void
    {
        while ($this->top && $this->top !== $stackBottom) {
            $this->removeDelimiter($this->top);
        }
    }

    public function removeEarlierMatches(string $character): void
    {
        $opener = $this->top;
        while ($opener !== null) {
            if ($opener->getChar() === $character) {
                $opener->setActive(false);
            }

            $opener = $opener->getPrevious();
        }
    }

    /**
     * @param string|string[] $characters
     */
    public function searchByCharacter($characters): ?DelimiterInterface
    {
        if (! \is_array($characters)) {
            $characters = [$characters];
        }

        $opener = $this->top;
        while ($opener !== null) {
            if (\in_array($opener->getChar(), $characters, true)) {
                break;
            }

            $opener = $opener->getPrevious();
        }

        return $opener;
    }

    public function processDelimiters(?DelimiterInterface $stackBottom, DelimiterProcessorCollection $processors): void
    {
        $openersBottom = [];
        $closer = $this->findEarliest($stackBottom);
        while ($closer !== null) {
            $delimiterChar = $closer->getChar();

            $delimiterProcessor = $processors->getDelimiterProcessor($delimiterChar);
            if (! $closer->canClose() || $delimiterProcessor === null) {
                $closer = $closer->getNext();
                continue;
            }

            $openingDelimiterChar = $delimiterProcessor->getOpeningCharacter();

            $useDelims            = 0;
            $openerFound          = false;
            $potentialOpenerFound = false;
            $opener               = $closer->getPrevious();
            while ($opener !== null && $opener !== $stackBottom && $opener !== ($openersBottom[$delimiterChar] ?? null)) {
                if ($opener->canOpen() && $opener->getChar() === $openingDelimiterChar) {
                    $potentialOpenerFound = true;
                    $useDelims            = $delimiterProcessor->getDelimiterUse($opener, $closer);
                    if ($useDelims > 0) {
                        $openerFound = true;
                        break;
                    }
                }

                $opener = $opener->getPrevious();
            }

            if (! $openerFound) {
                if (! $potentialOpenerFound) {
                    $openersBottom[$delimiterChar] = $closer->getPrevious();
                    if (! $closer->canOpen()) {
                        $this->removeDelimiter($closer);
                    }
                }

                $closer = $closer->getNext();
                continue;
            }

            \assert($opener !== null);

            $openerNode = $opener->getInlineNode();
            $closerNode = $closer->getInlineNode();
            $opener->setLength($opener->getLength() - $useDelims);
            $closer->setLength($closer->getLength() - $useDelims);

            $openerNode->setLiteral(\substr($openerNode->getLiteral(), 0, -$useDelims));
            $closerNode->setLiteral(\substr($closerNode->getLiteral(), 0, -$useDelims));

            $this->removeDelimitersBetween($opener, $closer);
            AdjacentTextMerger::mergeTextNodesBetweenExclusive($openerNode, $closerNode);
            $delimiterProcessor->process($openerNode, $closerNode, $useDelims);
            if ($opener->getLength() === 0) {
                $this->removeDelimiterAndNode($opener);
            }
            if ($closer->getLength() === 0) {
                $next = $closer->getNext();
                $this->removeDelimiterAndNode($closer);
                $closer = $next;
            }
        }
        $this->removeAll($stackBottom);
    }
}
