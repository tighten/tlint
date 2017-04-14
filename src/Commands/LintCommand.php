<?php

namespace Tighten\Commands;

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
            ->addArgument('file', InputArgument::REQUIRED, 'The file to lint');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists($input->getArgument('file'))) {
            return $output->writeln('No file found at ' . $input->getArgument('file'));
        }

        $output->writeln([
            'Linting ' . $input->getArgument('file'),
            '============',
        ]);

        $linters = $this->getLinters($input->getArgument('file'));

        $tighten = new TLint;

        $lints = [];
        foreach ($linters as $linter) {
            $lints = array_merge($lints, $tighten->lint(new $linter(file_get_contents($input->getArgument('file')))));
        }

        $output->writeln([
            'Lints: ',
            '============',
        ]);

        foreach ($lints as $lint) {
            $output->writeln((string) $lint);
        }
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
