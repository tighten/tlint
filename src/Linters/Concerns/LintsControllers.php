<?php

namespace Tighten\Linters\Concerns;

trait LintsControllers
{
    public static function appliesToPath(string $path): bool
    {
        return strpos($path, 'app/Http/Controllers') !== false;
    }
}
