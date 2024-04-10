<?php

namespace Tighten\TLint;

use PhpParser\Lexer\Emulative;
use PhpParser\Parser\Php7;
use PhpParser\PhpVersion;
use PHPUnit\Runner\Version;
use PHPUnit\TextUI\XmlConfiguration\Php;

class TFormat
{
    private $parser;
    private $lexer;

    public function __construct()
    {
        $this->lexer = new Emulative(phpVersion: PhpVersion::getHostVersion());
        $this->parser = new Php7($this->lexer, PhpVersion::getHostVersion());
    }

    public function format(BaseFormatter $formatter)
    {
        return $formatter->format($this->parser, $this->lexer);
    }
}
