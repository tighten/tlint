<?php

namespace Tighten\TLint;

use PhpParser\Parser;

class BaseFormatter extends AbstractBase
{
    public function format(Parser $parser): string
    {
        return $this->code;
    }

    public function getFormatDescription(): string
    {
        return $this->description;
    }

    public function setFormatDescription(string $description): string
    {
        return $this->description = $description;
    }

    public function replaceCodeLine(int $line, string $replacement): string
    {
        $this->codeLines[$line - 1] = $replacement;

        return implode(PHP_EOL, $this->codeLines);
    }
}
