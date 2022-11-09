<?php

namespace Tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Formatters\SpaceAfterBladeDirectives;
use Tighten\TLint\TFormat;

class SpaceAfterBladeDirectivesTest extends TestCase
{
    /** @test */
    public function it_adds_space_to_if_statement()
    {
        $file = <<<file
@if(true)
    This is true.
@elseif(false)
    This is false.
@endforeach
file;

        $formatted = (new TFormat)->format(
            new SpaceAfterBladeDirectives($file)
        );

        $correctlyFormatted = <<<file
@if (true)
    This is true.
@elseif (false)
    This is false.
@endforeach
file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function it_adds_space_to_unless_statement()
    {
        $file = <<<file
@unless(true)
    This isn't true.
@endforeach
file;

        $formatted = (new TFormat)->format(
            new SpaceAfterBladeDirectives($file)
        );

        $correctlyFormatted = <<<file
@unless (true)
    This isn't true.
@endforeach
file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function it_adds_space_to_for_statement()
    {
        $file = <<<'file'
@for($i = 0; $i < 10; $i++)
    The current value is {{ $i }}
@endfor
file;

        $formatted = (new TFormat)->format(
            new SpaceAfterBladeDirectives($file)
        );

        $correctlyFormatted = <<<'file'
@for ($i = 0; $i < 10; $i++)
    The current value is {{ $i }}
@endfor
file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function it_adds_space_to_foreach_statement()
    {
        $file = <<<'file'
@foreach($users as $user)
    <li>{{ $user->name }}</li>
@endforeach
file;

        $formatted = (new TFormat)->format(
            new SpaceAfterBladeDirectives($file)
        );

        $correctlyFormatted = <<<'file'
@foreach ($users as $user)
    <li>{{ $user->name }}</li>
@endforeach
file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function it_adds_space_to_forelse_statement()
    {
        $file = <<<'file'
@forelse($users as $user)
    <li>{{ $user->name }}</li>
@empty
    <p>No users</p>
@endforelse
file;

        $formatted = (new TFormat)->format(
            new SpaceAfterBladeDirectives($file)
        );

        $correctlyFormatted = <<<'file'
@forelse ($users as $user)
    <li>{{ $user->name }}</li>
@empty
    <p>No users</p>
@endforelse
file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function it_adds_space_to_while_statement()
    {
        $file = <<<file
@while(true)
    <p>I'm looping forever.</p>
@endwhile
file;

        $formatted = (new TFormat)->format(
            new SpaceAfterBladeDirectives($file)
        );

        $correctlyFormatted = <<<file
@while (true)
    <p>I'm looping forever.</p>
@endwhile
file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function it_ignores_correctly_spaced_directives()
    {
        $file = <<<'file'
@foreach ($users as $user)
    <li>{{ $user->name }}</li>
@endforeach
file;

        $formatted = (new TFormat)->format(
            new SpaceAfterBladeDirectives($file)
        );

        $this->assertEquals($file, $formatted);
    }

    /** @test */
    public function it_adds_space_to_kitchen_sink()
    {
        $file = <<<'file'
@if(true)
    This is true.
@elseif(false)
    This is false.
@endif

@if(true) @if($inline) Inline @endif @endif

@unless(true)
    This isn't true.
@endunless

@for($i = 0; $i < 10; $i++)
    The current value is {{ $i }}
@endfor

@foreach($users as $user)
    @foreach($user->emails as $email)
        <li>{{ $email }}</li>
    @endforeach
@endforeach

@forelse($users as $user)
    <li>{{ $user->name }}</li>
@empty
    <p>No users</p>
@endforelse

@while(true)
    <p>I'm looping forever.</p>
@endwhile
file;

        $formatted = (new TFormat)->format(
            new SpaceAfterBladeDirectives($file)
        );

        $correctlyFormatted = <<<'file'
@if (true)
    This is true.
@elseif (false)
    This is false.
@endif

@if (true) @if ($inline) Inline @endif @endif

@unless (true)
    This isn't true.
@endunless

@for ($i = 0; $i < 10; $i++)
    The current value is {{ $i }}
@endfor

@foreach ($users as $user)
    @foreach ($user->emails as $email)
        <li>{{ $email }}</li>
    @endforeach
@endforeach

@forelse ($users as $user)
    <li>{{ $user->name }}</li>
@empty
    <p>No users</p>
@endforelse

@while (true)
    <p>I'm looping forever.</p>
@endwhile
file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }
}
