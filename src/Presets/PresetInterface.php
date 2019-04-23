<?php

namespace Tighten\Presets;

interface PresetInterface
{
    public function getLinters() : array;
}
