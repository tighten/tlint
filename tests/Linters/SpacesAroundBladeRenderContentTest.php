<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\SpacesAroundBladeRenderContent;
use Tighten\TLint;

class SpacesAroundBladeRenderContentTest extends TestCase
{
    /** @test */
    public function catches_missing_spaces_around_blade_render_content()
    {
        $file = <<<file
        {{1 + 1}}
file;

        $lints = (new TLint)->lint(
            new SpacesAroundBladeRenderContent($file)
        );

        $this->assertEquals(1, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function catches_missing_spaces_around_raw_blade_render_content()
    {
        $file = <<<file
        {!!\$a!!}
file;

        $lints = (new TLint)->lint(
            new SpacesAroundBladeRenderContent($file)
        );

        $this->assertEquals(1, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function does_not_trigger_when_spaces_are_placed_correctly()
    {
        $file = <<<file
        {{ 1 + 1 }}
file;

        $lints = (new TLint)->lint(
            new SpacesAroundBladeRenderContent($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function does_not_trigger_on_multiline_renders()
    {
        $file = <<<file
        {{
        1 + 1
        }}
file;

        $lints = (new TLint)->lint(
            new SpacesAroundBladeRenderContent($file)
        );

        $this->assertEmpty($lints);
    }
}
