<?php

namespace Tests\Linting;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tighten\TLint\Commands\LintCommand;

class InvalidFileOrDirectoryGivesMessageTest extends TestCase
{
    /** @test */
    public function gracefully_handles_non_existent_file()
    {
        $application = new Application();
        $command = new LintCommand(__DIR__);
        $application->add($command);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            'file or directory' => 'non-existent-file.php',
        ]);

        $this->assertStringContainsString('No file or directory found at non-existent-file.php', $commandTester->getDisplay());
        $this->assertEquals(1, $commandTester->getStatusCode());
    }

    /** @test */
    public function gracefully_handles_non_existent_directory()
    {
        $application = new Application();
        $command = new LintCommand(__DIR__);
        $application->add($command);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            'file or directory' => 'non-existent-directory',
        ]);

        $this->assertStringContainsString('No file or directory found at non-existent-directory', $commandTester->getDisplay());
        $this->assertEquals(1, $commandTester->getStatusCode());
    }
}
