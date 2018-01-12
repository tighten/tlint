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
                '/\{\{\s*(.+?)\s*\}\}/',
                $codeLine,
                $matches
            );

            if (isset($matches[1]) && (! str_contains($codeLine, '{{ ') && ! str_contains($codeLine, ' }}'))) {
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
