<?php

namespace Tighten\Illuminate;

use Illuminate\View\Compilers\BladeCompiler as IlluminateBladeCompiler;
use InvalidArgumentException;

class BladeCompiler extends IlluminateBladeCompiler
{
    protected $compilesComponentTags = false;

    public function __construct($files, $cachePath)
    {
        if (! $cachePath) {
            throw new InvalidArgumentException('Please provide a valid cache path.');
        }

        $this->files = $files;
        $this->cachePath = $cachePath;
    }
}
