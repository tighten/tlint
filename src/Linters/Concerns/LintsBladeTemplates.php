<?php

namespace Tighten\TLint\Linters\Concerns;

trait LintsBladeTemplates
{
    public const SEARCH_NORMAL = '/(?<!@)\{\{\s*(.+?)\s*\}\}/';

    public const SEARCH_RAW = '/(?<!@)\{\!\!\s*(.+?)\s*\!\!\}/';

    // https://github.com/illuminate/view/blob/master/Compilers/BladeCompiler.php#L525
    public const DIRECTIVE_SEARCH = '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( [\S\s]*? ) \))?/x';

    public static function appliesToPath(string $path, array $configPaths): bool
    {
        return strpos($path, '.blade.php') !== false;
    }

    public static function getBladeDirective(string $codeLine): string
    {
        preg_match(self::DIRECTIVE_SEARCH, $codeLine, $matches);

        return $matches[1] ?? '';
    }
}
