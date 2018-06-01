<?php

namespace Tighten\Linters;

use PhpParser\Parser;
use Tighten\BaseLinter;
use Tighten\CustomNode;

class SpaceAfterBladeDirectives extends BaseLinter
{
    private const SPACE_AFTER = [
        'if',
        'elseif',
        'for',
        'foreach',
        'unless',
        'forelse',
    ];

    protected $description = 'Put a space between blade control structure names and the opening paren:'
        . '`@if(` -> `@if (`';

    public function lint(Parser $parser)
    {
        $foundNodes = [];

        foreach ($this->getCodeLines() as $line => $codeLine) {
            $matches = [];

            // https://github.com/illuminate/view/blob/master/Compilers/BladeCompiler.php#L271
            preg_match(
                '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x',
                $codeLine,
                $matches
            );

            if (in_array($matches[1] ?? null, self::SPACE_AFTER) && ($matches[2] ?? null) === '') {
                $foundNodes[] = new CustomNode(['startLine' => $line + 1]);
            }
        }

        return $foundNodes;
    }
}
