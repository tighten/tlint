<?php

namespace Tighten\TLint\Linters;

use Closure;
use PhpParser\Node;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Linters\Concerns\LintsControllers;

class PureRestControllers extends BaseLinter
{
    use LintsControllers;

    public const DESCRIPTION = 'You should not mix restful and non-restful public methods in a controller';

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

    protected function visitor(): Closure
    {
        return function (Node $node) {
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
        };
    }
}
