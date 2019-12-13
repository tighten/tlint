<?php

namespace Tighten\Linters\Concerns;

trait LintsMailables
{
    public static function appliesToPath(string $path): bool
    {
        return true;
    }
}
