<?php

namespace Tighten\TLint\Linters\Concerns;

trait LintsBladeTemplates
{
    public static string $searchNormal = '/(?<!@)\{\{\s*(.+?)\s*\}\}/';

    public static string $searchRaw = '/(?<!@)\{\!\!\s*(.+?)\s*\!\!\}/';

    // https://github.com/illuminate/view/blob/master/Compilers/BladeCompiler.php#L525
    public static string $directiveSearch = '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( [\S\s]*? ) \))?/x';

    public static function appliesToPath(string $path, array $configPaths): bool
    {
        return strpos($path, '.blade.php') !== false;
    }

    public static function getBladeDirective(string $codeLine): string
    {
        preg_match(self::$directiveSearch, $codeLine, $matches);

        return $matches[1] ?? '';
    }
}
