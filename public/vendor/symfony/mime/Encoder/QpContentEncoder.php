<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mime\Encoder;

/**
 * @author Lars Strojny
 */
final class QpContentEncoder implements ContentEncoderInterface
{
    public function encodeByteStream($stream, int $maxLineLength = 0): iterable
    {
        if (!\is_resource($stream)) {
            throw new \TypeError(sprintf('Method "%s" takes a stream as a first argument.', __METHOD__));
        }
        yield $this->encodeString(stream_get_contents($stream), 'utf-8', 0, $maxLineLength);
    }

    public function getName(): string
    {
        return 'quoted-printable';
    }

    public function encodeString(string $string, ?string $charset = 'utf-8', int $firstLineOffset = 0, int $maxLineLength = 0): string
    {
        return $this->standardize(quoted_printable_encode($string));
    }

    /**
     * Make sure CRLF is correct and HT/SPACE are in valid places.
     */
    private function standardize(string $string): string
    {
        $string = preg_replace('~=0D(?!=0A)|(?<!=0D)=0A~', '=0D=0A', $string);
        $string = str_replace(["\t=0D=0A", ' =0D=0A', '=0D=0A'], ["=09\r\n", "=20\r\n", "\r\n"], $string);

        switch (\ord(substr($string, -1))) {
            case 0x09:
                $string = substr_replace($string, '=09', -1);
                break;
            case 0x20:
                $string = substr_replace($string, '=20', -1);
                break;
        }

        return $string;
    }
}
