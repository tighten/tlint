<?php

namespace Tighten\Commands;

use PhpParser\Error;
use PhpParser\NodeAbstract;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Tighten\Lint;
use Tighten\LinterInterface;
use Tighten\Linters\AlphabeticalImports;
use Tighten\Linters\ApplyMiddlewareInRoutes;
use Tighten\Linters\ClassThingsOrder;
use Tighten\Linters\ImportFacades;
use Tighten\Linters\MailableMethodsInBuild;
use Tighten\Linters\ModelMethodOrder;
use Tighten\Linters\NoDocBlocksForMigrationUpDown;
use Tighten\Linters\NoLeadingShashesOnRoutePaths;
use Tighten\Linters\NoParensEmptyInstantiations;
use Tighten\Linters\NoSpaceAfterBladeDirectives;
use Tighten\Linters\PureRestControllers;
use Tighten\Linters\RequestHelperFunctionWherePossible;
use Tighten\Linters\RestControllersMethodOrder;
use Tighten\Linters\SpaceAfterBladeDirectives;
use Tighten\Linters\QualifiedNamesOnlyForClassName;
use Tighten\Linters\RemoveLeadingSlashNamespaces;
use Tighten\Linters\SpaceAfterSoleNotOperator;
use Tighten\Linters\TrailingCommasOnArrays;
use Tighten\Linters\UseAuthHelperOverFacade;
use Tighten\Linters\ViewWithOverArrayParamaters;
use Tighten\TLint;

class LintCommand extends Command
{
    private const NO_LINTS_FOUND_OR_SUCCESS = 0;
    private const LINTS_FOUND_OR_ERROR = 1;

    protected function configure()
    {
        $this
            ->setName('lint')
            ->setDescription('Lints a file.')
            ->setDefinition(new InputDefinition([
                new InputArgument(
                    'file or directory',
                    InputArgument::OPTIONAL,
                    'The file or directory to lint',
                    getcwd()
                ),
                new InputOption(
                    'diff'
                ),
            ]))
            ->setHelp('This command allows you to lint a laravel file.');
    }

    private function lintFile(InputInterface $input, OutputInterface $output, $file)
    {
        if ($this->isBlacklisted($file)) {
            return self::NO_LINTS_FOUND_OR_SUCCESS;
        }

        $linters = $this->getLinters($file);

        $tighten = new TLint;

        $lints = [];

        foreach ($linters as $linterClass => $parseAs) {
            /** @var LinterInterface $linterInstance */
            $linterInstance = new $linterClass(
                file_get_contents($file),
                $parseAs
            );

            try {
                $lints = array_merge($lints, $tighten->lint($linterInstance));
            } catch (Error $e) {
                $linterInstance->setLintDescription($e->getRawMessage());

                return $this->outputLints($output, $file, [
                    new Lint(
                        $linterInstance,
                        new class(['startLine' => $e->getStartLine()]) extends NodeAbstract {
                            public function getSubNodeNames() : array
                            {
                                return [];
                            }
                        }
                    ),
                ]);
            }
        }

        return $this->outputLints($output, $file, $lints);
    }

    private function outputLints(OutputInterface $output, $file, $lints)
    {
        if (! empty($lints)) {
            $output->writeln([
                "Lints for $file: ",
                '============',
            ]);

            foreach ($lints as $lint) {
                /** @var Lint $lint */
                $output->writeln((string)$lint);
            }

            return self::LINTS_FOUND_OR_ERROR;
        }

        return self::NO_LINTS_FOUND_OR_SUCCESS;
    }

