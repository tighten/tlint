<?php

namespace Tests;

use Tighten\TLint\Presets\PresetInterface;

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
