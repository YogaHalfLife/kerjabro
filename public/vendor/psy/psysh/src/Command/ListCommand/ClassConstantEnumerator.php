<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2022 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\Command\ListCommand;

use Psy\Reflection\ReflectionClassConstant;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class Constant Enumerator class.
 */
class ClassConstantEnumerator extends Enumerator
{
    /**
     * {@inheritdoc}
     */
    protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null): array
    {
        if ($reflector === null) {
            return [];
        }
        if (!$reflector instanceof \ReflectionClass) {
            return [];
        }
        if (!$input->getOption('constants')) {
            return [];
        }

        $noInherit = $input->getOption('no-inherit');
        $constants = $this->prepareConstants($this->getConstants($reflector, $noInherit));

        if (empty($constants)) {
            return [];
        }

        $ret = [];
        $ret[$this->getKindLabel($reflector)] = $constants;

        return $ret;
    }

    /**
     * Get defined constants for the given class or object Reflector.
     *
     * @param \Reflector $reflector
     * @param bool       $noInherit Exclude inherited constants
     *
     * @return array
     */
    protected function getConstants(\Reflector $reflector, bool $noInherit = false): array
    {
        $className = $reflector->getName();

        $constants = [];
        foreach ($reflector->getConstants() as $name => $constant) {
            $constReflector = ReflectionClassConstant::create($reflector->name, $name);

            if ($noInherit && $constReflector->getDeclaringClass()->getName() !== $className) {
                continue;
            }

            $constants[$name] = $constReflector;
        }

        \ksort($constants, \SORT_NATURAL | \SORT_FLAG_CASE);

        return $constants;
    }

    /**
     * Prepare formatted constant array.
     *
     * @param array $constants
     *
     * @return array
     */
    protected function prepareConstants(array $constants): array
    {
        $ret = [];

        foreach ($constants as $name => $constant) {
            if ($this->showItem($name)) {
                $ret[$name] = [
                    'name'  => $name,
                    'style' => self::IS_CONSTANT,
                    'value' => $this->presentRef($constant->getValue()),
                ];
            }
        }

        return $ret;
    }

    /**
     * Get a label for the particular kind of "class" represented.
     *
     * @param \ReflectionClass $reflector
     *
     * @return string
     */
    protected function getKindLabel(\ReflectionClass $reflector): string
    {
        if ($reflector->isInterface()) {
            return 'Interface Constants';
        } else {
            return 'Class Constants';
        }
    }
}
