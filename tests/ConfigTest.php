<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Config;
use Tighten\TLint\Linters\ApplyMiddlewareInRoutes;
use Tighten\TLint\Presets\LaravelPreset;
use Tighten\TLint\Presets\TightenPreset;

class ConfigTest extends TestCase
{
    /** @test */
    public function tighten_preset_can_get_linters()
    {
        $this->assertNotEmpty((new TightenPreset)->getLinters());
    }

    /** @test */
    public function tighten_preset_can_get_formatters()
    {
        $this->assertNotEmpty((new TightenPreset)->getFormatters());
    }

    /** @test */
    public function laravel_preset_can_get_linters()
    {
        $this->assertNotEmpty((new LaravelPreset)->getLinters());
    }

    /** @test */
    public function laravel_preset_can_get_formatters()
    {
        $this->assertIsArray((new LaravelPreset)->getFormatters());
    }

    /** @test */
    public function disabling_a_linter_via_json_config_removes_it_when_filtered()
    {
        $config = new Config(['disabled' => [ApplyMiddlewareInRoutes::class]]);

        $this->assertNotContains(ApplyMiddlewareInRoutes::class, $config->getLinters());
    }

    /** @test */
    public function custom_paths_used_via_json_config()
    {
        $config = new Config(['paths' => ['controllers' => 'app/Domain/Http/Controllers']]);

        $this->assertTrue(ApplyMiddlewareInRoutes::appliesToPath('app/Domain/Http/Controllers/UserController.php', $config->paths));
        $this->assertFalse(ApplyMiddlewareInRoutes::appliesToPath('app/Http/Controllers/UserController.php', $config->paths));
    }

    /** @test */
    public function custom_paths_used_via_json_config_can_be_array()
    {
        $config = new Config(['paths' => ['controllers' => ['app/Domain1/Http/Controllers', 'app/Domain2/Http/Controllers']]]);

        $this->assertTrue(ApplyMiddlewareInRoutes::appliesToPath('app/Domain1/Http/Controllers/UserController.php', $config->paths));
        $this->assertTrue(ApplyMiddlewareInRoutes::appliesToPath('app/Domain2/Http/Controllers/UserController.php', $config->paths));
        $this->assertFalse(ApplyMiddlewareInRoutes::appliesToPath('app/Domain3/Http/Controllers/UserController.php', $config->paths));
    }

    /** @test */
    public function custom_paths_used_via_json_config_can_be_empty()
    {
        $DS = DIRECTORY_SEPARATOR;

        $config = new Config(['paths' => []]);
        $this->assertTrue(ApplyMiddlewareInRoutes::appliesToPath("app{$DS}Http{$DS}Controllers{$DS}UserController.php", $config->paths));

        $config = new Config(['paths' => null]);
        $this->assertTrue(ApplyMiddlewareInRoutes::appliesToPath("app{$DS}Http{$DS}Controllers{$DS}UserController.php", $config->paths));

        $config = new Config([]);
        $this->assertTrue(ApplyMiddlewareInRoutes::appliesToPath("app{$DS}Http{$DS}Controllers{$DS}UserController.php", $config->paths));
    }

    /** @test */
    public function default_preset_is_tighten()
    {
        $config = new Config(null);

        $this->assertInstanceOf(TightenPreset::class, $config->getPreset());
    }

    /** @test */
    public function a_custom_preset_can_be_provided()
    {
        $config = new Config(['preset' => ConfigTestPreset::class]);

        $this->assertInstanceOf(ConfigTestPreset::class, $config->getPreset());
    }
}
