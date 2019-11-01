<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tighten\Commands\LintCommand;

class LaravelPresetPassesForFreshInstallTest extends TestCase
{
    /** @test */
    function no_lints_when_using_laravel_preset()
    {
        $application = new Application;
        $command = new LintCommand(__DIR__ . '/LaravelApp');
        $application->add($command);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command'  => $command->getName(),
            'file or directory' => '.',
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}
