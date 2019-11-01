<?php

namespace Tighten;

use PhpParser\Lexer;
use PhpParser\Parser;

class BaseFormatter
{
    protected $description = 'No Description for Formatter.';
    protected $extension;
    protected $code;
    protected $codeLines;

    public function __construct($code, $extension = '.php')
    {
        $this->extension = $extension;
        $this->code = $code;
        $this->codeLines = preg_split('/\r\n|\r|\n/', $code);
    }

    public function format(Parser $parser, Lexer $lexer)
    {
        return [];
    }

    public function getFormatDescription()
    {
        return $this->description;
    }

    public function setFormatDescription(string $description)
    {
        return $this->description = $description;
    }

    public function getCode() : string
    {
        return $this->code;
    }

    public function getExtension()
    {
        return $this->extension;
    }
}
