<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tighten\Commands\LintCommand;

class DoesNotLintBootstrapFilesTest extends TestCase
{
    /** @test */
    function does_not_lint_bootstrap_app_php()
    {
        $application = new Application;
        $command = new LintCommand(__DIR__ . '/LaravelApp');
        $application->add($command);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command'  => $command->getName(),
            'file or directory' => 'bootstrap/app.php',
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}
