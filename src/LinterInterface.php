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
     * @return string
     */
    public function lintDescription();
}