    private function isBlacklisted($filepath)
    {
        return strpos($filepath, 'vendor') !== false
            || strpos($filepath, 'app/Http/Middleware/RedirectIfAuthenticated.php') !== false
            || strpos($filepath, 'app/Exceptions/Handler.php') !== false
            || strpos($filepath, 'app/Http/Controllers/Auth') !== false
            || strpos($filepath, 'app/Http/Kernel.php') !== false
            || strpos($filepath, 'public/index.php') !== false
            || strpos($filepath, 'bootstrap/app.php') !== false
            || strpos($filepath, 'storage/framework/views') !== false;
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
        $process = new Process('git diff --name-only');
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
            /** @var \SplFileObject $file */
            $filepath = $file->getRealPath();

            yield $filepath;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileOrDirectory = $input->getArgument('file or directory');
        $finalResponseCode = self::NO_LINTS_FOUND_OR_SUCCESS;

        if ($this->isBlacklisted($fileOrDirectory)) {
            return self::NO_LINTS_FOUND_OR_SUCCESS;
        }

        if (is_file($fileOrDirectory)) {
            $finalResponseCode = $this->lintFile($input, $output, $fileOrDirectory);
        } elseif (is_dir($fileOrDirectory)) {
            try {
                foreach ($this->filesInDir($fileOrDirectory, 'php', $input->getOption('diff')) as $file) {
                    if ($this->lintFile($input, $output, $file) === 1) {
                        $finalResponseCode = self::LINTS_FOUND_OR_ERROR;
                    }
                }
            } catch (ProcessFailedException $e) {
                $output->writeln('Not a git repository (or any of the parent directories)');

                $finalResponseCode = self::LINTS_FOUND_OR_ERROR;
            }
        } else {
            $output->writeln('No file or directory found at ' . $fileOrDirectory);

            return self::LINTS_FOUND_OR_ERROR;
        }

        if ($finalResponseCode === self::NO_LINTS_FOUND_OR_SUCCESS) {
            $output->writeLn('LGTM!');
        }

        return $finalResponseCode;
    }

    private function getRoutesFilesLinters($path)
    {
        if (strpos($path, 'routes') !== false) {
            return [
                ViewWithOverArrayParamaters::class => '.php',
                NoLeadingShashesOnRoutePaths::class => '.php',
            ];
        }

        return [];
    }

    private function getMigrationsLinters($path)
    {
        if (strpos($path, 'migrations') !== false) {
            return [
                NoDocBlocksForMigrationUpDown::class => '.php',
            ];
        }

        return [];
    }

    private function getControllerFilesLinters($path)
    {
        if (strpos($path, 'app/Http/Controllers') !== false) {
            return [
                ViewWithOverArrayParamaters::class => '.php',
                PureRestControllers::class => '.php',
//                RestControllersMethodOrder::class => '.php',
//                RequestHelperFunctionWherePossible::class => '.php',
//                ApplyMiddlewareInRoutes::class => '.php',
            ];
        }

        return [];
    }

    private function getBladeTemplatesLinters($path)
    {
        if (strpos($path, '.blade.php') !== false) {
            return [
//                SpaceAfterBladeDirectives::class => '.php',
//                NoSpaceAfterBladeDirectives::class => '.php',
//                UseAuthHelperOverFacade::class => '.blade.php',
            ];
        }

        return [];
    }

    private function getMailableLinters($path)
    {
        return [
//            MailableMethodsInBuild::class => '.php',
        ];
    }

    private function getLinters($path)
    {
        return array_merge(
            [
                RemoveLeadingSlashNamespaces::class => '.php',
//                QualifiedNamesOnlyForClassName::class => '.php',
                UseAuthHelperOverFacade::class => '.php',
                ImportFacades::class => '.php',
                ModelMethodOrder::class => '.php',
                ClassThingsOrder::class => '.php',
                AlphabeticalImports::class => '.php',
//                TrailingCommasOnArrays::class => '.php',
                NoParensEmptyInstantiations::class => '.php',
                SpaceAfterSoleNotOperator::class => '.php',
            ],
            $this->getRoutesFilesLinters($path),
            $this->getControllerFilesLinters($path),
            $this->getBladeTemplatesLinters($path),
            $this->getMigrationsLinters($path),
            $this->getMailableLinters($path)
        );
    }
}
