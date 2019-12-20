<?php

namespace Tighten\Linters\Concerns;

trait LintsTests
{
    public static function appliesToPath(string $path): bool
    {
        return strpos($path, 'tests') !== false;
    }
}
