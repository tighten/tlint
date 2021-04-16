<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Config;
use Tighten\TLint\Linters\AlphabeticalImports;
use Tighten\TLint\Presets\LaravelPreset;
use Tighten\TLint\Presets\PresetInterface;
use Tighten\TLint\Presets\TightenPreset;

class ConfigTest extends TestCase
{
    /** @test */
    function laravel_preset_does_not_include_alphabetical_imports()
    {
        $laravelPreset = new LaravelPreset;

        $this->assertNotContains('AlphabeticalImports', $laravelPreset->getLinters());
    }

    /** @test */
    function disabling_a_linter_via_json_config_removes_it_when_filtered()
    {
        $config = new Config(['disabled' => ['AlphabeticalImports']]);

        $this->assertNotContains(AlphabeticalImports::class, $config->getLinters());
    }

    /** @test */
    function default_preset_is_tighten()
    {
        $config = new Config(null);

        $this->assertInstanceOf(TightenPreset::class, $config->getPreset());
    }

    /** @test */
    function a_custom_preset_can_be_provided()
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
