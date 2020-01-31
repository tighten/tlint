<?php

namespace Tighten\Linters;

use Closure;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;
use Tighten\Concerns\IdentifiesExtends;
use Tighten\Concerns\IdentifiesModelMethodTypes;

class ModelMethodOrder extends BaseLinter
{
    use IdentifiesModelMethodTypes;
    use IdentifiesExtends;

    public const description = 'Model method order should be relationships > scopes > accessors > mutators > boot';

    protected const METHOD_ORDER = [
        0 => 'relationship',
        1 => 'scope',
        2 => 'accessor',
        3 => 'mutator',
        4 => 'boot',
    ];

    protected $tests;

    public function __construct($code, $filename = null)
    {
        parent::__construct($code, $filename);

        $this->tests = [
            'scope' => Closure::fromCallable([$this, 'isScopeMethod']),
            'accessor' => Closure::fromCallable([$this, 'isAccessorMethod']),
            'mutator' => Closure::fromCallable([$this, 'isMutatorMethod']),
            'boot' => Closure::fromCallable([$this, 'isBootMethod']),
        ];
    }

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            if ($this->extends($node, 'Model')) {
                $methodTypes = array_map(function (ClassMethod $stmt) {
                    foreach ($this->tests as $label => $test) {
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
