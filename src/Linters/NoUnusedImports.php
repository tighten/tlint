<?php

namespace Tighten\TLint\Linters;

use PhpParser\Parser;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Concerns\IdentifiesImports;

class NoUnusedImports extends BaseLinter
{
    use IdentifiesImports;

    public const DESCRIPTION = 'There should be no unused imports.';

    public function lint(Parser $parser)
    {
        return $this->getUnusedImportNodes(
            $parser->parse($this->code)
        );
    }
}
