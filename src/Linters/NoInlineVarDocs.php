<?php

namespace Tighten\TLint\Linters;

use PhpParser\Node;
use PhpParser\Parser;
use Tighten\TLint\BaseLinter;

class NoInlineVarDocs extends BaseLinter
{
    public const DESCRIPTION = 'No /** @var ClassName $var */ inline docs. [ref](https://github.com/tighten/tlint/issues/108)';

    public function lint(Parser $parser)
    {
        $visitor = $this->visitUsing($parser, function (Node $node) {
            if ($node->getDocComment() && strpos($node->getDocComment()->getText(), ' @var ') !== false) {
                return $node;
            }

            return false;
        });

        $startLines = [];

        return array_filter($visitor->getFoundNodes(), function (Node $node) use (&$startLines) {
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
