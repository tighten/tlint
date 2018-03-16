<?php

namespace Tighten\Linters;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class SpacesAroundConcatenators extends BaseLinter
{
    protected $description = 'There should be spaces around `.` concatenators';

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
                return $node->getEndTokenPos() - $node->getStartTokenPos() !== 4;
            }

            return false;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
