<?php

namespace Tighten\Commands;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tighten\Linters\NoSpaceAfterBladeDirectives;
use Tighten\Linters\SpaceAfterBladeDirectives;
use Tighten\Linters\FQCNOnlyForClassName;
use Tighten\Linters\RemoveLeadingSlashNamespaces;
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
            ->addArgument('file or directory', InputArgument::REQUIRED, 'The file or directory to lint');
    }

    private function lintFile(InputInterface $input, OutputInterface $output, $file)
    {
        $linters = $this->getLinters($file);

        $tighten = new TLint;

        $lints = [];
        foreach ($linters as $linter) {
            $lints = array_merge($lints, $tighten->lint(new $linter(file_get_contents($file))));
        }

        if (!empty($lints)) {
            $output->writeln([
                "Lints for $file: ",
                '============',
            ]);

            foreach ($lints as $lint) {
                $output->writeln((string) $lint);
            }
        }
    }

    private function filesInDir($directory, $fileExtension)
    {
        $directory = realpath($directory);
        $it = new \RecursiveDirectoryIterator($directory);
        $it = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::LEAVES_ONLY);
        $it = new \RegexIterator($it, '(\.' . preg_quote($fileExtension) . '$)');

        foreach ($it as $file) {
            /** @var \SplFileObject $file */
            $filepath = $file->getRealPath();

            if (strpos($filepath, 'vendor') !== false
            || strpos($filepath, 'public/index.php')) {
                continue;
            }

            yield $filepath;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (is_file($input->getArgument('file or directory'))) {
            $this->lintFile($input, $output, $input->getArgument('file or directory'));
        } elseif (is_dir($input->getArgument('file or directory'))) {
            foreach ($this->filesInDir($input->getArgument('file or directory'), 'php') as $file) {
                $this->lintFile($input, $output, $file);
            }
        } else {
            $output->writeln('No file or directory found at ' . $input->getArgument('file or directory'));

            return 1;
        }

        return 0;
    }

    private function getLinters($path)
    {
        $linters = [
            RemoveLeadingSlashNamespaces::class,
            FQCNOnlyForClassName::class,
        ];

        if (strpos($path, 'routes') !== false
            || strpos($path, 'app/Http/Controllers') !== false) {
            $linters[] = ViewWithOverArrayParamaters::class;
        }

        if (strpos($path, '.blade.php') !== false) {
            $linters[] = SpaceAfterBladeDirectives::class;
            $linters[] = NoSpaceAfterBladeDirectives::class;
        }

        return $linters;
    }
}
