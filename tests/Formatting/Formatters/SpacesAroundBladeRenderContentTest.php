<?php

namespace Tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Formatters\SpacesAroundBladeRenderContent;
use Tighten\TLint\TFormat;

class SpacesAroundBladeRenderContentTest extends TestCase
{
    /** @test */
    public function catches_missing_spaces_around_blade_render_content()
    {
        $file = <<<'file'
{{1 + 1}}
file;

        $correctlyFormatted = <<<'file'
{{ 1 + 1 }}
file;

        $formatted = (new TFormat())->format(
            new SpacesAroundBladeRenderContent($file)
        );

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function catches_missing_spaces_around_blade_render_content_after_correctly_spaced()
    {
        $file = <<<'file'
{{ 1 + 1 }} {{1 + 1}}
file;

        $correctlyFormatted = <<<'file'
{{ 1 + 1 }} {{ 1 + 1 }}
file;

        $formatted = (new TFormat())->format(
            new SpacesAroundBladeRenderContent($file)
        );

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function catches_extra_spaces_around_blade_render_content()
    {
        $file = <<<'file'
{{1 + 1    }}
file;

        $correctlyFormatted = <<<'file'
{{ 1 + 1 }}
file;

        $formatted = (new TFormat())->format(
            new SpacesAroundBladeRenderContent($file)
        );

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function catches_missing_spaces_around_raw_blade_render_content()
    {
        $file = <<<'file'
{!!$a!!}
file;

        $correctlyFormatted = <<<'file'
{!! $a !!}
file;

        $formatted = (new TFormat())->format(
            new SpacesAroundBladeRenderContent($file)
        );

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function does_not_trigger_when_spaces_are_placed_correctly_raw_blade_render_content()
    {
        $file = <<<'file'
{!! $a !!}
file;

        $formatted = (new TFormat())->format(
            new SpacesAroundBladeRenderContent($file)
        );

        $this->assertEquals($file, $formatted);
    }

    /** @test */
    public function does_not_trigger_when_spaces_are_placed_correctly()
    {
        $file = <<<'file'
{{ 1 + 1 }}
file;

        $formatted = (new TFormat())->format(
            new SpacesAroundBladeRenderContent($file)
        );

        $this->assertEquals($file, $formatted);
    }

    /** @test */
    public function does_not_trigger_on_multiline_renders()
    {
        $file = <<<'file'
{{
1 + 1
}}
file;

        $formatted = (new TFormat())->format(
            new SpacesAroundBladeRenderContent($file)
        );

        $this->assertEquals($file, $formatted);
    }

    /** @test */
    public function does_not_trigger_on_blade_comment()
    {
        $file = <<<'file'
{{-- This comment will not be present in the rendered HTML --}}
file;

        $formatted = (new TFormat())->format(
            new SpacesAroundBladeRenderContent($file)
        );

        $this->assertEquals($file, $formatted);
    }

    /** @test */
    public function catches_space_to_render_content_complex()
    {
        $file = <<<'file'
<div class="mt-6 bg-white shadow-sm rounded-lg divide-y">
    @foreach ($chirps as $chirp)
        <div class="p-6 flex space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 -scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <div class="flex-1">
                <div class="flex justify-between items-center">
                    <div>
                        <span class="text-gray-800">{{$chirp->user->name}} {{$chirp->user->created_at   }}</span>
                        <small class="ml-2 text-sm text-gray-600">{{ $chirp->created_at->format('j M Y, g:i a') }}</small>
                    </div>
                </div>
                <p class="mt-4 text-lg text-gray-900">{!!$chirp->message!!}</p>
            </div>
        </div>
    @endforeach
</div>
file;

        $formatted = (new TFormat())->format(
            new SpacesAroundBladeRenderContent($file)
        );

        $correctlyFormatted = <<<'file'
<div class="mt-6 bg-white shadow-sm rounded-lg divide-y">
    @foreach ($chirps as $chirp)
        <div class="p-6 flex space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 -scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <div class="flex-1">
                <div class="flex justify-between items-center">
                    <div>
                        <span class="text-gray-800">{{ $chirp->user->name }} {{ $chirp->user->created_at }}</span>
                        <small class="ml-2 text-sm text-gray-600">{{ $chirp->created_at->format('j M Y, g:i a') }}</small>
                    </div>
                </div>
                <p class="mt-4 text-lg text-gray-900">{!! $chirp->message !!}</p>
            </div>
        </div>
    @endforeach
</div>
file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }
}
