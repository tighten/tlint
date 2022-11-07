<?php

namespace Tighten\TLint\Formatters;

use PhpParser\Lexer;
use PhpParser\Parser;
use Tighten\TLint\BaseFormatter;
use Tighten\TLint\Linters\Concerns\LintsBladeTemplates;

class SpacesAroundBladeRenderContent extends BaseFormatter
{
    use LintsBladeTemplates;

    public const DESCRIPTION = 'Spaces around blade rendered content:'
        . '`{{1 + 1}}` -> `{{ 1 + 1 }}`';

    public function format(Parser $parser, Lexer $lexer)
    {
        foreach ($this->getCodeLines() as $codeLine) {
            $matchesNormal = [];

            preg_match_all(
                '/\{\{\s*(.+?)\s*\}\}/',
                $codeLine,
                $matchesNormal,
                PREG_SET_ORDER
            );

            foreach ($matchesNormal as $match) {
                if (isset($match[1])
                    && (substr($match[1], 0, 2) !== '--')
                    && $match[0] !== '{{ ' . $match[1] . ' }}'
                ) {
                    $this->code = str_replace($match[0], '{{ ' . $match[1] . ' }}', $this->code);
                }
            }

            $matchesRaw = [];

            preg_match_all(
                '/\{\!\!\s*(.+?)\s*\!\!\}/',
                $codeLine,
                $matchesRaw,
                PREG_SET_ORDER
            );

            foreach ($matchesRaw as $match) {
                if (isset($match[1]) && $match[0] !== '{!! ' . $match[1] . ' !!}') {
                    $this->code = str_replace($match[0], '{!! ' . $match[1] . ' !!}', $this->code);
                }
            }
        }

        return $this->code;
    }
}
