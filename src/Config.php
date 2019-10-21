<?php

namespace Tighten;

use Tighten\Presets\LaravelPreset;
use Tighten\Presets\TightenPreset;

class Config
{
    protected $preset;
    protected $disabled;
    protected $excluded = [];

    public function __construct($jsonConfigContents) {
        switch ($jsonConfigContents['preset'] ?? null) {
            case 'laravel':
                $this->preset = new LaravelPreset;
                break;
            default:
                $this->preset = new TightenPreset;
                break;
        }

        if (isset($jsonConfigContents['disabled']) && is_array($jsonConfigContents['disabled'])) {
            $this->disabled = $jsonConfigContents['disabled'];
        }

        if (isset($jsonConfigContents['excluded']) && is_array($jsonConfigContents['excluded'])) {
            $this->excluded = $jsonConfigContents['excluded'];
        }
    }

    public function filterLinters(array $linters)
    {
        if ($this->preset) {
            $linters = array_intersect_key($linters, array_flip(array_map(function ($className) {
                return 'Tighten\\Linters\\' . $className;
            }, $this->preset->getLinters())));
        }

        if ($this->disabled) {
            $linters = array_diff_key($linters, array_flip(array_map(function ($className) {
                return 'Tighten\\Linters\\' . $className;
            }, $this->disabled)));
        }

        return $linters;
    }

    public function getPreset(): TightenPreset
    {
        return $this->preset;
    }

    public function getExcluded(): array
    {
        return $this->excluded;
    }
}
