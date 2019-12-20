<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class TrailingCommasOnArrays extends BaseLinter
{
    public const description = 'Multiline arrays should have trailing commas';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $missingTrailingCommas = [];

        $visitor = new FindingVisitor(function (Node $node) use (&$missingTrailingCommas) {
            if ($node instanceof Node\Expr\Array_
                && ! empty($node->items)
                && ($node->getAttributes()['endLine'] - $node->getAttributes()['startLine'] > 0)) {
                $numberOfItems = count($node->items);

                $lastNode = $node->items[$numberOfItems - 1];

                $lastLine = $this->getCodeLine($lastNode->getAttributes()['endLine']);

                $reverseIndexOfLastComma = strpos(strrev($lastLine), ',');

                if ($reverseIndexOfLastComma === false) {
                    $missingTrailingCommas[] = $lastNode;
                } elseif ($reverseIndexOfLastComma === 0) {
                    return false;
                }

                $indexOfLastComma = (strlen($lastLine) - 1) - $reverseIndexOfLastComma;
                $lastLineAfterLastComma = substr($lastLine, $indexOfLastComma);

                /** Handle no comment */
                if (strpos($lastLineAfterLastComma, '//') !== false) {
                    /** Handle inline comment */
                    if (rtrim(explode('//', $lastLine)[0])[-1] !== ',') {
                        $missingTrailingCommas[] = $lastNode;
                    }
                } elseif (strpos($lastLineAfterLastComma, '/**') !== false) {
                    /** Handle block style inline comment */
                    if (rtrim(explode('/**', $lastLine)[0])[-1] !== ',') {
                        $missingTrailingCommas[] = $lastNode;
                    }
                }
            }

            return false;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        if (! empty($missingTrailingCommas)) {
            return array_map(function (Node $node) {
                /** Set the reported line to the end of the array */
                $node->setAttribute('startLine', $node->getAttributes()['endLine']);

                return $node;
            }, $missingTrailingCommas);
        }

        return $visitor->getFoundNodes();
    }
}
