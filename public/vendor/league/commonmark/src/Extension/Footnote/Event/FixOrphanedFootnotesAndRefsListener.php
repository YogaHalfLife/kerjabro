<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\CommonMark\Extension\Footnote\Event;

use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\Footnote\Node\Footnote;
use League\CommonMark\Extension\Footnote\Node\FootnoteRef;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Inline\Text;

final class FixOrphanedFootnotesAndRefsListener
{
    public function onDocumentParsed(DocumentParsedEvent $event): void
    {
        $document = $event->getDocument();
        $map      = $this->buildMapOfKnownFootnotesAndRefs($document);

        foreach ($map['_flat'] as $node) {
            if ($node instanceof FootnoteRef && ! isset($map[Footnote::class][$node->getReference()->getLabel()])) {
                $node->replaceWith(new Text(\sprintf('[^%s]', $node->getReference()->getLabel())));
            }
            if ($node instanceof Footnote && ! isset($map[FootnoteRef::class][$node->getReference()->getLabel()])) {
                $node->detach();
            }
        }
    }

    /** @phpstan-ignore-next-line */
    private function buildMapOfKnownFootnotesAndRefs(Document $document): array // @phpcs:ignore
    {
        $map = [
            Footnote::class => [],
            FootnoteRef::class => [],
            '_flat' => [],
        ];

        foreach ($document->iterator() as $node) {
            if ($node instanceof Footnote) {
                $map[Footnote::class][$node->getReference()->getLabel()] = true;

                $map['_flat'][] = $node;
            } elseif ($node instanceof FootnoteRef) {
                $map[FootnoteRef::class][$node->getReference()->getLabel()] = true;

                $map['_flat'][] = $node;
            }
        }

        return $map;
    }
}
