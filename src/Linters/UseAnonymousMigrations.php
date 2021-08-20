<?php

namespace Tighten\TLint\Linters;

use PhpParser\Node;
use PhpParser\Parser;
use PhpParser\NodeTraverser;
use Tighten\TLint\BaseLinter;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitor\FindingVisitor;
use Tighten\TLint\Linters\Concerns\LintsMigrations;

class UseAnonymousMigrations extends BaseLinter
{
    use LintsMigrations;

    public const description = 'Prefer anonymous class migrations.';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            return $node instanceof Class_
                && $node->extends->toString() === 'Migration'
                && $node->name;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
