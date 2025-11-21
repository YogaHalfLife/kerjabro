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

namespace League\CommonMark\Normalizer;
final class UniqueSlugNormalizer implements UniqueSlugNormalizerInterface
{
    private TextNormalizerInterface $innerNormalizer;
    /** @var array<string, bool> */
    private array $alreadyUsed = [];

    public function __construct(TextNormalizerInterface $innerNormalizer)
    {
        $this->innerNormalizer = $innerNormalizer;
    }

    public function clearHistory(): void
    {
        $this->alreadyUsed = [];
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-allow-private-mutation
     */
    public function normalize(string $text, array $context = []): string
    {
        $normalized = $this->innerNormalizer->normalize($text, $context);
        if (\array_key_exists($normalized, $this->alreadyUsed)) {
            $suffix = 0;
            do {
                ++$suffix;
            } while (\array_key_exists("$normalized-$suffix", $this->alreadyUsed));

            $normalized = "$normalized-$suffix";
        }

        $this->alreadyUsed[$normalized] = true;

        return $normalized;
    }
}
