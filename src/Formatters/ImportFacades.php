<?php

namespace Tighten\Formatters;

use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Tighten\BaseFormatter;
use Tighten\Concerns\IdentifiesFacades;

class ImportFacades extends BaseFormatter
{
    use IdentifiesFacades;

    public const description = 'Import facades using their full namespace.';

    public function format(Parser $parser, Lexer $lexer)
    {
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new CloningVisitor);

        $originalStatements = $parser->parse($this->code);
        $tokens = $lexer->getTokens();

        $statements = array_map(function (Node $node) {
            if (
                $node instanceof Use_
                && array_key_exists((string) $node->uses[0]->name, static::$aliases)
            ) {
                return new Use_([new UseUse(new Name(static::$aliases[(string) $node->uses[0]->name]))]);
            }

            return $node;
        }, $traverser->traverse($originalStatements));

        return (new Standard)->printFormatPreserving($statements, $originalStatements, $tokens);
    }
}
