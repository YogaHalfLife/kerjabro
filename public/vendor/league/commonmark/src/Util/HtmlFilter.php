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

namespace League\CommonMark\Util;

/**
 * @psalm-immutable
 */
final class HtmlFilter
{
    public const ALLOW = 'allow';
    public const ESCAPE = 'escape';
    public const STRIP = 'strip';

    /**
     * Runs the given HTML through the given filter
     *
     * @param string $html   HTML input to be filtered
     * @param string $filter One of the HtmlFilter constants
     *
     * @return string Filtered HTML
     *
     * @throws \InvalidArgumentException when an invalid $filter is given
     *
     * @psalm-pure
     */
    public static function filter(string $html, string $filter): string
    {
        switch ($filter) {
            case self::STRIP:
                return '';
            case self::ESCAPE:
                return \htmlspecialchars($html, \ENT_NOQUOTES);
            case self::ALLOW:
                return $html;
            default:
                throw new \InvalidArgumentException(\sprintf('Invalid filter provided: "%s"', $filter));
        }
    }
}
