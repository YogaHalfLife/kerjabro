<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2022 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\Readline;

use Psy\Util\Str;

/**
 * A Libedit-based Readline implementation.
 *
 * This is largely the same as the Readline implementation, but it emulates
 * support for `readline_list_history` since PHP decided it was a good idea to
 * ship a fake Readline implementation that is missing history support.
 *
 * NOTE: As of PHP 7.4, PHP sometimes has history support in the Libedit
 * wrapper, so it will use the GNUReadline implementation rather than this one.
 */
class Libedit extends GNUReadline
{
    private $hasWarnedOwnership = false;

    /**
     * Let's emulate GNU Readline by manually reading and parsing the history file!
     *
     * @return bool
     */
    public static function isSupported(): bool
    {
        return \function_exists('readline') && !\function_exists('readline_list_history');
    }

    /**
     * {@inheritdoc}
     */
    public static function supportsBracketedPaste(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function listHistory(): array
    {
        $history = \file_get_contents($this->historyFile);
        if (!$history) {
            return [];
        }
        $history = \explode("\n", $history);
        if ($history[0] === '_HiStOrY_V2_') {
            \array_shift($history);
        }
        $history = \array_map([$this, 'parseHistoryLine'], $history);
        return \array_values(\array_filter($history));
    }

    /**
     * {@inheritdoc}
     */
    public function writeHistory(): bool
    {
        $res = parent::writeHistory();
        if ($res === false && !$this->hasWarnedOwnership) {
            if (\is_file($this->historyFile) && \is_writable($this->historyFile)) {
                $this->hasWarnedOwnership = true;
                $msg = \sprintf('Error writing history file, check file ownership: %s', $this->historyFile);
                \trigger_error($msg, \E_USER_NOTICE);
            }
        }

        return $res;
    }

    /**
     * From GNUReadline (readline/histfile.c & readline/histexpand.c):
     * lines starting with "\0" are comments or timestamps;
     * if "\0" is found in an entry,
     * everything from it until the next line is a comment.
     *
     * @param string $line The history line to parse
     *
     * @return string|null
     */
    protected function parseHistoryLine(string $line)
    {
        if (!$line || $line[0] === "\0") {
            return;
        }
        if (($pos = \strpos($line, "\0")) !== false) {
            $line = \substr($line, 0, $pos);
        }

        return ($line !== '') ? Str::unvis($line) : null;
    }
}
