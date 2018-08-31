<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tighten\Commands\LintCommand;

class DoesNotLintPublicFilesTest extends TestCase
{
    /** @test */
    public function does_not_lint_public_app_js()
    {
        $application = new Application;

        $command = new LintCommand;
        $application->add($command);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command'  => $command->getName(),
            'file or directory' => 'public/app.js',
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    /** @test */
    public function does_lint_app_js_outside_public()
    {
        $application = new Application;

        $command = new LintCommand;
        $application->add($command);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command'  => $command->getName(),
            'file or directory' => 'app.js',
        ]);

        $this->assertEquals(1, $commandTester->getStatusCode());
    }
}
