<?php

namespace Tighten\TLint\Formatters;

use PhpParser\Lexer;
use PhpParser\Parser;
use Tighten\TLint\BaseFormatter;
use Tighten\TLint\Linters\Concerns\LintsBladeTemplates;

class SpaceAfterBladeDirectives extends BaseFormatter
{
    use LintsBladeTemplates;

    public const DESCRIPTION = 'Puts a space between blade control structure names and the opening paren:'
        . '`@if(` -> `@if (`';

    protected const SPACE_AFTER = [
        'if',
        'elseif',
        'unless',
        'for',
        'foreach',
        'forelse',
        'while',
    ];

    public function format(Parser $parser, Lexer $lexer): string
    {
        foreach ($this->getCodeLines() as $line => $codeLine) {
            $matches = [];

            // https://github.com/illuminate/view/blob/master/Compilers/BladeCompiler.php#L500
            preg_match(
                '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x',
                $codeLine,
                $matches
            );

            if (in_array($matches[1] ?? null, self::SPACE_AFTER) && ($matches[2] ?? null) === '') {
                $this->code = str_replace($matches[0], "@{$matches[1]} {$matches[3]}", $this->code);
            }
        }

        return $this->code;
    }
}
