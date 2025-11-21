<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing;

/**
 * RouteCompiler compiles Route instances to CompiledRoute instances.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Tobias Schultze <http://tobion.de>
 */
class RouteCompiler implements RouteCompilerInterface
{
    /**
     * This string defines the characters that are automatically considered separators in front of
     * optional placeholders (with default and no static text following). Such a single separator
     * can be left out together with the optional placeholder from matching and generating URLs.
     */
    public const SEPARATORS = '/,;.:-_~+*=@|';

    /**
     * The maximum supported length of a PCRE subpattern name
     * http://pcre.org/current/doc/html/pcre2pattern.html#SEC16.
     *
     * @internal
     */
    public const VARIABLE_MAXIMUM_LENGTH = 32;

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException if a path variable is named _fragment
     * @throws \LogicException           if a variable is referenced more than once
     * @throws \DomainException          if a variable name starts with a digit or if it is too long to be successfully used as
     *                                   a PCRE subpattern
     */
    public static function compile(Route $route): CompiledRoute
    {
        $hostVariables = [];
        $variables = [];
        $hostRegex = null;
        $hostTokens = [];

        if ('' !== $host = $route->getHost()) {
            $result = self::compilePattern($route, $host, true);

            $hostVariables = $result['variables'];
            $variables = $hostVariables;

            $hostTokens = $result['tokens'];
            $hostRegex = $result['regex'];
        }

        $locale = $route->getDefault('_locale');
        if (null !== $locale && null !== $route->getDefault('_canonical_route') && preg_quote($locale) === $route->getRequirement('_locale')) {
            $requirements = $route->getRequirements();
            unset($requirements['_locale']);
            $route->setRequirements($requirements);
            $route->setPath(str_replace('{_locale}', $locale, $route->getPath()));
        }

        $path = $route->getPath();

        $result = self::compilePattern($route, $path, false);

        $staticPrefix = $result['staticPrefix'];

        $pathVariables = $result['variables'];

        foreach ($pathVariables as $pathParam) {
            if ('_fragment' === $pathParam) {
                throw new \InvalidArgumentException(sprintf('Route pattern "%s" cannot contain "_fragment" as a path parameter.', $route->getPath()));
            }
        }

        $variables = array_merge($variables, $pathVariables);

        $tokens = $result['tokens'];
        $regex = $result['regex'];

        return new CompiledRoute(
            $staticPrefix,
            $regex,
            $tokens,
            $pathVariables,
            $hostRegex,
            $hostTokens,
            $hostVariables,
            array_unique($variables)
        );
    }

    private static function compilePattern(Route $route, string $pattern, bool $isHost): array
    {
        $tokens = [];
        $variables = [];
        $matches = [];
        $pos = 0;
        $defaultSeparator = $isHost ? '.' : '/';
        $useUtf8 = preg_match('//u', $pattern);
        $needsUtf8 = $route->getOption('utf8');

        if (!$needsUtf8 && $useUtf8 && preg_match('/[\x80-\xFF]/', $pattern)) {
            throw new \LogicException(sprintf('Cannot use UTF-8 route patterns without setting the "utf8" option for route "%s".', $route->getPath()));
        }
        if (!$useUtf8 && $needsUtf8) {
            throw new \LogicException(sprintf('Cannot mix UTF-8 requirements with non-UTF-8 pattern "%s".', $pattern));
        }
        preg_match_all('#\{(!)?(\w+)\}#', $pattern, $matches, \PREG_OFFSET_CAPTURE | \PREG_SET_ORDER);
        foreach ($matches as $match) {
            $important = $match[1][1] >= 0;
            $varName = $match[2][0];
            $precedingText = substr($pattern, $pos, $match[0][1] - $pos);
            $pos = $match[0][1] + \strlen($match[0][0]);

            if (!\strlen($precedingText)) {
                $precedingChar = '';
            } elseif ($useUtf8) {
                preg_match('/.$/u', $precedingText, $precedingChar);
                $precedingChar = $precedingChar[0];
            } else {
                $precedingChar = substr($precedingText, -1);
            }
            $isSeparator = '' !== $precedingChar && str_contains(static::SEPARATORS, $precedingChar);
            if (preg_match('/^\d/', $varName)) {
                throw new \DomainException(sprintf('Variable name "%s" cannot start with a digit in route pattern "%s". Please use a different name.', $varName, $pattern));
            }
            if (\in_array($varName, $variables)) {
                throw new \LogicException(sprintf('Route pattern "%s" cannot reference variable name "%s" more than once.', $pattern, $varName));
            }

            if (\strlen($varName) > self::VARIABLE_MAXIMUM_LENGTH) {
                throw new \DomainException(sprintf('Variable name "%s" cannot be longer than %d characters in route pattern "%s". Please use a shorter name.', $varName, self::VARIABLE_MAXIMUM_LENGTH, $pattern));
            }

            if ($isSeparator && $precedingText !== $precedingChar) {
                $tokens[] = ['text', substr($precedingText, 0, -\strlen($precedingChar))];
            } elseif (!$isSeparator && '' !== $precedingText) {
                $tokens[] = ['text', $precedingText];
            }

            $regexp = $route->getRequirement($varName);
            if (null === $regexp) {
                $followingPattern = (string) substr($pattern, $pos);
                $nextSeparator = self::findNextSeparator($followingPattern, $useUtf8);
                $regexp = sprintf(
                    '[^%s%s]+',
                    preg_quote($defaultSeparator),
                    $defaultSeparator !== $nextSeparator && '' !== $nextSeparator ? preg_quote($nextSeparator) : ''
                );
                if (('' !== $nextSeparator && !preg_match('#^\{\w+\}#', $followingPattern)) || '' === $followingPattern) {
                    $regexp .= '+';
                }
            } else {
                if (!preg_match('//u', $regexp)) {
                    $useUtf8 = false;
                } elseif (!$needsUtf8 && preg_match('/[\x80-\xFF]|(?<!\\\\)\\\\(?:\\\\\\\\)*+(?-i:X|[pP][\{CLMNPSZ]|x\{[A-Fa-f0-9]{3})/', $regexp)) {
                    throw new \LogicException(sprintf('Cannot use UTF-8 route requirements without setting the "utf8" option for variable "%s" in pattern "%s".', $varName, $pattern));
                }
                if (!$useUtf8 && $needsUtf8) {
                    throw new \LogicException(sprintf('Cannot mix UTF-8 requirement with non-UTF-8 charset for variable "%s" in pattern "%s".', $varName, $pattern));
                }
                $regexp = self::transformCapturingGroupsToNonCapturings($regexp);
            }

            if ($important) {
                $token = ['variable', $isSeparator ? $precedingChar : '', $regexp, $varName, false, true];
            } else {
                $token = ['variable', $isSeparator ? $precedingChar : '', $regexp, $varName];
            }

            $tokens[] = $token;
            $variables[] = $varName;
        }

        if ($pos < \strlen($pattern)) {
            $tokens[] = ['text', substr($pattern, $pos)];
        }
        $firstOptional = \PHP_INT_MAX;
        if (!$isHost) {
            for ($i = \count($tokens) - 1; $i >= 0; --$i) {
                $token = $tokens[$i];
                if ('variable' === $token[0] && !($token[5] ?? false) && $route->hasDefault($token[3])) {
                    $firstOptional = $i;
                } else {
                    break;
                }
            }
        }
        $regexp = '';
        for ($i = 0, $nbToken = \count($tokens); $i < $nbToken; ++$i) {
            $regexp .= self::computeRegexp($tokens, $i, $firstOptional);
        }
        $regexp = '{^'.$regexp.'$}sD'.($isHost ? 'i' : '');
        if ($needsUtf8) {
            $regexp .= 'u';
            for ($i = 0, $nbToken = \count($tokens); $i < $nbToken; ++$i) {
                if ('variable' === $tokens[$i][0]) {
                    $tokens[$i][4] = true;
                }
            }
        }

        return [
            'staticPrefix' => self::determineStaticPrefix($route, $tokens),
            'regex' => $regexp,
            'tokens' => array_reverse($tokens),
            'variables' => $variables,
        ];
    }

