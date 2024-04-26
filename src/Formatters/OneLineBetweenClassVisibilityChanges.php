<?php

namespace Tighten\TLint\Formatters;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Tighten\TLint\BaseFormatter;
use Tighten\TLint\Linters\OneLineBetweenClassVisibilityChanges as Linter;

class OneLineBetweenClassVisibilityChanges extends BaseFormatter
{
    public const DESCRIPTION = Linter::DESCRIPTION;

    public static function appliesToPath(string $path, array $configPaths): bool
    {
        return Linter::appliesToPath($path, $configPaths);
    }

    public function format(Parser $parser): string
    {
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new CloningVisitor);

        $oldStmts = $parser->parse($this->code);
        $oldTokens = $parser->getTokens();

        $newStmts = $traverser->traverse($oldStmts);

        $traverser = new NodeTraverser;
        $traverser->addVisitor($this->visitor());
        $newStmts = $traverser->traverse($newStmts);

        return preg_replace('/\ *\r?\n/', PHP_EOL, (new Standard)->printFormatPreserving($newStmts, $oldStmts, $oldTokens));
    }

    private function visitor(): NodeVisitorAbstract
    {
        return new class extends NodeVisitorAbstract
        {
            private $previousNode;

            public function beforeTraverse(array $nodes)
            {
                $this->previousNode = null;

                return null;
            }

            public function enterNode(Node $node)
            {
                if ($node instanceof Node\Stmt\Class_) {
                    $this->previousNode = null;

                    return null;
                }

                if (! $node instanceof ClassConst && ! $node instanceof Property) {
                    return null;
                }

                // Ignore the very first node
                if (is_null($this->previousNode)) {
                    $this->previousNode = $node;

                    return null;
                }

                // Ignore nodes with exactly the same visibility
                if ($this->previousNode->flags === $node->flags) {
                    $this->previousNode = $node;

                    return null;
                }

                // Ignore nodes separated by exactly one blank line and no comments
                if ($node->getStartLine() - $this->previousNode->getEndLine() === 2 && empty($node->getComments())) {
                    $this->previousNode = $node;

                    return null;
                }

                if (! empty($comments = $node->getComments())) {
                    // Get the line numbers of all lines between this node and the previous one
                    $allLinesBetweenNodes = range($this->previousNode->getEndLine() + 1, $node->getStartLine() - 1);

                    // Get the line numbers of all lines between this node and the previous one that are part of any comment
                    $commentLines = array_merge(...array_map(function ($comment) {
                        return range($comment->getStartLine(), $comment->getEndLine());
                    }, $comments));

                    // Diff <all the lines> and <the commented lines> to find the lines between these two nodes that are blank
                    // Ignore nodes separated by comments when there is exactly one blank line within the comments
                    if (count(array_diff($allLinesBetweenNodes, $commentLines)) === 1) {
                        $this->previousNode = $node;

                        return null;
                    }
                }

                $node->setAttribute('comments', [new Comment(''), ...$node->getComments()]);

                return $node;
            }
        };
    }
}
