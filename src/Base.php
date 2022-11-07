<?php

namespace Tighten\TLint;

use PhpParser\Node;

class Base
{
    public const DESCRIPTION = 'No Description.';

    protected $description;
    protected $filename;
    protected $code;
    protected $codeLines;

    public function __construct($code, $filename = null)
    {
        $this->description = static::DESCRIPTION;
        $this->filename = $filename;
        $this->code = $code;
        $this->codeLines = preg_split('/\r\n|\r|\n/', $code);
    }

    public static function appliesToPath(string $path, array $configPaths): bool
    {
        return true;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getCodeLines()
    {
        return $this->codeLines;
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
}
