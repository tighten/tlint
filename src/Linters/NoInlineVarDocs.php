<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class NoInlineVarDocs extends BaseLinter
{
    public const description = 'No /** @var ClassName $var */ inline docs.';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $useStatementsVisitor = new FindingVisitor(function (Node $node) use (&$useStatements) {

            if ($node->getDocComment() && strpos($node->getDocComment()->getText(), ' @var ') !== false) {
                return $node;
            }

            return false;
        });

        $traverser->addVisitor($useStatementsVisitor);

        $traverser->traverse($parser->parse($this->code));


        $startLines = [];
        return array_filter($useStatementsVisitor->getFoundNodes(), function (Node $node) use (&$startLines) {
            if (in_array($node->getStartLine(), $startLines)) {
                return false;
            }

            $startLines[] = $node->getStartLine();

            return $node->getStartLine();
        });
//        return collect($useStatementsVisitor->getFoundNodes())->uniqueStrict(function (Node $node) {
//            return $node->getStartLine();
//        })->toArray();
    }
}
