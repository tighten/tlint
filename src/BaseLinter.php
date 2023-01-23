<?php

namespace Tighten\TLint;

use Closure;
use LogicException;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;

class BaseLinter extends AbstractBase
{
    public function lint(Parser $parser)
    {
        return $this->traverseAutomatically($parser);
    }

    public function getLintDescription()
    {
        return $this->description;
    }

    public function setLintDescription(string $description)
    {
        return $this->description = $description;
    }

    protected function visitor(): Closure
    {
        throw new LogicException('Custom linters must override either the `lint` or `visitor` method.');
    }

    protected function visitUsing(Parser $parser, Closure $callback): FindingVisitor
    {
        $traverser = new NodeTraverser;
        $traverser->addVisitor($visitor = new FindingVisitor($callback));
        $traverser->traverse($parser->parse($this->code));

        return $visitor;
    }

    private function traverseAutomatically(Parser $parser): array
    {
        return $this->visitUsing($parser, $this->visitor())->getFoundNodes();
    }
}
