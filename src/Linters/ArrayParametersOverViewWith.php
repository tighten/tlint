<?php

namespace Tighten\TLint\Linters;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Linters\Concerns\LintsControllers;
use Tighten\TLint\Linters\Concerns\LintsRoutesFiles;

class ArrayParametersOverViewWith extends BaseLinter
{
    use LintsControllers, LintsRoutesFiles {
        LintsControllers::appliesToPath as pathIsController;
        LintsRoutesFiles::appliesToPath as pathIsRoute;
    }

    public const DESCRIPTION = 'Prefer `view(..., [...])` over `view(...)->with(...)`.';

    public static function appliesToPath(string $path): bool
    {
        return static::pathIsController($path) || static::pathIsRoute($path);
    }

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            return $node instanceof Node\Expr\MethodCall
                && $node->var instanceof FuncCall
                && $node->var->name instanceof Node\Name
                && $node->var->name->toString() === 'view'
                && $node->name instanceof Node\Identifier
                && $node->name->toString() === 'with';
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
