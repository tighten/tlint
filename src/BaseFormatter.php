<?php

namespace Tighten;

use PhpParser\Lexer;
use PhpParser\Parser;

class BaseFormatter
{
    public const description = 'No Description for Formatter.';

    protected $description;
    protected $filename;
    protected $code;
    protected $codeLines;

    public function __construct($code, $filename = null)
    {
        $this->description = static::description;
        $this->filename = $filename;
        $this->code = $code;
        $this->codeLines = preg_split('/\r\n|\r|\n/', $code);
    }

    public static function appliesToPath(string $path): bool
    {
        return true;
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
