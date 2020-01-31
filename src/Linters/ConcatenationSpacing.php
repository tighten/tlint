<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class ConcatenationSpacing extends BaseLinter
{
    public const description = 'There should be 1 space around `.` concatenations, and additional lines should'
        . ' always start with a `.`';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            static $startLine;

            if ($node instanceof Concat) {
                /**
                 * Stop multiple lints triggers for a single concat
                 */
                if ($startLine === $node->getStartLine()) {
                    return false;
                }

                $startLine = $node->getStartLine();
                $stringLiteralCorrectlyFormattedConcatCount = 0;
                $concatCount = 1;
                $left = $node->left;

                /**
                 * Count correctly formatted raw string concats
                 */
                if ($left instanceof String_) {
                    $stringLiteralCorrectlyFormattedConcatCount += $this->countCorrectlyFormattedConcats($left->value);
                }

                /**
                 * Count concat operations via parser tokens
                 */
                while ($left instanceof Concat) {
                    $concatCount += 1;

                    $left = $left->left;
                }

                $totalCodeLine = $node->getEndLine() > $node->getStartLine()
                    ? $this->getCodeLinesFromNode($node)
                    : $this->getCodeLine($node->getLine());

                $correctlyFormattedCodeLineConcatCount = $this->countCorrectlyFormattedConcats($totalCodeLine);

                $concatCount -= $this->countAdditionalLinesThatStartWithConcat($node);

                /**
                 * Compare the parsed vs raw string (correct) count
                 */
                return $correctlyFormattedCodeLineConcatCount - $stringLiteralCorrectlyFormattedConcatCount
                    < $concatCount;
            }

            return false;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }

    private function countCorrectlyFormattedConcats(string $string)
    {
        preg_match_all('/(?<= )(?<!  |^)\.(?= )(?!  |$)/', $string, $matches);

        return count($matches[0] ?? []);
    }

    /**
     * Count non-first line start of line concats (with space after)
     */
    private function countAdditionalLinesThatStartWithConcat(Node $node)
    {
        $concatCount = 0;

        if ($node->getEndLine() > $node->getStartLine()) {
            foreach (range($node->getStartLine() + 1, $node->getEndLine()) as $lineNumber) {
                preg_match_all('/^\s*\.(?= )(?!  |$)/', $this->getCodeLine($lineNumber), $matches);
                $concatCount += count($matches[0] ?? []) > 0 ? 1 : 0;
            }
        }

        return $concatCount;
    }
}
