<?php

namespace Tighten\Commands;

use PhpParser\Error;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Tighten\CustomNode;
use Tighten\Lint;
use Tighten\TLint;

class LintCommand extends BaseCommand
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
            ->setHelp('This command allows you to lint a php/laravel file/directory.');
    }

    private function lintFile(InputInterface $input, OutputInterface $output, $file)
    {
        if ($this->isBlacklisted($file)) {
            return self::NO_LINTS_FOUND_OR_SUCCESS;
        }

        $linters = $this->getLinters($file);

        if (! empty($only = $input->getOption('only'))) {
            $linters = array_filter($linters, function($linter) use ($only) {
                foreach ($only as $filter) {
                    if (false !== strpos($linter, $filter)) {
                        return true;
                    }
                }
                return false;
            });
        }

        $tighten = new TLint;

        $lints = [];

        foreach ($linters as $linterClass) {
            $linterInstance = new $linterClass(
                file_get_contents($file),
                $file
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
                'source' => basename(str_replace('\\', '/', get_class($lint->getLinter()))),
            ];
        }, $lints);

        $output->writeln(json_encode([
            'errors' => $errors,
        ]));

        return self::NO_LINTS_FOUND_OR_SUCCESS;
    }

    private function outputLints(OutputInterface $output, $file, $lints)
    {
        if (! empty($lints)) {
            $output->writeln([
                "Lints for {$file}",
                '============',
            ]);

            foreach ($lints as $lint) {
                $lines = explode(PHP_EOL, (string) $lint);
                $title = array_shift($lines);
                $codeLine = array_pop($lines);

                $output->writeln("<fg=yellow>{$title}</>");
                if (! empty($lines)) {
                    $output->writeln($lines, OutputInterface::OUTPUT_NORMAL | OutputInterface::VERBOSITY_VERBOSE);
                }
                $output->writeln("<fg=magenta>{$codeLine}</>");
            }

            $output->writeln(['']);

            return self::LINTS_FOUND_OR_ERROR;
        }

        return self::NO_LINTS_FOUND_OR_SUCCESS;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileOrDirectory = $this->resolveFileOrDirectory($input->getArgument('file or directory'));
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
            $output->writeln('No file or directory found at ' . $input->getArgument('file or directory'));

            return self::LINTS_FOUND_OR_ERROR;
        }


        if($input->getOption('json')) {
            return self::NO_LINTS_FOUND_OR_SUCCESS;
        }


        if($finalResponseCode === self::NO_LINTS_FOUND_OR_SUCCESS) {
            $output->writeLn('LGTM!');
        }

        return $finalResponseCode;
    }

    private function getLinters($path)
    {
        return array_filter($this->config->getLinters(), function($className) use ($path) {
            return $className::appliesToPath($path);
        });
    }
}
