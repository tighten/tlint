<?php

foreach (
    [
        __DIR__ . '/../../../../vendor/autoload.php',
        __DIR__ . '/../../autoload.php',
        __DIR__ . '/../vendor/autoload.php',
        __DIR__ . '/vendor/autoload.php',
    ] as $file) {
    if (file_exists($file)) {
        require $file;

        break;
    }
}

echo '| Linter | Description |' . "\n";
echo '| --- | --- |' . "\n";

$files = glob("./src/Linters/*.php");
foreach ($files as $file) {
    $linter = new ReflectionClass('\Tighten\TLint\Linters\\' . basename($file, '.php'));
    echo '| `' . $linter->getShortName() . '` | ' . $linter->getConstants()['description'] . ' |' . "\n";
}

echo "\n";
echo "\n";
echo "\n";

echo '| Formatter | Description |' . "\n";
echo '| --- | --- |' . "\n";

$files = glob("./src/Formatters/*.php");
foreach ($files as $file) {
    $linter = new ReflectionClass('\Tighten\TLint\Formatters\\' . basename($file, '.php'));
    echo '| `' . $linter->getShortName() . '` | ' . $linter->getConstants()['description'] . ' |' . "\n";
}
