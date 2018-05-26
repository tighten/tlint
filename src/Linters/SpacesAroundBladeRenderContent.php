<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeAbstract;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class SpacesAroundBladeRenderContent extends BaseLinter
{
    protected $description = 'Spaces around blade rendered content:'
        . '`{{1 + 1}}` -> `{{ 1 + 1 }}`';

    public function lint(Parser $parser)
    {
        $foundNodes = [];

        /**
         * Normal render
         */
        foreach ($this->getCodeLines() as $line => $codeLine) {
            $matches = [];

            preg_match(
                '/\{\{(.+?)\}\}/',
                $codeLine,
                $matches
            );

            if (isset($matches[1]) && isset($matches[0])
                /** Is not a blade comment */
                && (substr($matches[1], 0, 2) !== '--')
                && (
                    /** Does not only have a *single* space before the start of the content */
                    $matches[1][0] !== ' '
                    || $matches[1][1] === ' '
                    /** Does not only have a *single* space at the end of the content */
                    || $matches[1][-1] !== ' '
                    || $matches[1][-2] === ' '
                )
            ) {
                $foundNodes[] = new class(['startLine' => $line + 1]) extends NodeAbstract {
                    public function getSubNodeNames() : array
                    {
                        return [];
                    }

                    public function getType(): string
                    {
                        return '';
                    }
                };
            }
        }

        /**
         * Raw render
         */
        foreach ($this->getCodeLines() as $line => $codeLine) {
            $matches = [];

            preg_match(
                '/\{\!!s*(.+?)\s*!!\}/',
                $codeLine,
                $matches
            );

            if (isset($matches[1]) && (! str_contains($codeLine, '{!! ') && ! str_contains($codeLine, ' !!}'))) {
                $foundNodes[] = new class(['startLine' => $line + 1]) extends NodeAbstract {
                    public function getSubNodeNames() : array
                    {
                        return [];
                    }

                    public function getType(): string
                    {
                        return '';
                    }
                };
            }
        }

        return $foundNodes;
    }
}
