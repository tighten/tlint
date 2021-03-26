<?php

namespace Tighten\Formatters;

use Closure;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Tighten\BaseFormatter;
use Tighten\Concerns\IdentifiesExtends;

class NoDatesPropertyOnModels extends BaseFormatter
{
    use IdentifiesExtends;

    public const description = 'Use `$casts` instead of `$dates` on Eloquent models.';

    public function format(Parser $parser, Lexer $lexer)
    {
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new CloningVisitor);

        $originalStatements = $parser->parse($this->code);
        $statements = $traverser->traverse($originalStatements);

        $nodeFinder = new NodeFinder;
        $dates = $nodeFinder->findFirst($statements, $this->nodeFinderForModelProperty('dates'));
        $casts = $nodeFinder->findFirst($statements, $this->nodeFinderForModelProperty('casts'));

        if ($dates) {
            $newCasts = $this->addDatesToCasts($dates, $casts);

            $statements = array_map(function (Node $node) use ($casts, $newCasts) {
                if ($this->extendsAny($node, ['Model', 'Pivot', 'Authenticatable'])) {
                    // Replace the dates with the new casts and remove any existing casts (necessary so
                    // that we know where to put the new casts if there weren't any already)
                    $node->stmts = array_values(array_filter(array_map(function ($stmt) use ($newCasts) {
                        if ($stmt instanceof Property && (string) $stmt->props[0]->name === 'dates') {
                            return $newCasts;
                        } elseif ($stmt instanceof Property && (string) $stmt->props[0]->name === 'casts') {
                            return;
                        }

                        return $stmt;
                    }, $node->stmts)));
                }

                return $node;
            }, $statements);
        }

        return $this->printer()->printFormatPreserving($statements, $originalStatements, $lexer->getTokens());
    }

    private function nodeFinderForModelProperty(string $attribute): Closure
    {
        return function (Node $node) use ($attribute) {
            static $model = false;

            if ($this->extendsAny($node, ['Model', 'Pivot', 'Authenticatable'])) {
                $model = true;
            }

            return $model && $node instanceof Property && (string) $node->props[0]->name === $attribute;
        };
    }

    private function addDatesToCasts(Property $dates, Property $casts = null): Property
    {
        // Get the names of all the existing date attributes
        $dateAttributes = array_map(function ($item) {
            return $item->value->value;
        }, $dates->props[0]->default->items);

        // Create new $casts entries for each date attribute
        $newCasts = array_map(function ($attribute) {
            return new ArrayItem(new String_('datetime'), new String_($attribute));
        }, $dateAttributes);

        // If there are already casts...
        if ($casts && ! empty($casts->props[0]->default)) {
            // ...get their names...
            $castAttributes = array_map(function ($item) {
                return $item->key->value;
            }, $casts->props[0]->default->items);

            // ...and discard any of the new datetime casts that conflict with existing casts
            $newCasts = array_values(array_filter($newCasts, function ($item) use ($castAttributes) {
                return ! in_array($item->key->value, $castAttributes);
            }));

            $newCasts = array_merge($casts->props[0]->default->items, $newCasts);
        }

        // Sort casts alphabetically
        sort($newCasts);

        // We have to return a *new* Property node here (even if there was
        // already a casts property) so the printer formats it correctly
        return new Property(Class_::MODIFIER_PROTECTED, [
            new PropertyProperty('casts', new Array_($newCasts, ['kind' => Array_::KIND_SHORT]))
        ]);
    }

    private function printer(): Standard
    {
        return new class extends Standard {
            // Force all arrays to be printed in multiline style
            protected function pMaybeMultiline(array $nodes, bool $trailingComma = true)
            {
                return $this->pCommaSeparatedMultiline($nodes, $trailingComma) . $this->nl;
            }
        };
    }
}
