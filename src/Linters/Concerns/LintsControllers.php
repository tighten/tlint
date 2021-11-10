<?php

namespace Tighten\TLint\Linters\Concerns;

trait LintsControllers
{
    public static function appliesToPath(string $path): bool
    {
        $DS = DIRECTORY_SEPARATOR;

        return strpos($path, "app{$DS}Http{$DS}Controllers") !== false;
    }
}
