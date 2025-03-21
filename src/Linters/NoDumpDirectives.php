<?php

namespace Tighten\TLint\Linters;

use PhpParser\Parser;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\CustomNode;
use Tighten\TLint\Linters\Concerns\LintsBladeTemplates;

class NoDumpDirectives extends BaseLinter
{
    use LintsBladeTemplates;

    public const DESCRIPTION = 'Do not use the @dump or @dd directives.';

    public function lint(Parser $parser)
    {
        $foundNodes = [];

        foreach ($this->getCodeLines() as $line => $codeLine) {
            $directive = $this->getBladeDirective($codeLine);

            if ($directive === 'dd' || $directive === 'dump') {
                $foundNodes[] = new CustomNode(['startLine' => $line + 1]);
            }
        }

        return $foundNodes;
    }
}
