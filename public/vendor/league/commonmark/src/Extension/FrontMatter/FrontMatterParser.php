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

namespace League\CommonMark\Extension\FrontMatter;

use League\CommonMark\Extension\FrontMatter\Data\FrontMatterDataParserInterface;
use League\CommonMark\Extension\FrontMatter\Input\MarkdownInputWithFrontMatter;
use League\CommonMark\Parser\Cursor;

final class FrontMatterParser implements FrontMatterParserInterface
{
    /** @psalm-readonly */
    private FrontMatterDataParserInterface $frontMatterParser;

    private const REGEX_FRONT_MATTER = '/^---\\R.*?\\R---\\R/s';

    public function __construct(FrontMatterDataParserInterface $frontMatterParser)
    {
        $this->frontMatterParser = $frontMatterParser;
    }

    public function parse(string $markdownContent): MarkdownInputWithFrontMatter
    {
        $cursor = new Cursor($markdownContent);
        $frontMatter = $cursor->match(self::REGEX_FRONT_MATTER);
        if ($frontMatter === null) {
            return new MarkdownInputWithFrontMatter($markdownContent);
        }
        $frontMatter = \preg_replace('/---\R$/', '', $frontMatter);
        if ($frontMatter === null) {
            return new MarkdownInputWithFrontMatter($markdownContent);
        }
        $data = $this->frontMatterParser->parse($frontMatter);
        $trailingNewlines = $cursor->match('/^\R+/');
        $lineOffset = \preg_match_all('/\R/', $frontMatter . $trailingNewlines) + 1;

        return new MarkdownInputWithFrontMatter($cursor->getRemainder(), $lineOffset, $data);
    }
}
