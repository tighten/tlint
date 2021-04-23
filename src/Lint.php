<?php

namespace Tighten\TLint;

use PhpParser\Node;

class Lint
{
    private $linter;
    private $node;

    public function __construct(BaseLinter $linter, Node $node)
    {
        $this->linter = $linter;
        $this->node = $node;
    }

    public function getLinter(): BaseLinter
    {
        return $this->linter;
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function __toString()
    {
        return '! ' . $this->linter->getLintDescription() . PHP_EOL
            . $this->node->getLine() . ' : `' . $this->linter->getCodeLine($this->node->getLine()) . '`';
    }
}
