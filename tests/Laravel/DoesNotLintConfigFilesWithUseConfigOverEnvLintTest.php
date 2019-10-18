<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tighten\Commands\LintCommand;

class DoesNotLintConfigFilesWithUseConfigOverEnvLintTest extends TestCase
{
    /** @test */
    function does_not_lint_config_files_with_use_config_over_env_lint()
    {
        $application = new Application;
        $command = new LintCommand(__DIR__ . '/LaravelApp');
        $application->add($command);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command'  => 'lint --only=UseConfigOverEnv',
            'file or directory' => 'config/app.php',
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}
