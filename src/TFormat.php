<?php

namespace Tighten;

use PhpParser\Lexer\Emulative;
use PhpParser\Parser\Php7;

class TFormat
{
    private $parser;
    private $lexer;

    public function __construct()
    {
        $this->lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine', 'endLine',
                'startTokenPos', 'endTokenPos',
            ],
        ]);
        $this->parser = new Php7($this->lexer);
    }

    public function format(BaseFormatter $formatter)
    {
        return $formatter->format($this->parser, $this->lexer);
    }
}
