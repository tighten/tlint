<?php

namespace Tighten\TLint\Formatters;

use PhpParser\Parser;
use Tighten\TLint\BaseFormatter;
use Tighten\TLint\Linters\SpaceAfterBladeDirectives as Linter;

class SpaceAfterBladeDirectives extends BaseFormatter
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
                $match = array_pad($match, 5, null);

                if (in_array($match[1], Linter::SPACE_AFTER) && $match[2] === '') {
                    $codeLine = str_replace($match[0], "@{$match[1]} {$match[3]}", $codeLine);

                    $this->code = $this->replaceCodeLine($index + 1, $codeLine);
                }
            }
        }

        return $this->code;
    }
}
