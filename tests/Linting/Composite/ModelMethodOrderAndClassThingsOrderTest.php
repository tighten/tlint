<?php

namespace tests\Linting\Composite;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tighten\Linters\ClassThingsOrder;
use Tighten\Linters\ModelMethodOrder;
use Tighten\TLint;

class ModelMethodOrderAndClassThingsOrderTest extends TestCase
{
    /**
     * @test
     * @dataProvider modelFixtures
     */
    public function a_valid_sorted_model_passes_both_linters(string $file, string $extension): void
    {
        if ($extension === 'pending') {
            $this->markTestSkipped('Model fixture is flagged as pending.');
        }

        $lints = [];
        $lints += (new TLint)->lint(
            new ModelMethodOrder($file),
        );
        $lints += (new TLint)->lint(
            new ClassThingsOrder($file),
        );

        $this->assertEmpty($lints);
    }

    public function modelFixtures(): array
    {
        $models = [];

        $models['Thing'] = [file_get_contents(__DIR__ . '/../../fixtures/Models/Valid/Thing.php'), 'php'];

        $dir = realpath(__DIR__ . '/../../fixtures/Models/RealWorld');
        $directoryIterator = new RecursiveDirectoryIterator($dir);
        $iterator = new RecursiveIteratorIterator($directoryIterator);
        foreach ($iterator as $fileInfo) {
            if (! $fileInfo->isFile()) continue;
            if (! $fileInfo->isReadable()) continue;
            if (! in_array($fileInfo->getExtension(), ['php', 'pending'])) continue;

            $name = trim(str_replace([$dir, '.php'], '', $fileInfo->getPathname()), '/');
            $models[$name] = [file_get_contents($fileInfo->getPathname()), $fileInfo->getExtension()];
        }

        return $models;
    }
}
