<?php

namespace Tighten\Commands;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tighten\Lint;
use Tighten\Linters\ImportFacades;
use Tighten\Linters\MailableMethodsInBuild;
use Tighten\Linters\NoDocBlocksForMigrationUpDown;
use Tighten\Linters\NoSpaceAfterBladeDirectives;
use Tighten\Linters\PureRestControllers;
use Tighten\Linters\RequestHelperFunctionWherePossible;
use Tighten\Linters\RestControllersMethodOrder;
use Tighten\Linters\SpaceAfterBladeDirectives;
use Tighten\Linters\QualifiedNamesOnlyForClassName;
use Tighten\Linters\RemoveLeadingSlashNamespaces;
use Tighten\Linters\UseAuthHelperOverFacade;
use Tighten\Linters\ViewWithOverArrayParamaters;
use Tighten\TLint;

class LintCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('lint')
            ->setDescription('Lints a file.')
            ->setHelp('This command allows you to lint a laravel file.');

        $this
            ->addArgument(
                'file or directory',
                InputArgument::OPTIONAL,
                'The file or directory to lint',
                getcwd()
            );
    }

    private function lintFile(InputInterface $input, OutputInterface $output, $file)
    {
        if ($this->isBlacklisted($file)) {
            return 0;
        }

        $linters = $this->getLinters($file);

        $tighten = new TLint;

        $lints = [];
        foreach ($linters as $linter => $parseAs) {
            $lints = array_merge($lints, $tighten->lint(new $linter(
                file_get_contents($file),
                $parseAs
            )));
        }

        if (!empty($lints)) {
            $output->writeln([
                PHP_EOL,
                "Lints for $file: ",
                '============',
            ]);

            foreach ($lints as $lint) {
                /** @var Lint $lint */
                $output->writeln((string) $lint);
            }
        }

        /**
         * CI error codes
         * 0: no lints
         * 1: lints found
         */
        return empty($lints) ? 0 : 1;
    }

    private function isBlacklisted($filepath)
    {
        return strpos($filepath, 'vendor') !== false
            || strpos($filepath, 'app/Http/Middleware/RedirectIfAuthenticated.php') !== false
            || strpos($filepath, 'public/index.php') !== false
            || strpos($filepath, 'bootstrap/app.php') !== false
            || strpos($filepath, 'storage/framework/views') !== false;
    }

    private function filesInDir($directory, $fileExtension)
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

        if ($this->isBlacklisted($fileOrDirectory)) {
            return 0;
        }

        if (is_file($fileOrDirectory)) {
            return $this->lintFile($input, $output, $fileOrDirectory);
        }

        if (is_dir($fileOrDirectory)) {
            $finalResponseCode = 0;

            foreach ($this->filesInDir($fileOrDirectory, 'php') as $file) {
                if ($this->lintFile($input, $output, $file) === 1) {
                    $finalResponseCode = 1;
                }
            }

            return $finalResponseCode;
        }

        $output->writeln('No file or directory found at ' . $fileOrDirectory);
        return 1;
    }

    private function getRoutesFilesLinters($path)
    {
        if (strpos($path, 'routes') !== false) {
            return [
                ViewWithOverArrayParamaters::class => '.php'
            ];
        }

        return [];
    }

    private function getMigrationsLinters($path)
    {
        if (strpos($path, 'migrations') !== false) {
            return [
                NoDocBlocksForMigrationUpDown::class => '.php'
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
                RestControllersMethodOrder::class => '.php',
                RequestHelperFunctionWherePossible::class => '.php',
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

    private function getLinters($path)
    {
        return array_merge(
            [
                RemoveLeadingSlashNamespaces::class => '.php',
                QualifiedNamesOnlyForClassName::class => '.php',
                UseAuthHelperOverFacade::class => '.php',
                ImportFacades::class => '.php',
            ],
            $this->getRoutesFilesLinters($path),
            $this->getControllerFilesLinters($path),
            $this->getBladeTemplatesLinters($path),
            $this->getMigrationsLinters($path),
            $this->getMailableLinters($path)
        );
    }
}
