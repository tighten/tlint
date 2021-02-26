<?php

namespace Tighten\Commands;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Tighten\Config;
use Tighten\Utils\ParsesGitOutput;

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

    protected function getDiffedFilesInDir()
    {
        $process = new Process([(new ExecutableFinder)->find('git'), 'diff', '--name-only', '--diff-filter=ACMRTUXB']);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return ParsesGitOutput::parseFilesFromGitDiffOutput($process->getOutput());
    }

    protected function getAllFilesInDir($directory, $fileExtension)
    {
        $directory = realpath($directory);
        $it = new RecursiveDirectoryIterator($directory);
        $it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::LEAVES_ONLY);
        $it = new RegexIterator($it, '(\.' . preg_quote($fileExtension) . '$)');

        foreach ($it as $file) {
            $filepath = $file->getRealPath();

            yield $filepath;
        }
    }

    protected function filesInDir($directory, $fileExtension, $diff)
    {
        if ($diff) {
            return $this->getDiffedFilesInDir();
        }

        return $this->getAllFilesInDir($directory, $fileExtension);
    }

    protected function isExcluded(string $filepath): bool
    {
        foreach ($this->config->getExcluded() as $excluded) {
            $excludedPath = $this->resolveFileOrDirectory($excluded);

            if ($excludedPath && strpos($filepath, $this->resolveFileOrDirectory($excluded)) === 0) {
                return true;
            }
        }

        return false;
    }

    protected function isBlacklisted($filepath)
    {
        $DS = DIRECTORY_SEPARATOR;
        return strpos($filepath, "vendor") !== false
            || strpos($filepath, "node_modules") !== false
            || strpos($filepath, "public{$DS}") !== false
            || strpos($filepath, "bootstrap{$DS}") !== false
            || strpos($filepath, "server.php") !== false
            || strpos($filepath, "app{$DS}Http{$DS}Middleware{$DS}RedirectIfAuthenticated.php") !== false
            || strpos($filepath, "app{$DS}Exceptions{$DS}Handler.php") !== false
            || strpos($filepath, "app{$DS}Http{$DS}Controllers{$DS}Auth") !== false
            || strpos($filepath, "app{$DS}Http{$DS}Kernel.php") !== false
            || strpos($filepath, "storage{$DS}framework{$DS}views") !== false
            || $this->isExcluded($filepath);
    }
}
