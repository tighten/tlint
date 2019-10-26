<?php

namespace Tighten\Commands;

use Symfony\Component\Console\Command\Command;
use Tighten\Config;

abstract class BaseCommand extends Command
{
    protected $cwd;
    protected $config;

    public function __construct(string $cwd = null)
    {
        $this->cwd = $cwd;
        $configPath = $this->resolveFileOrDirectory('tlint.json');
        $this->config = new Config(
            json_decode(
                is_file($configPath)
                    ? file_get_contents($configPath)
                    : null,
                true
            ) ?? null
        );

        parent::__construct();
    }

    protected function resolveFileOrDirectory(string $fileOrDirectory)
    {
        $realpath = realpath($fileOrDirectory);

        if ($this->cwd || ! $realpath) {
            return $this->cwd
                ? realpath($this->cwd . '/' . ltrim($fileOrDirectory, '/'))
                : realpath(getcwd() . '/' . ltrim($fileOrDirectory, '/'));
        }

        return $realpath;
    }
}
