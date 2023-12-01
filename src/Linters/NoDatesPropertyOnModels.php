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

    protected bool $model = false;

    protected function visitor(): Closure
    {
        return function (Node $node) {
            if ($this->extendsAny($node, ['Model', 'Pivot', 'Authenticatable'])) {
                $this->model = true;
            }

            return $this->model && $node instanceof Property && (string) $node->props[0]->name === 'dates';
        };
    }
}
