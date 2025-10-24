<?php declare(strict_types=1);

namespace PhpParser\Node\Scalar;

use PhpParser\Node\Scalar;

class DNumber extends Scalar
{
    /** @var float Number value */
    public $value;

    /**
     * Constructs a float number scalar node.
     *
     * @param float $value      Value of the number
     * @param array $attributes Additional attributes
     */
    public function __construct(float $value, array $attributes = []) {
        $this->attributes = $attributes;
        $this->value = $value;
    }

    public function getSubNodeNames() : array {
        return ['value'];
    }

    /**
     * @internal
     *
     * Parses a DNUMBER token like PHP would.
     *
     * @param string $str A string number
     *
     * @return float The parsed number
     */
    public static function parse(string $str) : float {
        $str = str_replace('_', '', $str);
        if (false !== strpbrk($str, '.eE')) {
            return (float) $str;
        }
        if ('0' === $str[0]) {
            if ('x' === $str[1] || 'X' === $str[1]) {
                return hexdec($str);
            }
            if ('b' === $str[1] || 'B' === $str[1]) {
                return bindec($str);
            }
            return octdec(substr($str, 0, strcspn($str, '89')));
        }
        return (float) $str;
    }
    
    public function getType() : string {
        return 'Scalar_DNumber';
    }
}
