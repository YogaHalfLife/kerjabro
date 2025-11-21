<?php

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Input;

use League\CommonMark\Exception\UnexpectedEncodingException;

class MarkdownInput implements MarkdownInputInterface
{
    /**
     * @var array<int, string>|null
     *
     * @psalm-readonly-allow-private-mutation
     */
    private ?array $lines = null;

    /** @psalm-readonly-allow-private-mutation */
    private string $content;

    /** @psalm-readonly-allow-private-mutation */
    private ?int $lineCount = null;

    /** @psalm-readonly */
    private int $lineOffset;

    public function __construct(string $content, int $lineOffset = 0)
    {
        if (! \mb_check_encoding($content, 'UTF-8')) {
            throw new UnexpectedEncodingException('Unexpected encoding - UTF-8 or ASCII was expected');
        }
        if (\substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $content = \substr($content, 3);
        }

        $this->content    = $content;
        $this->lineOffset = $lineOffset;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * {@inheritDoc}
     */
    public function getLines(): iterable
    {
        $this->splitLinesIfNeeded();

        \assert($this->lines !== null);

        /** @psalm-suppress PossiblyNullIterator */
        foreach ($this->lines as $i => $line) {
            yield $this->lineOffset + $i + 1 => $line;
        }
    }

    public function getLineCount(): int
    {
        $this->splitLinesIfNeeded();

        \assert($this->lineCount !== null);

        return $this->lineCount;
    }

    private function splitLinesIfNeeded(): void
    {
        if ($this->lines !== null) {
            return;
        }

        $lines = \preg_split('/\r\n|\n|\r/', $this->content);
        if ($lines === false) {
            throw new UnexpectedEncodingException('Failed to split Markdown content by line');
        }

        $this->lines = $lines;
        if (\end($this->lines) === '') {
            \array_pop($this->lines);
        }

        $this->lineCount = \count($this->lines);
    }
}
