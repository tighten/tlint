<?php

namespace Tighten\Linters\Concerns;

trait LintsBladeTemplates
{
    public static function appliesToPath(string $path): bool
    {
        return strpos($path, '.blade.php') !== false;
    }
}
