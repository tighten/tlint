<?php

namespace Tighten\TLint\Commands;

use DOMDocument;
use DOMElement;
use PhpParser\Error;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Tighten\TLint\CustomNode;
use Tighten\TLint\Lint;
use Tighten\TLint\TLint;

class LintCommand extends BaseCommand
{
    private const NO_LINTS_FOUND_OR_SUCCESS = 0;
    private const LINTS_FOUND_OR_ERROR = 1;
    private DOMElement $checkstyle;

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
                    'checkstyle'
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileOrDirectory = $this->resolveFileOrDirectory($input->getArgument('file or directory'));
        $finalResponseCode = self::NO_LINTS_FOUND_OR_SUCCESS;

        if ($this->isBlacklisted($fileOrDirectory)) {
            return self::NO_LINTS_FOUND_OR_SUCCESS;
        }

        if ($input->getOption('checkstyle')) {
            $dom = new DOMDocument('1.0', 'UTF-8');
            $this->checkstyle = $dom->createElement('checkstyle');
            $this->checkstyle->setAttribute('version', '3.7.2');
            $dom->appendChild($this->checkstyle);
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

        if ($input->getOption('json')) {
            return self::NO_LINTS_FOUND_OR_SUCCESS;
        }

        if ($input->getOption('checkstyle')) {
            $dom->formatOutput = true;
            $output->write($dom->saveXML());

            return self::NO_LINTS_FOUND_OR_SUCCESS;
        }

        if ($finalResponseCode === self::NO_LINTS_FOUND_OR_SUCCESS) {
            $output->writeLn('LGTM!');
        }

        return $finalResponseCode;
    }

    private function lintFile(InputInterface $input, OutputInterface $output, $file)
    {
        if ($this->isBlacklisted($file)) {
            return self::NO_LINTS_FOUND_OR_SUCCESS;
        }

        $linters = $this->getLinters($file);

        if (! empty($only = $input->getOption('only'))) {
            $linters = array_filter($this->getAllLinters($file), function ($linter) use ($only) {
                foreach ($only as $filter) {
                    if (strpos($linter, $filter) !== false) {
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
        }

        if ($input->getOption('checkstyle')) {
            return $this->outputLintsAsCheckstyle($output, $file, $lints);
        }

        return $this->outputLints($output, $file, $lints);
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

    private function outputLintsAsCheckstyle(OutputInterface $output, $file, $lints)
    {
        if (! empty($lints)) {
            foreach ($lints as $lint) {
                $fileNode = $this->checkstyle->ownerDocument->createElement('file');
                $fileNode->setAttribute('name', $file);
                $this->checkstyle->appendChild($fileNode);

                $title = explode(PHP_EOL, (string) $lint)[0];

                $errorNode = $this->checkstyle->ownerDocument->createElement('error');
                $errorNode->setAttribute('line', $lint->getNode()->getStartLine());
                $errorNode->setAttribute('severity', 'error');
                $errorNode->setAttribute('message', $title);
                $errorNode->setAttribute('source', basename(str_replace('\\', '/', get_class($lint->getLinter()))));

                $fileNode->appendChild($errorNode);
            }

            return self::LINTS_FOUND_OR_ERROR;
        }

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

    private function getLinters($path)
    {
        return array_filter($this->config->getLinters(), function ($className) use ($path) {
            return $className::appliesToPath($path, $this->config->paths);
        });
    }

    private function getAllLinters($path)
    {
        $linters = [];
        foreach (glob(__DIR__ . '/../Linters/*.php') as $file) {
            $linters[] = 'Tighten\TLint\Linters\\' . basename($file, '.php');
        }

        return array_filter($linters, function ($className) use ($path) {
            return $className::appliesToPath($path, $this->config->paths);
        });
    }
}
