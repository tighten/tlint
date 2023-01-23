<?php

namespace Tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Formatters\NoSpaceAfterBladeDirectives;
use Tighten\TLint\TFormat;

class NoSpaceAfterBladeDirectivesTest extends TestCase
{
    /** @test */
    public function it_removes_spaces_from_directives()
    {
        $file = <<<'file'
@section ('sidebar')
    This is the master sidebar.
@show

<div class="container">
    @yield ('content')
</div>

<span @class (['p-4'])>Padding</span>

<input type="checkbox" value="active" @checked (old('active', $user->active)) />

<select name="version">
    @foreach ($product->versions as $version)
        <option @selected (old('version') == $version)>
            {{ $version }}
        </option>
    @endforeach
</select>

@auth The user is authenticated @endauth

@auth ('admin')
    // The user is authenticated...
@endauth
file;

        $formatted = (new TFormat)->format(
            new NoSpaceAfterBladeDirectives($file)
        );

        $correctlyFormatted = <<<'file'
@section('sidebar')
    This is the master sidebar.
@show

<div class="container">
    @yield('content')
</div>

<span @class(['p-4'])>Padding</span>

<input type="checkbox" value="active" @checked(old('active', $user->active)) />

<select name="version">
    @foreach ($product->versions as $version)
        <option @selected(old('version') == $version)>
            {{ $version }}
        </option>
    @endforeach
</select>

@auth The user is authenticated @endauth

@auth('admin')
    // The user is authenticated...
@endauth
file;

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function it_ignores_directives_where_space_required()
    {
        $file = <<<'file'
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

        $formatted = (new TFormat)->format(
            new NoSpaceAfterBladeDirectives($file)
        );

        $this->assertEquals($file, $formatted);
    }
}
