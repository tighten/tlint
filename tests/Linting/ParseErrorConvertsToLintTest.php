<?php

namespace tests\Linting;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tighten\Commands\LintCommand;

class ParseErrorConvertsToLintTest extends TestCase
{
    /** @test */
    function gracefully_handles_parse_error()
    {
        $application = new Application;
        $command = new LintCommand;
        $application->add($command);
        $commandTester = new CommandTester($command);

        $file = <<<file
<?php

namespace App;

class Thing
{
    const OK

    retunr 1
}
file;

        $filePath = tempnam(sys_get_temp_dir(), 'test');

        file_put_contents($filePath, $file);

        $commandTester->execute([
            'command'  => $command->getName(),
            'file or directory' => $filePath,
        ]);

        $this->assertStringContainsString("unexpected T_STRING, expecting '='", $commandTester->getDisplay());
        $this->assertEquals(1, $commandTester->getStatusCode());
    }
}
