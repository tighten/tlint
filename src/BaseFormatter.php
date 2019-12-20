<?php

namespace Tighten;

use PhpParser\Lexer;
use PhpParser\Parser;

class BaseFormatter
{
    protected $description = 'No Description for Formatter.';
    protected $filename;
    protected $code;
    protected $codeLines;

    public static function appliesToPath(string $path): bool
    {
        return true;
    }

    public function __construct($code, $filename = null)
    {
        $this->filename = $filename;
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

    public function getFilename()
    {
        return $this->filename;
    }
}
