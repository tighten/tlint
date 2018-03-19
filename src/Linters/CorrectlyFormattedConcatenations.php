<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class CorrectlyFormattedConcatenations extends BaseLinter
{
    protected $description = 'There should be 1 space around `.` concatenations, and additional lines should'
        . ' always start with a `.`';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            static $startLine;

            if ($node instanceof Concat) {
                /**
                 * Stop multiple lints for single concat
                 */
                if ($startLine === $node->getStartLine()) {
                    return false;
                }

                $startLine = $node->getStartLine();
                $stringLiteralCorrectlyFormattedConcatCount = 0;
                $concatCount = 1;
                $left = $node->left;

                if ($left instanceof String_) {
                    preg_match_all('/(?<= )(?<!  |^)\.(?= )(?!  |$)/', $left->value, $matches);
                    $stringLiteralCorrectlyFormattedConcatCount += count($matches[0]);
                }

                while ($left instanceof Concat) {
                    $concatCount += 1;

                    $left = $left->left;
                }

                $totalCodeLine = $node->getEndLine() > $node->getStartLine()
                    ? $this->getCodeLinesFromNode($node)
                    : $this->getCodeLine($node->getLine());
                preg_match_all('/(?<= )(?<!  |^)\.(?= )(?!  |$)/', $totalCodeLine, $matches);
                $correctlyFormattedCodeLineConcatCount = count($matches[0]);

                /**
                 * Check for non-first line start of line concats space after and subtract from
                 * $concatCount if they are correct.
                 */
                if ($node->getEndLine() > $node->getStartLine()) {
                    foreach (range($node->getStartLine() + 1, $node->getEndLine()) as $lineNumber) {
                        preg_match_all('/^\s*\.(?= )(?!  |$)/', $this->getCodeLine($lineNumber), $matches);
                        $concatCount -= count($matches[0]);
                    }
                }

                return $correctlyFormattedCodeLineConcatCount - $stringLiteralCorrectlyFormattedConcatCount
                    < $concatCount;
            }

            return false;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
