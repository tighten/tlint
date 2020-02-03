<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;
use Tighten\Linters\Concerns\LintsControllers;
use Tighten\Linters\Concerns\LintsRoutesFiles;

class ViewWithOverArrayParameters extends BaseLinter
{
    use LintsControllers, LintsRoutesFiles {
        LintsControllers::appliesToPath as pathIsController;
        LintsRoutesFiles::appliesToPath as pathIsRoute;
    }

    public const description = 'Prefer `view(...)->with(...)` over `view(..., [...])`.';

    public static function appliesToPath(string $path): bool
    {
        return static::pathIsController($path) || static::pathIsRoute($path);
    }

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            return $node instanceof FuncCall
                && $node->name instanceof Node\Name
                && $node->name->toString() === 'view'
                && ($node->args[1]->value ?? null) instanceof Array_;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
