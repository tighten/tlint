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
    public function getCodeLine(int $line);
    public function getLintDescription();
    public function setLintDescription(string $description);
    public function getExtension();
}
