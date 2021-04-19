<?php

namespace Tighten\TLint\Linters\Concerns;

trait LintsRoutesFiles
{
    public static function appliesToPath(string $path): bool
    {
        return strpos($path, 'routes') !== false;
    }
}
