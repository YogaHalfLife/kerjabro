<?php

/**
 * This file is part of the ramsey/collection library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Ramsey <ben@benramsey.com>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace Ramsey\Collection\Tool;

use DateTimeInterface;

use function get_class;
use function get_resource_type;
use function is_array;
use function is_bool;
use function is_callable;
use function is_resource;
use function is_scalar;

/**
 * Provides functionality to express a value as string
 */
trait ValueToStringTrait
{
    /**
     * Returns a string representation of the value.
     *
     * - null value: `'NULL'`
     * - boolean: `'TRUE'`, `'FALSE'`
     * - array: `'Array'`
     * - scalar: converted-value
     * - resource: `'(type resource #number)'`
     * - object with `__toString()`: result of `__toString()`
     * - object DateTime: ISO 8601 date
     * - object: `'(className Object)'`
     * - anonymous function: same as object
     *
     * @param mixed $value the value to return as a string.
     */
    protected function toolValueToString($value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }
        if (is_array($value)) {
            return 'Array';
        }
        if (is_scalar($value)) {
            return (string) $value;
        }
        if (is_resource($value)) {
            return '(' . get_resource_type($value) . ' resource #' . (int) $value . ')';
        }
        if (!is_object($value)) {
            return '(' . var_export($value, true) . ')';
        }
        if (is_callable([$value, '__toString'])) {
            return (string) $value->__toString();
        }
        if ($value instanceof DateTimeInterface) {
            return $value->format('c');
        }
        return '(' . get_class($value) . ' Object)';
    }
}
