<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Config;
use Tighten\TLint\Linters;
use Tighten\TLint\Presets\LaravelPreset;
use Tighten\TLint\Presets\PresetInterface;
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
        $config = new Config(['disabled' => [Linters\ApplyMiddlewareInRoutes::class]]);

        $this->assertNotContains(Linters\ApplyMiddlewareInRoutes::class, $config->getLinters());
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

class ConfigTestPreset implements PresetInterface
{
    public function getLinters(): array
    {
        return [];
    }

    public function getFormatters(): array
    {
        return [];
    }
}
