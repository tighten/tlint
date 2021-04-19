<?php

namespace Tighten\TLint\Linters\Concerns;

trait LintsMailables
{
    public static function appliesToPath(string $path): bool
    {
        return true;
    }
}
