<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tighten\TLint\Commands\LintCommand;

class ExcludedPathTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = realpath(sys_get_temp_dir());
    }

    /** @test */
    public function it_excludes_root_vendor_directory()
    {
        $DS = DIRECTORY_SEPARATOR;

        $this->assertTrue($this->isOnExcludeList("{$this->root}{$DS}vendor{$DS}league{$DS}commonmark{$DS}src{$DS}File.php"));
    }

    /** @test */
    public function it_allows_vendor_views_directory()
    {
        $DS = DIRECTORY_SEPARATOR;

        $this->assertFalse($this->isOnExcludeList("{$this->root}{$DS}resources{$DS}views{$DS}vendor{$DS}mail{$DS}html{$DS}header.blade.php"));
    }

    /** @test */
    public function it_does_not_exclude_files_containing_vendor_in_name()
    {
        $DS = DIRECTORY_SEPARATOR;

        $this->assertFalse($this->isOnExcludeList("{$this->root}{$DS}app{$DS}Http{$DS}Controllers{$DS}VendorController.php"));
    }

    /** @test */
    public function it_excludes_root_node_modules_directory()
    {
        $DS = DIRECTORY_SEPARATOR;

        $this->assertTrue($this->isOnExcludeList("{$this->root}{$DS}node_modules{$DS}lodash{$DS}index.js"));
    }

    /** @test */
    public function it_does_not_exclude_files_containing_node_modules_in_name()
    {
        $DS = DIRECTORY_SEPARATOR;

        $this->assertFalse($this->isOnExcludeList("{$this->root}{$DS}app{$DS}Http{$DS}Controllers{$DS}NodeModulesController.php"));
    }

    /** @test */
    public function it_excludes_root_bootstrap_directory()
    {
        $DS = DIRECTORY_SEPARATOR;

        $this->assertTrue($this->isOnExcludeList("{$this->root}{$DS}bootstrap{$DS}app.php"));
    }

    /** @test */
    public function it_does_not_exclude_files_containing_bootstrap_in_name()
    {
        $DS = DIRECTORY_SEPARATOR;

        $this->assertFalse($this->isOnExcludeList("{$this->root}{$DS}app{$DS}Http{$DS}Controllers{$DS}BootstrapController.php"));
        $this->assertFalse($this->isOnExcludeList("{$this->root}{$DS}resources{$DS}views{$DS}bootstrap-layout.blade.php"));
    }

    /** @test */
    public function it_excludes_storage_framework_views_directory()
    {
        $DS = DIRECTORY_SEPARATOR;

        $this->assertTrue($this->isOnExcludeList("{$this->root}{$DS}storage{$DS}framework{$DS}views{$DS}cached.php"));
    }

    private function isOnExcludeList(string $filepath): bool
    {
        $command = new LintCommand(sys_get_temp_dir());

        $method = new ReflectionMethod($command, 'isOnExcludeList');

        return $method->invoke($command, $filepath);
    }
}
