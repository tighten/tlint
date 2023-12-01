<?php

namespace Tighten\TLint\Linters;

use Closure;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\Concerns\IdentifiesExtends;

class NoDatesPropertyOnModels extends BaseLinter
{
    use IdentifiesExtends;

    public const DESCRIPTION = 'The `$dates` property was deprecated in Laravel 8. Use `$casts` instead.';

    protected function visitor(): Closure
    {
        static $model = false;

        return function (Node $node) use (&$model) {
            if ($this->extendsAny($node, ['Model', 'Pivot', 'Authenticatable'])) {
                $model = true;
            }

            return $model && $node instanceof Property && (string) $node->props[0]->name === 'dates';
        };
    }
}
