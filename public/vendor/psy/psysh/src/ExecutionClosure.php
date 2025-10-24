<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2022 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy;

/**
 * The Psy Shell's execution scope.
 */
class ExecutionClosure
{
    const NOOP_INPUT = 'return null;';

    private $closure;

    /**
     * @param Shell $__psysh__
     */
    public function __construct(Shell $__psysh__)
    {
        $this->setClosure($__psysh__, function () use ($__psysh__) {
            try {
                \extract($__psysh__->getScopeVariables(false));
                \ob_start([$__psysh__, 'writeStdout'], 1);
                \set_error_handler([$__psysh__, 'handleError']);
                $_ = eval($__psysh__->onExecute($__psysh__->flushCode() ?: self::NOOP_INPUT));
            } catch (\Throwable $_e) {
                if (\ob_get_level() > 0) {
                    \ob_end_clean();
                }

                throw $_e;
            } finally {
                \restore_error_handler();
            }
            \ob_end_flush();
            $__psysh__->setScopeVariables(\get_defined_vars());

            return $_;
        });
    }

    /**
     * Set the closure instance.
     *
     * @param Shell    $shell
     * @param \Closure $closure
     */
    protected function setClosure(Shell $shell, \Closure $closure)
    {
        $that = $shell->getBoundObject();

        if (\is_object($that)) {
            $this->closure = $closure->bindTo($that, \get_class($that));
        } else {
            $this->closure = $closure->bindTo(null, $shell->getBoundClass());
        }
    }

    /**
     * Go go gadget closure.
     *
     * @return mixed
     */
    public function execute()
    {
        $closure = $this->closure;

        return $closure();
    }
}
