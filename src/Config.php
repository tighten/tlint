<?php

namespace Tighten;

use InvalidArgumentException;
use Tighten\Presets\PresetInterface;
use Tighten\Presets\TightenPreset;

class Config
{
    protected $preset;
    protected $linters;
    protected $excluded = [];

    public function __construct($jsonConfigContents) {
    	$this->setPreset($jsonConfigContents['preset'] ?? TightenPreset::class);

        if (isset($jsonConfigContents['excluded']) && is_array($jsonConfigContents['excluded'])) {
            $this->excluded = $jsonConfigContents['excluded'];
        }
        
        $this->linters = $this->buildLinterList($jsonConfigContents ?? []);
    }

    public function filterFormatters(array $formatters)
    {
        if ($this->preset) {
            $formatters = array_intersect_key($formatters, array_flip(array_map(function ($className) {
	            return $this->normalizeNamespace('Tighten\\Formatters\\', $className);
            }, $this->preset->getFormatters())));
        }

        if ($this->disabled) {
            $formatters = array_diff_key($formatters, array_flip(array_map(function ($className) {
	            return $this->normalizeNamespace('Tighten\\Formatters\\', $className);
            }, $this->disabled)));
        }

        return $formatters;
    }
    
    public function setPreset($preset): self
    {
    	if (!class_exists($preset)) {
    		$preset = 'Tighten\\Presets\\' . ucfirst($preset) . 'Preset';
	    }
    	
    	if (!is_a($preset, PresetInterface::class, true)) {
    		throw new InvalidArgumentException("The preset '{$preset}' does not exist or does not implement the PresetInterface.");
	    }
	
	    $this->preset = new $preset;
    	
    	return $this;
    }

    public function getPreset(): PresetInterface
    {
        return $this->preset;
    }

    public function getExcluded(): array
    {
        return $this->excluded;
    }
    
    public function getLinters(): array
    {
        return $this->linters;
    }
    
    private function normalizeClassList(string $namespace, array $classNames): array
    {
        return array_map(function($className) use ($namespace) {
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
        $linters = $this->normalizeClassList('Tighten\\Linters\\', $this->preset->getLinters());

        if (isset($config['custom']) && is_array($config['custom'])) {
            $linters = array_merge(
                $linters, 
                $this->normalizeClassList('Tighten\\Linters\\', $config['custom'])
            );
        }

        $disabled = isset($config['disabled']) && is_array($config['disabled'])
            ? $this->normalizeClassList('Tighten\\Linters\\', $config['disabled'])
            : [];
        
        return array_diff($linters, $disabled);
    }
}
