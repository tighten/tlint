<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;

class NoStringInterpolationWithoutBraces extends BaseLinter
{
    protected $description = 'Never use string interpolation without braces';

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) use ($parser) {
            if ($node instanceof Node\Scalar\Encapsed) {
                foreach ($node->parts as $part) {
                    if ($part instanceof Node\Expr\Variable) {
                        $line = $this->getCodeLine($node->getStartLine());
                        $name = $part->name;

                        return ! (strpos($line, "{\${$name}}") !== false);
                    } elseif ($part instanceof Node\Expr\PropertyFetch) {
                        $line = $this->getCodeLine($node->getStartLine());
                        $propertyFetchString = $this->constructPropertyFetchString($part);

                        return ! (strpos($line, "{\${$propertyFetchString}") !== false);
                    }
                }
            }

            return false;
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }

    private function constructPropertyFetchString($next, $string = '')
    {
        if (property_exists($next, 'var')) {
            return $this->constructPropertyFetchString(
                $next->var,
                $next->name->name . ($string ? ('->' . $string) : $string)
            );
        }

        return $next->name . '->' . $string;
    }
}
