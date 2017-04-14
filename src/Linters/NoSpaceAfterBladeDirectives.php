<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeAbstract;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\AbstractLinter;

class NoSpaceAfterBladeDirectives extends AbstractLinter
{
    private const NO_SPACE_AFTER = [
        'endif',
        'else',
        'section',
        'show',
        'yield',
        'extends',
        'parent',
        'verbatim',
        'empty',
        'continue',
        'break',
        'php',
        'include',
        'includeIf',
        'each',
        'push',
        'stack',
    ];

    public function lintDescription()
    {
        return 'No space between blade template directive names and the opening paren: `@section (` -> `@section(`';
    }

    /**
     * @param Parser $parser
     * @return Node[]
     */
    public function lint(Parser $parser)
    {
        $foundNodes = [];

        foreach ($this->getCodeLines() as $line => $codeLine) {
            $matches = [];

            // https://github.com/illuminate/view/blob/master/Compilers/BladeCompiler.php#L271
            preg_match(
                '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x',
                $codeLine,
                $matches
            );

            if (in_array($matches[1] ?? null, self::NO_SPACE_AFTER) && ($matches[2] ?? null) !== '') {
                $foundNodes[] = new class(['startLine' => $line + 1]) extends NodeAbstract {
                    public function getSubNodeNames()
                    {
                        return [];
                    }
                };
            }
        }

        return $foundNodes;
    }
}
