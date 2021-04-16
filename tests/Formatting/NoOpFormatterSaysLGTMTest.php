<?php

namespace Tests\Formatting;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tighten\TLint\Commands\FormatCommand;

class NoOpFormatterSaysLGTMTest extends TestCase
{
    /** @test */
    function no_op_says_lgtm()
    {
        $application = new Application;

        $command = new FormatCommand;
        $application->add($command);
        $commandTester = new CommandTester($command);

        $file = <<<file
<?php

namespace App;

class Thing
{

}

file;

        $filePath = tempnam(sys_get_temp_dir(), 'test');

        file_put_contents($filePath, $file);

        $commandTester->execute([
            'command'  => $command->getName(),
            'file or directory' => $filePath,
        ]);

        $this->assertStringContainsString("LGTM!", $commandTester->getDisplay());
        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}
