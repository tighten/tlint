<?php

namespace Tighten\TLint\Formatters;

use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Tighten\TLint\BaseFormatter;
use Tighten\TLint\Concerns\IdentifiesFacades;

class FullyQualifiedFacades extends BaseFormatter
{
    use IdentifiesFacades;

    public const DESCRIPTION = 'Import facades using their full namespace.';

    public function format(Parser $parser, Lexer $lexer)
    {
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new CloningVisitor);

        $oldStmts = $parser->parse($this->code);
        $oldTokens = $lexer->getTokens();

        $newStmts = $traverser->traverse($oldStmts);

        if (count($newStmts) && $newStmts[0] instanceof Namespace_) {
            $newStmts[0]->stmts = $this->transformFacadesToFullyQualified($newStmts[0]->stmts);
        } else {
            $newStmts = $this->transformFacadesToFullyQualified($newStmts);
        }

        return (new Standard)->printFormatPreserving($newStmts, $oldStmts, $oldTokens);
    }

    private function transformFacadesToFullyQualified(array $stmts)
    {
        return array_map(function (Node $node) {
            if (
                $node instanceof Use_
                && array_key_exists((string) $node->uses[0]->name, static::$aliases)
            ) {
                return new Use_([new UseUse(new Name(static::$aliases[(string) $node->uses[0]->name]))]);
            }

            return $node;
        }, $stmts);
    }
}
