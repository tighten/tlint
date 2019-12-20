<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Parser;
use Tighten\BaseLinter;

class AlphabeticalImports extends BaseLinter
{
    public const description = 'Imports should be ordered alphabetically (the first segment).';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $useStatements = [];

        $useStatementsVisitor = new FindingVisitor(function (Node $node) use (&$useStatements) {
            if ($node instanceof Node\Stmt\UseUse) {
                $useStatements[] = $node;
            }

            return false;
        });

        $traverser->addVisitor($useStatementsVisitor);

        $traverser->traverse($parser->parse($this->code));

        if (! empty($useStatements)) {
            $importStrings = array_map(function (UseUse $useStatement) {
                return $useStatement->name->toString();
            }, $useStatements);

            $alphabetical = $importStrings;
            asort($alphabetical);

            return array_values($importStrings) !== array_values($alphabetical) ? [$useStatements[0]] : [];
        }

        return $useStatementsVisitor->getFoundNodes();
    }
}
