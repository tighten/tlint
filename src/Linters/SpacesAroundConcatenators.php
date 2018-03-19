<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class SpacesAroundConcatenators extends BaseLinter
{
    protected $description = 'There should be spaces around `.` concatenations';

    /**
     * When multiple concatenations are present in a single line, they're recorded in an unpredictable way.
     * - the start token is always the left-most concat's start.
     * - the end token is the end of the farthest right concat.
     * Concats are traversed in right to left order, so the longest/farthest right is reported first.
     *
     * "foo" . $bar . "baz" would produce 2 concats:
     * one beginning at token 4 and ending at token 12
     * one beginning at token 4 and ending at token 8
     */
    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            if ($node instanceof Concat) {
                $stringLiteralCorrectlyFormattedConcatCount = 0;
                $concatCount = 1;
                $left = $node->left;

                if ($left instanceof String_) {
//                    $stringLiteralCorrectlyFormattedConcatCount += substr_count($left->value, ' . ');
                    preg_match_all('/(?<= )(?<!  |^)\.(?= )(?!  |$)/', $left->value, $matches);
                    $stringLiteralCorrectlyFormattedConcatCount += count($matches[0]);
                }

                while ($left instanceof Concat) {
                    $concatCount += 1;

                    $left = $left->left;
                }

//                $correctlyFormattedCodeLineConcatCount = substr_count($this->getCodeLine($node->getLine()), ' . ');
                preg_match_all('/(?<= )(?<!  |^)\.(?= )(?!  |$)/', $this->getCodeLine($node->getLine()), $matches);
                $correctlyFormattedCodeLineConcatCount = count($matches[0]);
//                dd(count($matches));
                // (?:[^ ] \. [^ ])

                /**
                 * Check for non-first line start of line concats space after and subtract from
                 * $concatCount if they are correct.
                 */
                if ($node->getEndLine() > $node->getStartLine()) {
                    foreach (range($node->getStartLine() + 1, $node->getEndLine()) as $lineNumber) {
                        echo $this->getCodeLine($lineNumber) . PHP_EOL;
                        preg_match_all('/^\s*\.(?= )(?!  |$)/', $this->getCodeLine($lineNumber), $matches);
                        $concatCount -= count($matches[0]);
                        var_dump($concatCount . 'as');
                    }
                }

                dd(
                    $this->getCodeLine($node->getLine()),
                    $correctlyFormattedCodeLineConcatCount,
                    $stringLiteralCorrectlyFormattedConcatCount,
                    $concatCount
                );

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
