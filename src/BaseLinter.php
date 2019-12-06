<?php

namespace Tighten;

use PhpParser\Node;
use PhpParser\Parser;
use Tighten\Illuminate\Compilers\BladeCompiler;

class BaseLinter
{
    protected $description = 'No Description for Linter.';
    protected $extension;
    protected $code;
    protected $codeLines;

    public function __construct($code, $extension = '.php')
    {
        $this->extension = $extension;

        if ($extension === '.blade.php') {
            $bladeCompiler = new BladeCompiler(null, sys_get_temp_dir());
            $this->code = $bladeCompiler->compileString($code);
        } else {
            $this->code = $code;
        }

        $this->codeLines = preg_split('/\r\n|\r|\n/', $code);
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

    public function getExtension()
    {
        return $this->extension;
    }
}
