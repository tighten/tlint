<?php

namespace Tighten\TLint\Linters\Concerns;

trait LintsTests
{
    public static function appliesToPath(string $path, array $configPaths): bool
    {
        return strpos($path, 'tests') !== false;
    }
}
