<?php

namespace Tighten\TLint\Linters;

use PhpParser\Parser;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\CustomNode;

class NewLineAtEndOfFile extends BaseLinter
{
    public const DESCRIPTION = 'File should end with a new line';

    public function lint(Parser $parser)
    {
        $codeLines = $this->getCodeLines();

        if (end($codeLines) ?? null !== '') {
            return [new CustomNode(['startLine' => count($codeLines)])];
        }

        return [];
    }
}
