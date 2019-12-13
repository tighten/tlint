<?php

namespace Tighten\Linters\Concerns;

trait LintsNonConfigFiles
{
    public static function appliesToPath(string $path): bool
    {
        return strpos($path, '/config/') === false;
    }
}
