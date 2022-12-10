<?php

namespace Tighten\TLint;

use InvalidArgumentException;
use Tighten\TLint\Presets\PresetInterface;
use Tighten\TLint\Presets\TightenPreset;

class Config
{
    public $paths = [];
    public $excluded = [];

    protected $preset;
    protected $linters;
    protected $formatters;

    public function __construct($jsonConfigContents)
    {
        $this->setPreset($jsonConfigContents['preset'] ?? TightenPreset::class);

        if (isset($jsonConfigContents['excluded']) && is_array($jsonConfigContents['excluded'])) {
            $this->excluded = $jsonConfigContents['excluded'];
        }

        if (isset($jsonConfigContents['paths']) && is_array($jsonConfigContents['paths'])) {
            $this->paths = $jsonConfigContents['paths'];
        }

        $this->linters = $this->buildLinterList($jsonConfigContents ?? []);
        $this->formatters = $this->buildFormatterList($jsonConfigContents ?? []);
    }

    public function setPreset($preset): self
    {
        if (! class_exists($preset)) {
            $preset = 'Tighten\\TLint\\Presets\\' . ucfirst($preset) . 'Preset';
        }

        if (! is_a($preset, PresetInterface::class, true)) {
            throw new InvalidArgumentException("The preset '{$preset}' does not exist or does not implement the PresetInterface.");
        }

        $this->preset = new $preset();

        return $this;
    }

    public function getPreset(): PresetInterface
    {
        return $this->preset;
    }

    public function getLinters(): array
    {
        return $this->linters;
    }

    public function getFormatters(): array
    {
        return $this->formatters;
    }

    private function normalizeClassList(string $namespace, array $classNames): array
    {
        return array_map(function ($className) use ($namespace) {
            return $this->normalizeNamespace($namespace, $className);
        }, $classNames);
    }

    private function normalizeNamespace(string $namespace, string $className): string
    {
        return class_exists($className)
            ? $className
            : $namespace . $className;
    }

    private function buildLinterList(array $config): array
    {
        $linters = $this->normalizeClassList('Tighten\\TLint\\Linters\\', $this->preset->getLinters());

        $disabled = isset($config['disabled']) && is_array($config['disabled'])
            ? $this->normalizeClassList('Tighten\\TLint\\Linters\\', $config['disabled'])
            : [];

        return array_diff($linters, $disabled);
    }

    private function buildFormatterList(array $config): array
    {
        $formatters = $this->normalizeClassList('Tighten\\TLint\\Formatters\\', $this->preset->getFormatters());

        $disabled = isset($config['disabled']) && is_array($config['disabled'])
            ? $this->normalizeClassList('Tighten\\TLint\\Formatters\\', $config['disabled'])
            : [];

        return array_diff($formatters, $disabled);
    }
}
