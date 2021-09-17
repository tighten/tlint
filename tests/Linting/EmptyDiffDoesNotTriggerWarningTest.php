<?php

namespace Tests\Linting;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Utils\ParsesGitOutput;

class EmptyDiffDoesNotTriggerWarningTest extends TestCase
{
    /** @test */
    public function gracefully_handles_empty_diff()
    {
        $files = ParsesGitOutput::parseFilesFromGitDiffOutput('');

        $this->assertCount(0, $files);
    }
}
