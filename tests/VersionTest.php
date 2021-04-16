<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use function Tighten\TLint\version;

class VersionTest extends TestCase
{
    /** @test */
    function can_get_tlint_version()
    {
        $this->assertNotEmpty(version());
    }
}
