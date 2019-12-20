<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;
use Tighten\Linters\Concerns\LintsControllers;

class PureRestControllers extends BaseLinter
{
    use LintsControllers;

    public const description = 'You should not mix restful and non-restful public methods in a controller';

    protected const RESTFUL_METHOD_NAMES = [
        'index',
        'create',
        'store',
        'show',
        'edit',
        'update',
        'destroy',
    ];
    protected const IGNORED_METHOD_NAMES = [
        'validator',
        '__construct',
    ];

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            if ($node instanceof Node\Stmt\Class_) {
                $methodNames = array_filter(array_map(function ($stmt) {
                    return $stmt->name;
                }, array_filter($node->stmts, function ($stmt) {
                    return $stmt instanceof Node\Stmt\ClassMethod && $stmt->isPublic();
                })), function ($methodName) {
                    return ! in_array($methodName, self::IGNORED_METHOD_NAMES);
                });

                $restfulMethods = array_intersect(self::RESTFUL_METHOD_NAMES, $methodNames);
                $nonRestfulMethods = array_diff($methodNames, self::RESTFUL_METHOD_NAMES);

                return count($restfulMethods) > 0 && count($nonRestfulMethods) > 0;
            }

            return false;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
