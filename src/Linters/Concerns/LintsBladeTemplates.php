<?php

namespace Tighten\TLint\Linters\Concerns;

trait LintsBladeTemplates
{
    public static function appliesToPath(string $path, array $configPaths): bool
    {
        return strpos($path, '.blade.php') !== false;
    }
}
