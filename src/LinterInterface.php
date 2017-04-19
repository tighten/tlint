<?php

namespace Tighten;

use PhpParser\Parser;

interface LinterInterface
{
    /**
     * @param Parser $parser
     * @return @return Lint[]
     */
    public function lint(Parser $parser);

    /**
     * @param int $line
     * @return string
     */
    public function getCodeLine(int $line);

    /**
     * @return string
     */
    public function lintDescription();

    /**
     * @return string
     */
    public function getExtension();
}
