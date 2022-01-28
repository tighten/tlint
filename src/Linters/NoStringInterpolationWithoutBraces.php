<?php

namespace Tighten\TLint\Linters;

use Closure;
use PhpParser\Node;
use Tighten\TLint\BaseLinter;

class NoStringInterpolationWithoutBraces extends BaseLinter
{
    public const DESCRIPTION = 'Never use string interpolation without braces';

    protected function visitor(): Closure
    {
        return function (Node $node) {
            if ($node instanceof Node\Scalar\Encapsed) {
                foreach ($node->parts as $part) {
                    if ($part instanceof Node\Expr\Variable) {
                        $line = $this->getCodeLinesFromNode($node);
                        $name = $part->name;

                        return ! (strpos($line, "{\${$name}}") !== false);
                    } elseif ($part instanceof Node\Expr\PropertyFetch) {
                        $line = $this->getCodeLinesFromNode($node);
                        $propertyFetchString = $this->constructPropertyFetchString($part);

                        return ! (strpos($line, "{\${$propertyFetchString}") !== false);
                    }
                }
            }

            return false;
        };
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
