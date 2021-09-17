<?php

namespace Tighten\TLint\Linters;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Linters\Concerns\LintsTests;

class NoMethodVisibilityInTests extends BaseLinter
{
    use LintsTests;

    public const DESCRIPTION = 'There should be no method visibility in test methods. [ref](https://github.com/tighten/tlint/issues/106#issuecomment-537952774)';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            static $extends = null;

            if ($node instanceof Class_) {
                $extends = $node->extends;
            }

            return $extends
                && $extends->toString() === 'TestCase'
                && $node instanceof Node\Stmt\ClassMethod
                && ! in_array($node->name->toString(), ['setUp', 'setUpBeforeClass', 'tearDown', 'tearDownAfterClass'])
                && (bool) ($node->flags & Class_::VISIBILITY_MODIFIER_MASK);
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
