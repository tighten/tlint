<?php

namespace Tighten\Linters;

use Closure;
use Exception;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;
use Tighten\Concerns\IdentifiesClassThings;
use Tighten\Concerns\IdentifiesExtends;

class ClassThingsOrder extends BaseLinter
{
    use IdentifiesClassThings;
    use IdentifiesExtends;

    protected const THINGS_ORDER = [
        'trait use',
        'public static property',
        'protected static property',
        'private static property',
        'public constant',
        'protected constant',
        'private constant',
        'public property',
        'protected property',
        'private property',
        'constructor',
        'public static method',
        'protected static method',
        'private static method',
        'public method',
        'protected method',
        'private method',
        'magic method',
    ];

    protected $tests;

    public function __construct($code, $filename = null)
    {
        parent::__construct($code, $filename);

        $this->setLintDescription('Class "things" should be ordered ' . implode(', ', self::THINGS_ORDER));

        $this->tests = [
            'trait use' => Closure::fromCallable([$this, 'isTraitUse']),
            'public static property' => Closure::fromCallable([$this, 'isPublicStaticProperty']),
            'protected static property' => Closure::fromCallable([$this, 'isProtectedStaticProperty']),
            'private static property' => Closure::fromCallable([$this, 'isPrivateStaticProperty']),
            'public constant' => Closure::fromCallable([$this, 'isPublicConstant']),
            'protected constant' => Closure::fromCallable([$this, 'isProtectedConstant']),
            'private constant' => Closure::fromCallable([$this, 'isPrivateConstant']),
            'public property' => Closure::fromCallable([$this, 'isPublicProperty']),
            'protected property' => Closure::fromCallable([$this, 'isProtectedProperty']),
            'private property' => Closure::fromCallable([$this, 'isPrivateProperty']),
            'public static method' => Closure::fromCallable([$this, 'isPublicStaticMethod']),
            'protected static method' => Closure::fromCallable([$this, 'isProtectedStaticMethod']),
            'private static method' => Closure::fromCallable([$this, 'isPrivateStaticMethod']),
            'constructor' => Closure::fromCallable([$this, 'isConstructor']),
            'public method' => Closure::fromCallable([$this, 'isPublicMethod']),
            'protected method' => Closure::fromCallable([$this, 'isProtectedMethod']),
            'private method' => Closure::fromCallable([$this, 'isPrivateMethod']),
            'magic method' => Closure::fromCallable([$this, 'isMagicMethod']),
        ];
    }

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            if ($node instanceof Class_) {
                $thingTypes = array_map(function ($stmt) {
                    foreach ($this->tests as $label => $test) {
                        if ($test($stmt)) {
                            return $label;
                        }
                    }

                    throw new Exception('Unknown statement');
                }, array_filter($node->stmts, function (Node\Stmt $stmt) {
                    // Ignore PhpUnit protected setUp/tearDown method
                    if ($stmt instanceof Node\Stmt\ClassMethod
                        && $stmt->isProtected()
                        && in_array($stmt->name->toString(), ['setUp', 'tearDown'])
                        && $stmt->returnType->toString() === 'void'
                    ) {
                        return false;
                    }

                    return ! $stmt instanceof Node\Stmt\Nop;
                }));

                $uniquedThingTypes = array_values(array_unique($thingTypes));

                return $uniquedThingTypes
                    !== array_values(array_intersect(self::THINGS_ORDER, $uniquedThingTypes));
            }

            return false;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
