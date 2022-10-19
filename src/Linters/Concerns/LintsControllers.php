<?php

namespace Tighten\TLint\Linters\Concerns;

trait LintsControllers
{
    public static function appliesToPath(string $path, array $configPaths): bool
    {
        $DS = DIRECTORY_SEPARATOR;

        $appPaths = isset($configPaths['controllers']) ? $configPaths['controllers'] : "app{$DS}Http{$DS}Controllers";

        if (is_array($appPaths)) {
            return (bool) array_filter(array_map(fn ($appPath) => strpos($path, $appPath) !== false, $appPaths));
        }

        return strpos($path, $appPaths) !== false;
    }
}
