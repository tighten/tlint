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
}
