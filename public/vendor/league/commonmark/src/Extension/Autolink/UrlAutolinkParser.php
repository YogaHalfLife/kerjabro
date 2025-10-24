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

namespace League\CommonMark\Extension\Autolink;

use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

final class UrlAutolinkParser implements InlineParserInterface
{
    private const ALLOWED_AFTER = [null, ' ', "\t", "\n", "\x0b", "\x0c", "\x0d", '*', '_', '~', '('];
    private const REGEX = '~
        (
            # Must start with a supported scheme + auth, or "www"
            (?:
                (?:%s)://                                 # protocol
                (?:([\.\pL\pN-]+:)?([\.\pL\pN-]+)@)?      # basic auth
            |www\.)
            (?:
                (?:[\pL\pN\pS\-\.])+(?:\.?(?:[\pL\pN]|xn\-\-[\pL\pN-]+)+\.?) # a domain name
                    |                                                 # or
                \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}                    # an IP address
                    |                                                 # or
                \[
                    (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
                \]  # an IPv6 address
            )
            (?::[0-9]+)?                              # a port (optional)
            (?:/ (?:[\pL\pN\-._\~!$&\'()*+,;=:@]|%%[0-9A-Fa-f]{2})* )*      # a path
            (?:\? (?:[\pL\pN\-._\~!$&\'()*+,;=:@/?]|%%[0-9A-Fa-f]{2})* )?   # a query (optional)
            (?:\# (?:[\pL\pN\-._\~!$&\'()*+,;=:@/?]|%%[0-9A-Fa-f]{2})* )?   # a fragment (optional)
        )~ixu';

    /**
     * @var string[]
     *
     * @psalm-readonly
     */
    private array $prefixes = ['www'];

    /** @psalm-readonly */
    private string $finalRegex;

    /**
     * @param array<int, string> $allowedProtocols
     */
    public function __construct(array $allowedProtocols = ['http', 'https', 'ftp'])
    {
        $this->finalRegex = \sprintf(self::REGEX, \implode('|', $allowedProtocols));

        foreach ($allowedProtocols as $protocol) {
            $this->prefixes[] = $protocol . '://';
        }
    }

    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::oneOf(...$this->prefixes);
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();
        $previousChar = $cursor->peek(-1);
        if (! \in_array($previousChar, self::ALLOWED_AFTER, true)) {
            return false;
        }
        if (! \preg_match($this->finalRegex, $cursor->getRemainder(), $matches)) {
            return false;
        }

        $url = $matches[0];
        if (\preg_match('/(.+)([?!.,:*_~]+)$/', $url, $matches)) {
            $url = $matches[1];
        }
        if (\preg_match('/(.+)(&[A-Za-z0-9]+;)$/', $url, $matches)) {
            $url = $matches[1];
        }
        if (\substr($url, -1) === ')' && ($diff = self::diffParens($url)) > 0) {
            $url = \substr($url, 0, -$diff);
        }

        $cursor->advanceBy(\mb_strlen($url));
        if (\substr($url, 0, 4) === 'www.') {
            $inlineContext->getContainer()->appendChild(new Link('http://' . $url, $url));

            return true;
        }

        $inlineContext->getContainer()->appendChild(new Link($url, $url));

        return true;
    }

    /**
     * @psalm-pure
     */
    private static function diffParens(string $content): int
    {
        \preg_match_all('/[()]/', $content, $matches);

        $charCount = ['(' => 0, ')' => 0];
        foreach ($matches[0] as $char) {
            $charCount[$char]++;
        }

        return $charCount[')'] - $charCount['('];
    }
}
