<?php

namespace Tighten\TLint\Formatters;

use Illuminate\Support\Str;
use PhpParser\Parser;
use Tighten\TLint\BaseFormatter;
use Tighten\TLint\Linters\NoSpaceAfterBladeDirectives as Linter;

class NoSpaceAfterBladeDirectives extends BaseFormatter
{
    public const DESCRIPTION = Linter::DESCRIPTION;

    public static function appliesToPath(string $path, array $configPaths): bool
    {
        return Linter::appliesToPath($path, $configPaths);
    }

    public function format(Parser $parser): string
    {
        foreach ($this->getCodeLines() as $index => $codeLine) {
            $matches = [];

            preg_match_all(
                Linter::DIRECTIVE_SEARCH,
                $codeLine,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $match) {
                if (in_array($match[1] ?? null, Linter::NO_SPACE_AFTER) && ($match[2] ?? null) !== '') {
                    // @auth can be used with or without parens ie: @auth('admin') or @auth
                    // if match[3] contains parens, we need to remove the space otherwise leave it alone
                    $match[3] = Str::containsAll($match[3] ?? '', ['(', ')']) ? $match[3] : $match[2] . ($match[3] ?? '');
                    $codeLine = str_replace($match[0], "@{$match[1]}{$match[3]}", $codeLine);

                    $this->code = $this->replaceCodeLine($index + 1, $codeLine);
                }
            }
        }

        return $this->code;
    }
}
