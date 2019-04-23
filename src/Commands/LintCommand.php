<?php

namespace Tighten\Commands;

use PhpParser\Error;
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
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Tighten\CustomNode;
use Tighten\Lint;
use Tighten\Linters\AlphabeticalImports;
use Tighten\Linters\ApplyMiddlewareInRoutes;
use Tighten\Linters\ClassThingsOrder;
use Tighten\Linters\ImportFacades;
use Tighten\Linters\MailableMethodsInBuild;
use Tighten\Linters\ModelMethodOrder;
use Tighten\Linters\NewLineAtEndOfFile;
use Tighten\Linters\NoCompact;
use Tighten\Linters\NoDd;
use Tighten\Linters\NoDocBlocksForMigrationUpDown;
use Tighten\Linters\NoInlineVarDocs;
use Tighten\Linters\NoLeadingSlashesOnRoutePaths;
use Tighten\Linters\NoMethodVisibilityInTests;
use Tighten\Linters\NoParensEmptyInstantiations;
use Tighten\Linters\NoSpaceAfterBladeDirectives;
use Tighten\Linters\NoStringInterpolationWithoutBraces;
use Tighten\Linters\NoUnusedImports;
use Tighten\Linters\OneLineBetweenClassVisibilityChanges;
use Tighten\Linters\PureRestControllers;
use Tighten\Linters\RequestHelperFunctionWherePossible;
use Tighten\Linters\RequestValidation;
use Tighten\Linters\RestControllersMethodOrder;
use Tighten\Linters\SpaceAfterBladeDirectives;
use Tighten\Linters\QualifiedNamesOnlyForClassName;
use Tighten\Linters\RemoveLeadingSlashNamespaces;
use Tighten\Linters\SpaceAfterSoleNotOperator;
use Tighten\Linters\SpacesAroundBladeRenderContent;
use Tighten\Linters\ConcatenationSpacing;
use Tighten\Linters\TrailingCommasOnArrays;
use Tighten\Linters\UseAuthHelperOverFacade;
use Tighten\Linters\UseConfigOverEnv;
use Tighten\Linters\ViewWithOverArrayParameters;
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
                new InputOption(
                    'json'
                ),
                new InputOption(
                    'only',
                    null,
                    InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                    'The subset of linters to use'
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

        if (! empty($input->getOption('only'))) {
            $linters = array_intersect_key($linters, array_flip(array_map(function ($className) {
                return 'Tighten\\Linters\\' . $className;
            }, $input->getOption('only'))));
        }

        $tighten = new TLint;

        $lints = [];

        foreach ($linters as $linterClass => $parseAs) {
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
                        new CustomNode(['startLine' => $e->getStartLine()])
                    ),
                ]);
            }
        }

        if ($input->getOption('json')) {
            return $this->outputLintsAsJson($output, $file, $lints);
        } else {
            return $this->outputLints($output, $file, $lints);
        }
    }

    private function outputLintsAsJson(OutputInterface $output, $file, $lints)
    {
        $errors = array_map(function (Lint $lint) {
            $title = explode(PHP_EOL, (string) $lint)[0];

            return [
                'line' => $lint->getNode()->getStartLine(),
                'message' => $title,
                'source' => class_basename($lint->getLinter()),
            ];
        }, $lints);

        $output->writeln(json_encode([
            'errors' => $errors,
        ]));
    }

    private function outputLints(OutputInterface $output, $file, $lints)
    {
        if (! empty($lints)) {
            $output->writeln([
                "Lints for {$file}",
                '============',
            ]);

            foreach ($lints as $lint) {
                [$title, $codeLine] = explode(PHP_EOL, (string) $lint);

                $output->writeln([
                    "<fg=yellow>{$title}</>",
                    $codeLine,
                ]);
            }

            $output->writeln(['']);

            return self::LINTS_FOUND_OR_ERROR;
        }

        return self::NO_LINTS_FOUND_OR_SUCCESS;
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
                ViewWithOverArrayParameters::class => '.php',
                NoLeadingSlashesOnRoutePaths::class => '.php',
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
                ViewWithOverArrayParameters::class => '.php',
                PureRestControllers::class => '.php',
                RestControllersMethodOrder::class => '.php',
                RequestHelperFunctionWherePossible::class => '.php',
                ApplyMiddlewareInRoutes::class => '.php',
                NoCompact::class => '.php',
                RequestValidation::class => '.php',
            ];
        }

        return [];
    }

    private function getTestFilesLinters($path)
    {
        if (strpos($path, 'tests') !== false) {
            return [
                NoMethodVisibilityInTests::class => '.php',
            ];
        }

        return [];
    }

    private function getBladeTemplatesLinters($path)
    {
        if (strpos($path, '.blade.php') !== false) {
            return [
                SpaceAfterBladeDirectives::class => '.php',
                NoSpaceAfterBladeDirectives::class => '.php',
                SpacesAroundBladeRenderContent::class => '.php',
                UseAuthHelperOverFacade::class => '.blade.php',
            ];
        }

        return [];
    }

    private function getMailableLinters($path)
    {
        return [
            MailableMethodsInBuild::class => '.php',
        ];
    }

    private function getNonConfigFileLinters($path)
    {
        if (strpos($path, '/config/') === false) {
            return [
                UseConfigOverEnv::class => '.php',
            ];
        }

        return [];
    }

    private function getLinters($path)
    {
        return array_merge(
            [
                RemoveLeadingSlashNamespaces::class => '.php',
                QualifiedNamesOnlyForClassName::class => '.php',
                UseAuthHelperOverFacade::class => '.php',
                ImportFacades::class => '.php',
                ModelMethodOrder::class => '.php',
                ClassThingsOrder::class => '.php',
                AlphabeticalImports::class => '.php',
                TrailingCommasOnArrays::class => '.php',
                NoParensEmptyInstantiations::class => '.php',
                SpaceAfterSoleNotOperator::class => '.php',
                OneLineBetweenClassVisibilityChanges::class => '.php',
                NoStringInterpolationWithoutBraces::class => '.php',
                ConcatenationSpacing::class => '.php',
                NoDd::class => '.php',
                NoInlineVarDocs::class => '.php',
                NoUnusedImports::class => '.php',
                NewLineAtEndOfFile::class => '.php',
            ],
            $this->getRoutesFilesLinters($path),
            $this->getControllerFilesLinters($path),
            $this->getTestFilesLinters($path),
            $this->getBladeTemplatesLinters($path),
            $this->getMigrationsLinters($path),
            $this->getMailableLinters($path),
            $this->getNonConfigFileLinters($path)
        );
    }
}
