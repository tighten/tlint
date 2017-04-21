<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tighten\Commands\LintCommand;

class DoesNotLintCoreLaravelFilesTest extends TestCase
{
    /** @test */
    public function app_Http_Middleware_RedirectIfAuthenticated()
    {
        $application = new Application();

        $command = new LintCommand();
        $application->add($command);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command'  => $command->getName(),
            'file or directory' => 'app/Http/Middleware/RedirectIfAuthenticated.php',
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    /** @test */
    public function does_not_lint_auth_scaffolding()
    {
        $application = new Application();

        $command = new LintCommand();
        $application->add($command);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command'  => $command->getName(),
            'file or directory' => 'app/Http/Controllers/Auth',
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}
