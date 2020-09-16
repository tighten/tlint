<?php

namespace Tighten\Linters;

use Closure;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use Tighten\BaseLinter;
use Tighten\Concerns\IdentifiesExtends;
use Tighten\Concerns\IdentifiesModelMethodTypes;

class ModelMethodOrder extends BaseLinter
{
    use IdentifiesModelMethodTypes;
    use IdentifiesExtends;

    public const description = 'Model method order should be: booting > boot > booted > custom_static > relationships > scopes > accessors > mutators > custom';

    protected const METHOD_ORDER = [
        0 => 'booting',
        1 => 'boot',
        2 => 'booted',
        3 => 'custom_static',
        4 => 'relationship',
        5 => 'scope',
        6 => 'accessor',
        7 => 'mutator',
        8 => 'custom',
    ];

    protected $tests;

    public function __construct($code, $filename = null)
    {
        parent::__construct($code, $filename);

        // order of tests is important
        $this->tests = [
            // detect the static boot methods
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
        $traverser->addVisitor(new NameResolver());

        $nodes = $traverser->traverse($parser->parse($this->code));

        return (new NodeFinder())->find(
            $nodes,
            function (Node $node): bool {
                return $node instanceof Node\Stmt\Class_
                    && $this->extends($node, 'Illuminate\Database\Eloquent\Model')
                    && ! $this->lintModel($node);
            }
        );
    }

    protected function lintModel(Node\Stmt\Class_ $node): bool
    {
        // get all methods on class
        $methods = $node->getMethods();

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
        // sort all methods by type and in type blocks alphabetically
        uksort($methodTypesShouldBeOrderedLike, function ($methodA, $methodB) use ($methodTypes) {
            $typeA = $methodTypes[$methodA];
            $typeB = $methodTypes[$methodB];

            $sortA = array_flip(self::METHOD_ORDER)[$typeA];
            $sortB = array_flip(self::METHOD_ORDER)[$typeB];

            if ($sortA == $sortB) {
                return strnatcasecmp($methodA, $methodB);
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

        return $uniqueMethodTypes === array_values(array_intersect(self::METHOD_ORDER, $uniqueMethodTypes));
    }
}
