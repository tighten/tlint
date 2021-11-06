<?php

require __DIR__ . '/../vendor/autoload.php';

$readme = __DIR__ . '/../readme.md';

$linters = collect(glob(__DIR__ . '/../src/Linters/*.php'))->reduce(function ($carry, $file) {
    $linter = new ReflectionClass('\Tighten\TLint\Linters\\' . basename($file, '.php'));

    return <<<MD
    {$carry}
    | `{$linter->getShortName()}` | {$linter->getConstants()['DESCRIPTION']} |
    MD;
}, <<<MD
| Linter | Description |
| --- | --- |
MD);

$formatters = collect(glob(__DIR__ . '/../src/Formatters/*.php'))->reduce(function ($carry, $file) {
    $formatter = new ReflectionClass('\Tighten\TLint\Formatters\\' . basename($file, '.php'));

    return <<<MD
    {$carry}
    | `{$formatter->getShortName()}` | {$formatter->getConstants()['DESCRIPTION']} |
    MD;
}, <<<MD
| Formatter | Description |
| --- | --- |
MD);

file_put_contents($readme, preg_replace([
    '/(?<=<!-- linters -->\n)(.*)(?=\n<!-- \/linters -->)/',
    '/(?<=<!-- formatters -->\n)(.*)(?=\n<!-- \/formatters -->)/',
], [$linters, $formatters], file_get_contents($readme)));
