<?php

namespace Tests\Linting;

use DomDocument;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tighten\TLint\Commands\LintCommand;
use Tighten\TLint\Linters\OneLineBetweenClassVisibilityChanges;

class CanOutputLintsAsCheckstyleTest extends TestCase
{
    /** @test */
    public function can_use_checkstyle_flag_with_lints()
    {
        $application = new Application;
        $command = new LintCommand;
        $application->add($command);
        $commandTester = new CommandTester($command);

        $file = <<<'file'
<?php

class Test
{
    public $test1;
    private $test2;
}

file;

        $filePath = tempnam(sys_get_temp_dir(), 'test');

        file_put_contents($filePath, $file);

        $commandTester->execute([
            'command' => $command->getName(),
            'file or directory' => $filePath,
            '--checkstyle' => true,
        ]);

        $output = $commandTester->getDisplay();
        $xml = new DomDocument;
        $xml->loadXML($output);
        $error = $xml->getElementsByTagName('error')->item(0)->attributes;

        $this->assertEquals('6', $error->getNamedItem('line')->nodeValue);
        $this->assertEquals('error', $error->getNamedItem('severity')->nodeValue);
        $this->assertEquals('! ' . OneLineBetweenClassVisibilityChanges::DESCRIPTION, $error->getNamedItem('message')->nodeValue);
        $this->assertEquals('OneLineBetweenClassVisibilityChanges', $error->getNamedItem('source')->nodeValue);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    /** @test */
    public function can_use_checkstyle_flag_without_lints()
    {
        $application = new Application;
        $command = new LintCommand;
        $application->add($command);
        $commandTester = new CommandTester($command);

        $file = <<<'file'
<?php

echo 'a';

file;

        $filePath = tempnam(sys_get_temp_dir(), 'test');

        file_put_contents($filePath, $file);

        $commandTester->execute([
            'command' => $command->getName(),
            'file or directory' => $filePath,
            '--checkstyle' => true,
        ]);

        $output = $commandTester->getDisplay();

        $xml = new DOMDocument;
        $xml->loadXML($output);
        $this->assertEquals(0, $xml->getElementsByTagName('file')->length);
        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}