    /**
     * Determines the longest static prefix possible for a route.
     */
    private static function determineStaticPrefix(Route $route, array $tokens): string
    {
        if ('text' !== $tokens[0][0]) {
            return ($route->hasDefault($tokens[0][3]) || '/' === $tokens[0][1]) ? '' : $tokens[0][1];
        }

        $prefix = $tokens[0][1];

        if (isset($tokens[1][1]) && '/' !== $tokens[1][1] && false === $route->hasDefault($tokens[1][3])) {
            $prefix .= $tokens[1][1];
        }

        return $prefix;
    }

    /**
     * Returns the next static character in the Route pattern that will serve as a separator (or the empty string when none available).
     */
    private static function findNextSeparator(string $pattern, bool $useUtf8): string
    {
        if ('' == $pattern) {
            return '';
        }
        if ('' === $pattern = preg_replace('#\{\w+\}#', '', $pattern)) {
            return '';
        }
        if ($useUtf8) {
            preg_match('/^./u', $pattern, $pattern);
        }

        return str_contains(static::SEPARATORS, $pattern[0]) ? $pattern[0] : '';
    }

    /**
     * Computes the regexp used to match a specific token. It can be static text or a subpattern.
     *
     * @param array $tokens        The route tokens
     * @param int   $index         The index of the current token
     * @param int   $firstOptional The index of the first optional token
     */
    private static function computeRegexp(array $tokens, int $index, int $firstOptional): string
    {
        $token = $tokens[$index];
        if ('text' === $token[0]) {
            return preg_quote($token[1]);
        } else {
            if (0 === $index && 0 === $firstOptional) {
                return sprintf('%s(?P<%s>%s)?', preg_quote($token[1]), $token[3], $token[2]);
            } else {
                $regexp = sprintf('%s(?P<%s>%s)', preg_quote($token[1]), $token[3], $token[2]);
                if ($index >= $firstOptional) {
                    $regexp = "(?:$regexp";
                    $nbTokens = \count($tokens);
                    if ($nbTokens - 1 == $index) {
                        $regexp .= str_repeat(')?', $nbTokens - $firstOptional - (0 === $firstOptional ? 1 : 0));
                    }
                }

                return $regexp;
            }
        }
    }

    private static function transformCapturingGroupsToNonCapturings(string $regexp): string
    {
        for ($i = 0; $i < \strlen($regexp); ++$i) {
            if ('\\' === $regexp[$i]) {
                ++$i;
                continue;
            }
            if ('(' !== $regexp[$i] || !isset($regexp[$i + 2])) {
                continue;
            }
            if ('*' === $regexp[++$i] || '?' === $regexp[$i]) {
                ++$i;
                continue;
            }
            $regexp = substr_replace($regexp, '?:', $i, 0);
            ++$i;
        }

        return $regexp;
    }
}
