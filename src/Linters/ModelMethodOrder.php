<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\AbstractLinter;

class ModelMethodOrder extends AbstractLinter
{
    private const METHOD_ORDER = [
        0 => 'relationship',
        1 => 'scope',
        2 => 'accessor',
        3 => 'mutator',
        4 => 'boot',
    ];

    public function lintDescription()
    {
        return 'Model method order should be relationships > scopes > accessors > mutators > boot';
    }

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser();

        $visitor = new FindingVisitor(function (Node $node) {
            if ($node instanceof Node\Stmt\Class_ && $node->extends->toString() === 'Model') {
                $methodTypes = array_map(function (ClassMethod $stmt) {
                    $tests = [
                        'scope' => function (ClassMethod $stmt) {
                            return strpos($stmt->name, 'scope') === 0;
                        },
                        'accessor' => function (ClassMethod $stmt) {
                            return strpos($stmt->name, 'get') === 0
                                && strpos($stmt->name, 'Attribute') === strlen($stmt->name) - 9;
                        },
                        'mutator' => function (ClassMethod $stmt) {
                            return strpos($stmt->name, 'set') === 0
                                && strpos($stmt->name, 'Attribute') === strlen($stmt->name) - 9;
                        },
                        'boot' => function (ClassMethod $stmt) {
                            return $stmt->name === 'boot';
                        },
                    ];

                    foreach ($tests as $label => $test) {
                        if ($test($stmt)) {
                            return $label;
                        }
                    }

                    /** This is the catch all, as custom methods should be extracted into "service objects" */
                    return 'relationship';
                }, array_filter($node->stmts, function ($stmt) {
                    return $stmt instanceof ClassMethod;
                }));

                $uniquedMethodTypes = array_values(array_unique($methodTypes));

                return $uniquedMethodTypes
                    !== array_values(array_intersect(self::METHOD_ORDER, $uniquedMethodTypes));
            }

            return false;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
