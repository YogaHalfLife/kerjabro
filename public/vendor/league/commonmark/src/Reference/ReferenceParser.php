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
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Reference;

use League\CommonMark\Parser\Cursor;
use League\CommonMark\Util\LinkParserHelper;

final class ReferenceParser
{
    private const START_DEFINITION = 0;
    private const LABEL = 1;
    private const DESTINATION = 2;
    private const START_TITLE = 3;
    private const TITLE = 4;
    private const PARAGRAPH = 5;

    /** @psalm-readonly-allow-private-mutation */
    private string $paragraph = '';

    /**
     * @var array<int, ReferenceInterface>
     *
     * @psalm-readonly-allow-private-mutation
     */
    private array $references = [];

    /** @psalm-readonly-allow-private-mutation */
    private int $state = self::START_DEFINITION;

    /** @psalm-readonly-allow-private-mutation */
    private ?string $label = null;

    /** @psalm-readonly-allow-private-mutation */
    private ?string $destination = null;

    /**
     * @var string string
     *
     * @psalm-readonly-allow-private-mutation
     */
    private string $title = '';

    /** @psalm-readonly-allow-private-mutation */
    private ?string $titleDelimiter = null;

    /** @psalm-readonly-allow-private-mutation */
    private bool $referenceValid = false;

    public function getParagraphContent(): string
    {
        return $this->paragraph;
    }

    /**
     * @return ReferenceInterface[]
     */
    public function getReferences(): iterable
    {
        $this->finishReference();

        return $this->references;
    }

    public function hasReferences(): bool
    {
        return $this->references !== [];
    }

    public function parse(string $line): void
    {
        if ($this->paragraph !== '') {
            $this->paragraph .= "\n";
        }

        $this->paragraph .= $line;

        $cursor = new Cursor($line);
        while (! $cursor->isAtEnd()) {
            $result = false;
            switch ($this->state) {
                case self::PARAGRAPH:
                    return;
                case self::START_DEFINITION:
                    $result = $this->parseStartDefinition($cursor);
                    break;
                case self::LABEL:
                    $result = $this->parseLabel($cursor);
                    break;
                case self::DESTINATION:
                    $result = $this->parseDestination($cursor);
                    break;
                case self::START_TITLE:
                    $result = $this->parseStartTitle($cursor);
                    break;
                case self::TITLE:
                    $result = $this->parseTitle($cursor);
                    break;
                default:
                    break;
            }

            if (! $result) {
                $this->state = self::PARAGRAPH;

                return;
            }
        }
    }

    private function parseStartDefinition(Cursor $cursor): bool
    {
        $cursor->advanceToNextNonSpaceOrTab();
        if ($cursor->isAtEnd() || $cursor->getCurrentCharacter() !== '[') {
            return false;
        }

        $this->state = self::LABEL;
        $this->label = '';

        $cursor->advance();
        if ($cursor->isAtEnd()) {
            $this->label .= "\n";
        }

        return true;
    }

    private function parseLabel(Cursor $cursor): bool
    {
        $cursor->advanceToNextNonSpaceOrTab();

        $partialLabel = LinkParserHelper::parsePartialLinkLabel($cursor);
        if ($partialLabel === null) {
            return false;
        }

        \assert($this->label !== null);
        $this->label .= $partialLabel;

        if ($cursor->isAtEnd()) {
            $this->label .= "\n";

            return true;
        }

        if ($cursor->getCurrentCharacter() !== ']') {
            return false;
        }

        $cursor->advance();
        if ($cursor->getCurrentCharacter() !== ':') {
            return false;
        }

        $cursor->advance();
        if (\mb_strlen($this->label, 'utf-8') > 999) {
            return false;
        }
        if (\trim($this->label) === '') {
            return false;
        }

        $cursor->advanceToNextNonSpaceOrTab();

        $this->state = self::DESTINATION;

        return true;
    }

    private function parseDestination(Cursor $cursor): bool
    {
        $cursor->advanceToNextNonSpaceOrTab();

        $destination = LinkParserHelper::parseLinkDestination($cursor);
        if ($destination === null) {
            return false;
        }

        $this->destination = $destination;

        $advanced = $cursor->advanceToNextNonSpaceOrTab();
        if ($cursor->isAtEnd()) {
            $this->referenceValid = true;
            $this->paragraph      = '';
        } elseif ($advanced === 0) {
            return false;
        }

        $this->state = self::START_TITLE;

        return true;
    }

    private function parseStartTitle(Cursor $cursor): bool
    {
        $cursor->advanceToNextNonSpaceOrTab();
        if ($cursor->isAtEnd()) {
            $this->state = self::START_DEFINITION;

            return true;
        }

        $this->titleDelimiter = null;
        switch ($c = $cursor->getCurrentCharacter()) {
            case '"':
            case "'":
                $this->titleDelimiter = $c;
                break;
            case '(':
                $this->titleDelimiter = ')';
                break;
            default:
                break;
        }

        if ($this->titleDelimiter !== null) {
            $this->state = self::TITLE;
            $cursor->advance();
            if ($cursor->isAtEnd()) {
                $this->title .= "\n";
            }
        } else {
            $this->finishReference();
            $this->state = self::START_DEFINITION;
        }

        return true;
    }

    private function parseTitle(Cursor $cursor): bool
    {
        \assert($this->titleDelimiter !== null);
        $title = LinkParserHelper::parsePartialLinkTitle($cursor, $this->titleDelimiter);

        if ($title === null) {
            return false;
        }
        $endDelimiterFound = false;
        if (\substr($title, -1) === $this->titleDelimiter) {
            $endDelimiterFound = true;
            $title = \substr($title, 0, -1);
        }

        $this->title .= $title;

        if (! $endDelimiterFound && $cursor->isAtEnd()) {
            $this->title .= "\n";

            return true;
        }
        $cursor->advanceToNextNonSpaceOrTab();
        if (! $cursor->isAtEnd()) {
            return false;
        }

        $this->referenceValid = true;
        $this->finishReference();
        $this->paragraph = '';
        $this->state = self::START_DEFINITION;

        return true;
    }

    private function finishReference(): void
    {
        if (! $this->referenceValid) {
            return;
        }

        /** @psalm-suppress PossiblyNullArgument -- these can't possibly be null if we're in this state */
        $this->references[] = new Reference($this->label, $this->destination, $this->title);

        $this->label          = null;
        $this->referenceValid = false;
        $this->destination    = null;
        $this->title          = '';
        $this->titleDelimiter = null;
    }
}
