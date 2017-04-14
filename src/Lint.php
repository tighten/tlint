<?php

namespace Tighten;

use PhpParser\Node;

class Lint
{
    private $linter;
    private $node;

    public function __construct(LinterInterface $linter, Node $node)
    {
        $this->linter = $linter;
        $this->node = $node;
    }

    public function __toString()
    {
        return '! ' . $this->linter->lintDescription() . PHP_EOL
            . $this->node->getLine() . ' : `' . $this->linter->getCodeLine($this->node->getLine()) . '`';
    }

    /**
     * @return LinterInterface
     */
    public function getLinter(): LinterInterface
    {
        return $this->linter;
    }

    /**
     * @return Node
     */
    public function getNode(): Node
    {
        return $this->node;
    }
}
