<?php

namespace Tighten\Commands;

use PhpParser\Error;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Tighten\Config;
use Tighten\TFormat;

class FormatCommand extends BaseCommand
{
    private const SUCCESS = 0;
    private const ERROR = 1;
    private $thereWasChange = false;

    protected function configure()
    {
        $this
            ->setName('format')
            ->setDescription('Formats a file.')
            ->setDefinition(new InputDefinition([
                new InputArgument(
                    'file or directory',
                    InputArgument::OPTIONAL,
                    'The file or directory to format',
                    getcwd()
                ),
                new InputOption(
                    'diff'
                ),
                new InputOption(
                    'only',
                    null,
                    InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                    'The subset of formatters to use'
                ),
            ]))
            ->setHelp('This command allows you to format a php/laravel file/directory.');
    }

    private function formatFile(InputInterface $input, OutputInterface $output, $file)
    {
        if ($this->isBlacklisted($file)) {
            return self::SUCCESS;
        }

        $formatters = $this->getFormatters($file);

        if (! empty($only = $input->getOption('only'))) {
            $formatters = array_filter($formatters, function($formatter) use ($only) {
                foreach ($only as $filter) {
                    if (false !== strpos($formatter, $filter)) {
                        return true;
                    }
                }
                return false;
            });
        }

        $tighten = new TFormat;

        $initialFileContents = file_get_contents($file);
        $formattedFileContents = $initialFileContents;

        foreach ($formatters as $formatterClass) {
            $formatterInstance = new $formatterClass(
                $formattedFileContents,
                $file
            );

            try {
                $formattedFileContents = $tighten->format($formatterInstance);
            } catch (Error $e) {
                $output->writeln([
                    "Error for {$file}: ",
                    $e->getMessage(),
                ]);

                return self::ERROR;
            }
        }

        if ($initialFileContents === $formattedFileContents) {
            return self::SUCCESS;
        }

        $this->thereWasChange = true;

        file_put_contents($file, $formattedFileContents);

        $output->writeln([
            "Formatted {$file}",
        ]);
        
        return self::SUCCESS;
    }

    private function isBlacklisted($filepath)
    {
        return strpos($filepath, 'vendor') !== false
            || strpos($filepath, 'public/') !== false
            || strpos($filepath, 'bootstrap/') !== false
            || strpos($filepath, 'server.php') !== false
            || strpos($filepath, 'app/Http/Middleware/RedirectIfAuthenticated.php') !== false
            || strpos($filepath, 'app/Exceptions/Handler.php') !== false
            || strpos($filepath, 'app/Http/Controllers/Auth') !== false
            || strpos($filepath, 'app/Http/Kernel.php') !== false
            || strpos($filepath, 'storage/framework/views') !== false
            || $this->isExcluded($filepath);;
    }

    private function filesInDir($directory, $fileExtension, $diff)
    {
        if ($diff) {
            return $this->getDiffedFilesInDir($directory, $fileExtension);
        }

        return $this->getAllFilesInDir($directory, $fileExtension);
    }

    private function getDiffedFilesInDir($directory, $fileExtension)
    {
        $process = new Process([(new ExecutableFinder)->find('git'), 'diff', '--name-only', '--diff-filter=ACMRTUXB']);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        foreach (explode(PHP_EOL, trim($process->getOutput())) as $relativeFilePath) {
            $filepath = getcwd() . '/' . $relativeFilePath;

            yield $filepath;
        }
    }

    private function getAllFilesInDir($directory, $fileExtension)
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

    private function isExcluded(string $filepath): bool
    {
        foreach ($this->config->getExcluded() as $excluded) {
            $excludedPath = $this->resolveFileOrDirectory($excluded);

            if ($excludedPath && strpos($filepath, $this->resolveFileOrDirectory($excluded)) === 0) {
                return true;
            }
        }

        return false;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileOrDirectory = $this->resolveFileOrDirectory($input->getArgument('file or directory'));
        $finalResponseCode = self::SUCCESS;

        if ($this->isBlacklisted($fileOrDirectory)) {
            return self::SUCCESS;
        }

        if (is_file($fileOrDirectory)) {
            $finalResponseCode = $this->formatFile($input, $output, $fileOrDirectory);
        } elseif (is_dir($fileOrDirectory)) {
            try {
                foreach ($this->filesInDir($fileOrDirectory, 'php', $input->getOption('diff')) as $file) {
                    if ($this->formatFile($input, $output, $file) === 1) {
                        $finalResponseCode = self::ERROR;
                    }
                }
            } catch (ProcessFailedException $e) {
                $output->writeln('Not a git repository (or any of the parent directories)');

                $finalResponseCode = self::ERROR;
            }
        } else {
            $output->writeln('No file or directory found at ' . $fileOrDirectory);

            return self::ERROR;
        }

        if (! $this->thereWasChange) {
            $output->writeln([
                "LGTM!",
            ]);
        }

        return $finalResponseCode;
    }

    private function getFormatters($path)
    {
        $configPath = getcwd() . '/tformat.json';
        $config = new Config(json_decode(is_file($configPath) ? file_get_contents($configPath) : null, true) ?? null);
        
        return array_filter($config->getFormatters(), function($className) use ($path) {
            return $className::appliesToPath($path);
        });
    }
}
