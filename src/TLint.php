<?php

namespace Tighten;

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeAbstract;
use PhpParser\ParserFactory;

class TLint
{
    private $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
    }

    /**
     * @param LinterInterface $linter
     * @return Lint[]
     */
    public function lint(LinterInterface $linter)
    {
        return array_map(function (Node $node) use ($linter) {
            return new Lint($linter, $node);
        }, $linter->lint($this->parser));
    }
}
