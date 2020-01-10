<?php

namespace Tighten\Linters;

use PhpParser\Parser;
use Tighten\BaseLinter;
use Tighten\Concerns\IdentifiesImports;

class NoUnusedImports extends BaseLinter
{
    use IdentifiesImports;

    public const description = 'There should be no unused imports.';

    public function lint(Parser $parser)
    {
        return $this->getUnusedImportNodes(
            $parser->parse($this->code)
        );
    }
}
