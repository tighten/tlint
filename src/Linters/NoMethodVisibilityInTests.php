<?php

namespace Tighten\TLint\Linters;

use Closure;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Linters\Concerns\LintsTests;

class NoMethodVisibilityInTests extends BaseLinter
{
    use LintsTests;

    public const DESCRIPTION = 'There should be no method visibility in test methods. [ref](https://github.com/tighten/tlint/issues/106#issuecomment-537952774)';

    protected function visitor(): Closure
    {
        return function (Node $node) {
            static $extends = null;

            if ($node instanceof Class_) {
                $extends = $node->extends;
            }

            return $extends
                && $extends->toString() === 'TestCase'
                && $node instanceof Node\Stmt\ClassMethod
                && ! in_array($node->name->toString(), ['setUp', 'setUpBeforeClass', 'tearDown', 'tearDownAfterClass'])
                && (bool) ($node->flags & Class_::VISIBILITY_MODIFIER_MASK);
        };
    }
}
