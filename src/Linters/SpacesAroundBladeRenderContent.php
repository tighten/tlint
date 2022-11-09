<?php

namespace Tighten\TLint\Linters;

use PhpParser\Parser;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\CustomNode;
use Tighten\TLint\Linters\Concerns\LintsBladeTemplates;

class SpacesAroundBladeRenderContent extends BaseLinter
{
    use LintsBladeTemplates;

    public const DESCRIPTION = 'Spaces around blade rendered content:'
        . '`{{1 + 1}}` -> `{{ 1 + 1 }}`';

    public const SEARCH_NORMAL = '/\{\{\s*(.+?)\s*\}\}/';

    public const SEARCH_RAW = '/\{\!\!\s*(.+?)\s*\!\!\}/';

    public function lint(Parser $parser)
    {
        $foundNodes = [];

        foreach ($this->getCodeLines() as $line => $codeLine) {
            $matchesNormal = [];

            preg_match_all(
                self::SEARCH_NORMAL,
                $codeLine,
                $matchesNormal,
                PREG_SET_ORDER
            );

            foreach ($matchesNormal as $match) {
                if (isset($match[1])
                    && (substr($match[1], 0, 2) !== '--')
                    && $match[0] !== '{{ ' . $match[1] . ' }}') {
                    $foundNodes[] = new CustomNode(['startLine' => $line + 1]);
                }
            }

            $matchesRaw = [];

            preg_match_all(
                self::SEARCH_RAW,
                $codeLine,
                $matchesRaw,
                PREG_SET_ORDER
            );

            foreach ($matchesRaw as $match) {
                if (isset($match[1]) && $match[0] !== '{!! ' . $match[1] . ' !!}') {
                    $foundNodes[] = new CustomNode(['startLine' => $line + 1]);
                }
            }
        }

        return $foundNodes;
    }
}
