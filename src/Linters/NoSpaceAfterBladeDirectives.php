<?php

namespace Tighten\TLint\Linters;

use PhpParser\Parser;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\CustomNode;
use Tighten\TLint\Linters\Concerns\LintsBladeTemplates;

class NoSpaceAfterBladeDirectives extends BaseLinter
{
    use LintsBladeTemplates;

    public const DESCRIPTION = 'No space between blade template directive names and the opening paren:'
        . '`@section (` -> `@section(`';

    // https://github.com/illuminate/view/blob/master/Compilers/BladeCompiler.php#L500
    public const DIRECTIVE_SEARCH = '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x';

    public const NO_SPACE_AFTER = [
        'auth',
        'section',
        'hasSection',
        'sectionMissing',
        'yield',
        'extends',
        'isset',
        'empty',
        'include',
        'includeIf',
        'includeWhen',
        'includeUnless',
        'includeFirst',
        'each',
        'push',
        'pushOnce',
        'stack',
        'class',
        'checked',
        'selected',
        'required',
        'readonly',
        'disabled',
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
                if (in_array($match[1] ?? null, self::NO_SPACE_AFTER) && ($match[2] ?? null) !== '') {
                    $foundNodes[] = new CustomNode(['startLine' => $line + 1]);
                }
            }
        }

        return $foundNodes;
    }
}
