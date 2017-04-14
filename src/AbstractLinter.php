<?php

namespace Tighten;

use PhpParser\Parser;

class AbstractLinter implements LinterInterface
{
    protected $code;

    public function __construct($code)
    {
        $this->code = $code;
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
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
