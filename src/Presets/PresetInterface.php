<?php

namespace Tighten\TLint\Presets;

interface PresetInterface
{
    public function getLinters(): array;
    public function getFormatters(): array;
}
