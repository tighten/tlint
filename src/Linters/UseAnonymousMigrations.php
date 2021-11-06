<?php

namespace Tighten\TLint\Linters;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Linters\Concerns\LintsMigrations;

class UseAnonymousMigrations extends BaseLinter
{
    use LintsMigrations;

    public const DESCRIPTION = 'Prefer anonymous class migrations.';

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
