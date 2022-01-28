<?php

namespace Tighten\TLint\Linters;

use PhpParser\Node;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Parser;
use Tighten\TLint\BaseLinter;

class AlphabeticalImports extends BaseLinter
{
    public const DESCRIPTION = 'Imports should be ordered alphabetically.';

    public function lint(Parser $parser)
    {
        $useStatements = [];

        $visitor = $this->visitUsing($parser, function (Node $node) use (&$useStatements) {
            if ($node instanceof Node\Stmt\UseUse) {
                $useStatements[] = $node;
            }

            return false;
        });

        if (! empty($useStatements)) {
            $importStrings = array_map(function (UseUse $useStatement) {
                return $useStatement->name->toString();
            }, $useStatements);

            $alphabetical = $importStrings;
            asort($alphabetical, SORT_STRING|SORT_FLAG_CASE);

            return array_values($importStrings) !== array_values($alphabetical) ? [$useStatements[0]] : [];
        }

        return $visitor->getFoundNodes();
    }
}
