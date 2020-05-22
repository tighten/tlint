<?php

namespace Tighten;

use PhpParser\Node;
use PhpParser\Parser;

class BaseLinter
{
    public const description = 'No Description for Linter.';

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

    public function lint(Parser $parser)
    {
        return [];
    }

    public function getLintDescription()
    {
        return $this->description;
    }

    public function setLintDescription(string $description)
    {
        return $this->description = $description;
    }

    public function getCode() : string
    {
        return $this->code;
    }

    public function getCodeLine(int $line)
    {
        return $this->getCodeLines()[$line - 1];
    }

    public function getCodeLinesFromNode(Node $node)
    {
        return array_reduce(
            range($node->getStartLine(), $node->getEndLine()),
            function ($carry, $line) {
                return $carry . $this->getCodeLine($line);
            },
            ''
        );
    }

    public function getCodeLines()
    {
        return $this->codeLines;
    }

    public function getFilename()
    {
        return $this->filename;
    }
}
