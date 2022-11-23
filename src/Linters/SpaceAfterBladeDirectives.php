<?php

namespace Tighten\TLint\Linters;

use PhpParser\Parser;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\CustomNode;
use Tighten\TLint\Linters\Concerns\LintsBladeTemplates;

class SpaceAfterBladeDirectives extends BaseLinter
{
    use LintsBladeTemplates;

    public const DESCRIPTION = 'Put a space between blade control structure names and the opening paren:'
        . '`@if(` -> `@if (`';

    // https://github.com/illuminate/view/blob/master/Compilers/BladeCompiler.php#L500
    public const DIRECTIVE_SEARCH = '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x';

    public const SPACE_AFTER = [
        'if',
        'elseif',
        'unless',
        'for',
        'foreach',
        'forelse',
        'while',
    ];

    public function lint(Parser $parser)
    {
        $foundNodes = [];

        foreach ($this->getCodeLines() as $line => $codeLine) {
            $matches = [];

            preg_match_all(
                self::DIRECTIVE_SEARCH,
                $codeLine,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $match) {
                if (in_array($match[1] ?? null, self::SPACE_AFTER) && ($match[2] ?? null) === '') {
                    $foundNodes[] = new CustomNode(['startLine' => $line + 1]);
                }
            }
        }

        return $foundNodes;
    }
}
