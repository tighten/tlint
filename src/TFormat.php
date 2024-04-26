<?php

namespace Tighten\TLint;

use PhpParser\ParserFactory;

class TFormat
{
    private $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory)->createForHostVersion();
    }

    public function format(BaseFormatter $formatter)
    {
        return $formatter->format($this->parser);
    }
}
