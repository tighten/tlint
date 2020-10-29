<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class OneLineBetweenClassVisibilityChanges extends BaseLinter
{
    public const description = 'Class members of differing visibility must be separated by a blank line';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $notSeparatedByBlankLine = [];

        $visitor = new FindingVisitor(function (Node $node) use (&$notSeparatedByBlankLine) {
            if ($node instanceof Class_) {
                $prev = null;

                foreach (array_filter($node->stmts, function ($stmt) {
                    return in_array(get_class($stmt), [
                        Node\Stmt\ClassConst::class,
                        Node\Stmt\Property::class,
                    ]);
                }) as $stmt) {
                    if (! is_null($prev)) {
                        if ($prev->flags !== $stmt->flags &&
                            (($stmt->getStartLine() - $prev->getEndLine() !== 2 && ! (bool) $stmt->getDocComment()) ||
                            ((bool) $stmt->getDocComment() && $stmt->getDocComment()->getStartLine() - $prev->getEndLine() !== 2 ))
                            ) {
                            $notSeparatedByBlankLine[] = $stmt;
                        }
                    }

                    $prev = $stmt;
                }
            }

            return false;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $notSeparatedByBlankLine;
    }
}
