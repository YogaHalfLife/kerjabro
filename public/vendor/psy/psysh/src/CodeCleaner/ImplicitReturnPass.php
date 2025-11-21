<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2022 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\CodeCleaner;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;

/**
 * Add an implicit "return" to the last statement, provided it can be returned.
 */
class ImplicitReturnPass extends CodeCleanerPass
{
    /**
     * @param array $nodes
     *
     * @return array
     */
    public function beforeTraverse(array $nodes): array
    {
        return $this->addImplicitReturn($nodes);
    }

    /**
     * @param array $nodes
     *
     * @return array
     */
    private function addImplicitReturn(array $nodes): array
    {
        if (empty($nodes)) {
            return [new Return_(NoReturnValue::create())];
        }

        $last = \end($nodes);
        if ($last instanceof If_) {
            $last->stmts = $this->addImplicitReturn($last->stmts);

            foreach ($last->elseifs as $elseif) {
                $elseif->stmts = $this->addImplicitReturn($elseif->stmts);
            }

            if ($last->else) {
                $last->else->stmts = $this->addImplicitReturn($last->else->stmts);
            }
        } elseif ($last instanceof Switch_) {
            foreach ($last->cases as $case) {
                $caseLast = \end($case->stmts);
                if ($caseLast instanceof Break_) {
                    $case->stmts = $this->addImplicitReturn(\array_slice($case->stmts, 0, -1));
                    $case->stmts[] = $caseLast;
                }
            }
        } elseif ($last instanceof Expr && !($last instanceof Exit_)) {
            $nodes[\count($nodes) - 1] = new Return_($last, [
                'startLine' => $last->getLine(),
                'endLine'   => $last->getLine(),
            ]);
        } elseif ($last instanceof Expression && !($last->expr instanceof Exit_)) {
            $nodes[\count($nodes) - 1] = new Return_($last->expr, [
                'startLine' => $last->getLine(),
                'endLine'   => $last->getLine(),
            ]);
        } elseif ($last instanceof Namespace_) {
            $last->stmts = $this->addImplicitReturn($last->stmts);
        }
        if (self::isNonExpressionStmt($last)) {
            $nodes[] = new Return_(NoReturnValue::create());
        }

        return $nodes;
    }

    /**
     * Check whether a given node is a non-expression statement.
     *
     * As of PHP Parser 4.x, Expressions are now instances of Stmt as well, so
     * we'll exclude them here.
     *
     * @param Node $node
     *
     * @return bool
     */
    private static function isNonExpressionStmt(Node $node): bool
    {
        return $node instanceof Stmt &&
            !$node instanceof Expression &&
            !$node instanceof Return_ &&
            !$node instanceof Namespace_;
    }
}
