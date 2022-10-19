<?php

namespace Tighten\TLint\Linters\Concerns;

trait LintsMigrations
{
    public static function appliesToPath(string $path, array $configPaths): bool
    {
        return strpos($path, 'migrations') !== false;
    }
}
