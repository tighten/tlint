<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use function Tighten\version;

class VersionTest extends TestCase
{
    /** @test */
    function can_get_tlint_version()
    {
        $this->assertNotEmpty(version());
    }

    /** @test */
    function version_is_updated()
    {
        $checkTag = Process::fromShellCommandline('git describe --abbrev=0 --tag');
        $checkTag->run();
        $this->assertEquals(trim($checkTag->getOutput()), version());
    }
}
