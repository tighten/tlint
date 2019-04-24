<?php

use PHPUnit\Framework\TestCase;
use Tighten\Config;
use Tighten\Linters\AlphabeticalImports;
use Tighten\Presets\LaravelPreset;
use Tighten\Presets\TightenPreset;

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

        $this->assertArrayNotHasKey(AlphabeticalImports::class, $config->filterLinters([
            AlphabeticalImports::class => '.php',
        ]));
    }

    /** @test */
    function default_preset_is_tighten()
    {
        $config = new Config(null);

        $this->assertInstanceOf(TightenPreset::class, $config->getPreset());
    }
}
