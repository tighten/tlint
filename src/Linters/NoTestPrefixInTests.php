<?php

namespace Tighten\TLint\Linters;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Linters\Concerns\LintsTests;

class NoTestPrefixInTests extends BaseLinter
{
    use LintsTests;

    public const DESCRIPTION = 'Test methods should be annotated using /** @test */, not prefixed with "test".';

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
                && $node->isPublic()
                && str_starts_with($node->name->toString(), 'test');
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
