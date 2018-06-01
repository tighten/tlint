<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class NoInlineVarDocs extends BaseLinter
{
    protected $description = 'No /** @var ClassName $var */ inline docs.';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $useStatementsVisitor = new FindingVisitor(function (Node $node) use (&$useStatements) {

            if ($node->getDocComment() && str_contains($node->getDocComment()->getText(), ' @var ')) {
                return $node;
            }

            return false;
        });

        $traverser->addVisitor($useStatementsVisitor);

        $traverser->traverse($parser->parse($this->code));

        return collect($useStatementsVisitor->getFoundNodes())->uniqueStrict(function (Node $node) {
            return $node->getStartLine();
        })->toArray();
    }
}
