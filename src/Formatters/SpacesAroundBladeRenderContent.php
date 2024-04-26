<?php

namespace Tighten\TLint\Formatters;

use PhpParser\Parser;
use Tighten\TLint\BaseFormatter;
use Tighten\TLint\Linters\SpacesAroundBladeRenderContent as Linter;

class SpacesAroundBladeRenderContent extends BaseFormatter
{
    public const DESCRIPTION = Linter::DESCRIPTION;

    public static function appliesToPath(string $path, array $configPaths): bool
    {
        return Linter::appliesToPath($path, $configPaths);
    }

    public function format(Parser $parser): string
    {
        foreach ($this->getCodeLines() as $index => $codeLine) {
            $matchesNormal = [];

            preg_match_all(
                Linter::SEARCH_NORMAL,
                $codeLine,
                $matchesNormal,
                PREG_SET_ORDER
            );

            foreach ($matchesNormal as $match) {
                if (isset($match[1])
                    && (substr($match[1], 0, 2) !== '--')
                    && $match[0] !== '{{ ' . $match[1] . ' }}'
                ) {
                    $codeLine = str_replace($match[0], '{{ ' . $match[1] . ' }}', $codeLine);

                    $this->code = $this->replaceCodeLine($index + 1, $codeLine);
                }
            }

            $matchesRaw = [];

            preg_match_all(
                Linter::SEARCH_RAW,
                $codeLine,
                $matchesRaw,
                PREG_SET_ORDER
            );

            foreach ($matchesRaw as $match) {
                if (isset($match[1]) && $match[0] !== '{!! ' . $match[1] . ' !!}') {
                    $codeLine = str_replace($match[0], '{!! ' . $match[1] . ' !!}', $codeLine);

                    $this->code = $this->replaceCodeLine($index + 1, $codeLine);
                }
            }
        }

        return $this->code;
    }
}
