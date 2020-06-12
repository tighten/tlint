<?php

namespace Tighten\Linters\Concerns;

trait LintsNonConfigFiles
{
    public static function appliesToPath(string $path): bool
    {
        return strpos($path, DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR) === false;
    }
}
