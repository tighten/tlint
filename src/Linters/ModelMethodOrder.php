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

    public const description = 'Model method order should be: relationships > scopes > accessors > mutators > booting > boot > booted > custom';

    protected const METHOD_ORDER = [
        0 => 'relationship',
        1 => 'scope',
        2 => 'accessor',
        3 => 'mutator',
        4 => 'booting',
        5 => 'boot',
        6 => 'booted',
        7 => 'custom',
    ];

    protected $tests;

    public function __construct($code, $filename = null)
    {
        parent::__construct($code, $filename);

        // order of tests is important
        $this->tests = [
            // first detect the static boot methods
            'booting' => Closure::fromCallable([$this, 'isBootingMethod']),
            'boot' => Closure::fromCallable([$this, 'isBootMethod']),
            'booted' => Closure::fromCallable([$this, 'isBootedMethod']),
            // declare everything else custom static
            'custom_static' => Closure::fromCallable([$this, 'isCustomStaticMethod']),
            // declare everything custom that's not public
            'custom' => Closure::fromCallable([$this, 'isCustomMethod']),
            // detect all methods that have to be public
            'relationship' => Closure::fromCallable([$this, 'isRelationshipMethod']),
            'scope' => Closure::fromCallable([$this, 'isScopeMethod']),
            'accessor' => Closure::fromCallable([$this, 'isAccessorMethod']),
            'mutator' => Closure::fromCallable([$this, 'isMutatorMethod']),
        ];
    }

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            if ($this->extends($node, 'Model')) {
                // get all methods on class
                $methods = array_filter($node->stmts, function ($stmt) {
                    return $stmt instanceof ClassMethod;
                });
                // key by method name
                $methods = array_combine(
                    array_map(function (ClassMethod $stmt) {
                        return $stmt->name;
                    }, $methods),
                    $methods
                );

                // resolve method type
                $methodTypes = array_map(function (ClassMethod $stmt) {
                    foreach ($this->tests as $label => $test) {
                        if ($test($stmt)) {
                            return $label;
                        }
                    }

                    return 'custom';
                }, $methods);

                $methodTypesShouldBeOrderedLike = $methodTypes;
                uasort($methodTypesShouldBeOrderedLike, function ($typeA, $typeB) {
                    $sortA = array_flip(self::METHOD_ORDER)[$typeA];
                    $sortB = array_flip(self::METHOD_ORDER)[$typeB];

                    if ($sortA == $sortB) {
                        return 0;
                    }

                    return ($sortA < $sortB) ? -1 : 1;
                });

                $this->setLintDescription(
                    self::description . PHP_EOL
                    . 'Methods are expected to be ordered like:' . PHP_EOL
                    . implode(
                        PHP_EOL,
                        array_map(function(string $method, string $type) {
                            return sprintf(' * %s() is matched as "%s"', $method, $type);
                        }, array_keys($methodTypesShouldBeOrderedLike), array_values($methodTypesShouldBeOrderedLike))
                    )
                );

                $uniqueMethodTypes = array_values(array_unique($methodTypes));

                return $uniqueMethodTypes
                    !== array_values(array_intersect(self::METHOD_ORDER, $uniqueMethodTypes));
            }

            return false;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
