<?php

namespace Tighten\TLint;

use PhpParser\Lexer;
use PhpParser\Parser;

class BaseFormatter extends AbstractBase
{
    public function format(Parser $parser, Lexer $lexer): string
    {
        return $this->code;
    }

    public function getFormatDescription()
    {
        return $this->description;
    }

    public function setFormatDescription(string $description)
    {
        return $this->description = $description;
    }
}
