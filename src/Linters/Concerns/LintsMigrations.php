<?php

namespace Tighten\TLint\Linters\Concerns;

trait LintsMigrations
{
    public static function appliesToPath(string $path): bool
    {
        return strpos($path, 'migrations') !== false;
    }
}
