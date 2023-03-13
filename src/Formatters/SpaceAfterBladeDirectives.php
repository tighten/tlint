<?php

namespace Tighten\TLint\Formatters;

use PhpParser\Lexer;
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

    public function format(Parser $parser, Lexer $lexer): string
    {
        foreach ($this->getCodeLines() as $index => $codeLine) {
            $matches = [];

            preg_match_all(
                Linter::DIRECTIVE_SEARCH,
                $codeLine,
                $matches
            );

            for ($i = 0; isset($matches[0][$i]); $i++) {
                $match = [
                    $matches[0][$i],
                    $matches[1][$i],
                    $matches[2][$i],
                    $matches[3][$i] ?: null,
                    $matches[4][$i] ?: null,
                ];

                if (in_array($match[1] ?? null, Linter::SPACE_AFTER) && ($match[2] ?? null) === '') {
                    $codeLine = str_replace($match[0], "@{$match[1]} {$match[3]}", $codeLine);

                    $this->code = $this->replaceCodeLine($index + 1, $codeLine);
                }
            }
        }

        return $this->code;
    }
}
