<?php

namespace Tighten\TLint;

use PhpParser\Node;
use PhpParser\ParserFactory;

class TLint
{
    private $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory)->createForHostVersion();
    }

    public function lint(BaseLinter $linter)
    {
        return array_map(function (Node $node) use ($linter) {
            return new Lint($linter, $node);
        }, $linter->lint($this->parser));
    }
}
