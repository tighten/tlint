<?php

namespace Tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Linters\SpaceAfterBladeDirectives;
use Tighten\TLint\TLint;

class SpaceAfterBladeDirectivesTest extends TestCase
{
    /** @test */
    public function it_catches_missing_space_if_statement()
    {
        $file = <<<'file'
@if(true)
    This is true.
@elseif(false)
    This is false.
@endif
file;

        $lints = (new TLint())->lint(
            new SpaceAfterBladeDirectives($file)
        );

        $this->assertEquals(1, $lints[0]->getNode()->getLine());
        $this->assertEquals(3, $lints[1]->getNode()->getLine());
    }

    /** @test */
    public function it_catches_missing_space_unless_statement()
    {
        $file = <<<'file'
@unless(true)
    This isn't true.
@endunless
file;

        $lints = (new TLint())->lint(
            new SpaceAfterBladeDirectives($file)
        );

        $this->assertEquals(1, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function it_catches_missing_space_for_statement()
    {
        $file = <<<'file'
@for($i = 0; $i < 10; $i++)
    The current value is {{ $i }}
@endfor
file;

        $lints = (new TLint())->lint(
            new SpaceAfterBladeDirectives($file)
        );

        $this->assertEquals(1, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function it_catches_missing_space_foreach_statement()
    {
        $file = <<<'file'
@foreach($users as $user)
    <li>{{ $user->name }}</li>
@endforeach
file;

        $lints = (new TLint())->lint(
            new SpaceAfterBladeDirectives($file)
        );

        $this->assertEquals(1, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function it_catches_missing_space_forelse_statement()
    {
        $file = <<<'file'
@forelse($users as $user)
    <li>{{ $user->name }}</li>
@empty
    <p>No users</p>
@endforelse
file;

        $lints = (new TLint())->lint(
            new SpaceAfterBladeDirectives($file)
        );

        $this->assertEquals(1, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function it_catches_missing_space_while_statement()
    {
        $file = <<<'file'
@while(true)
    <p>I'm looping forever.</p>
@endwhile
file;

        $lints = (new TLint())->lint(
            new SpaceAfterBladeDirectives($file)
        );

        $this->assertEquals(1, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function it_ignores_correctly_spaced_directives()
    {
        $file = <<<'file'
@foreach ($users as $user)
    <li>{{ $user->name }}</li>
@endforeach
file;

        $lints = (new TLint())->lint(
            new SpaceAfterBladeDirectives($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function it_catches_missing_space_kitchen_sink()
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

        $lints = (new TLint())->lint(
            new SpaceAfterBladeDirectives($file)
        );

        $this->assertEquals(1, $lints[0]->getNode()->getLine());
        $this->assertEquals(3, $lints[1]->getNode()->getLine());
        $this->assertEquals(7, $lints[2]->getNode()->getLine());
        $this->assertEquals(7, $lints[3]->getNode()->getLine());
        $this->assertEquals(9, $lints[4]->getNode()->getLine());
        $this->assertEquals(13, $lints[5]->getNode()->getLine());
        $this->assertEquals(17, $lints[6]->getNode()->getLine());
        $this->assertEquals(18, $lints[7]->getNode()->getLine());
        $this->assertEquals(23, $lints[8]->getNode()->getLine());
        $this->assertEquals(29, $lints[9]->getNode()->getLine());
    }
}
