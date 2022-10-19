<?php

namespace Tighten\TLint\Linters;

use Closure;
use PhpParser\Node;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Linters\Concerns\LintsControllers;

class RestControllersMethodOrder extends BaseLinter
{
    use LintsControllers;

    public const DESCRIPTION = 'REST methods in controllers should match the ordering here:'
        . ' https://laravel.com/docs/controllers#restful-partial-resource-routes';

    protected const RESTFUL_METHOD_NAMES = [
        'index',
        'create',
        'store',
        'show',
        'edit',
        'update',
        'destroy',
    ];

    protected function visitor(): Closure
    {
        return function (Node $node) {
            if ($node instanceof Node\Stmt\Class_) {
                $methodNames = array_map(function ($stmt) {
                    return $stmt->name->name;
                }, array_filter($node->stmts, function ($stmt) {
                    return $stmt instanceof Node\Stmt\ClassMethod;
                }));

                $restfulMethods = array_intersect(self::RESTFUL_METHOD_NAMES, $methodNames);
                $nonRestfulMethods = array_diff($methodNames, self::RESTFUL_METHOD_NAMES);

                if (count($restfulMethods) > 0 && count($nonRestfulMethods) === 0) {
                    $correctlyOrderedPresentMethods = array_filter(
                        self::RESTFUL_METHOD_NAMES,
                        function ($method) use ($restfulMethods) {
                            return in_array($method, $restfulMethods);
                        }
                    );

                    return array_values($correctlyOrderedPresentMethods) !== array_values($methodNames);
                }
            }

            return false;
        };
    }
}
