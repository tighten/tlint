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

        $command = new LintCommand;
        $application->add($command);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command'  => $command->getName(),
            'file or directory' => __DIR__ . '/Fixtures/UseConfigOverEnvFixtures',
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}
