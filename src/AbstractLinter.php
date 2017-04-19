<?php

namespace Tighten;

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use PhpParser\Parser;

class AbstractLinter implements LinterInterface
{
    protected $extension;
    protected $code;
    protected $codeLines;

    public function __construct($code, $extension = '.php')
    {
        $this->extension = $extension;

        if ($extension === '.blade.php') {
            $bladeCompiler = new BladeCompiler(new Filesystem(), sys_get_temp_dir());
            $this->code = $bladeCompiler->compileString($code);
        } else {
            $this->code = $code;
        }

        $this->codeLines = explode(PHP_EOL, $code);
    }

    public function lint(Parser $parser)
    {
        return [];
    }

    public function lintDescription()
    {
        return 'No Description for Linter.';
    }

    /**
     * Get the code to be parsed (this should always be raw php)
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get the line of code from the original source
     *
     * @param int $line
     * @return string
     */
    public function getCodeLine(int $line)
    {
        return $this->getCodeLines()[$line - 1];
    }

    public function getCodeLines()
    {
        return $this->codeLines;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }
}
