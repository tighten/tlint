<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\AlphabeticalImports;
use Tighten\Linters\ImportFacades;
use Tighten\TLint;

class AlphabeticalImportsTest extends TestCase
{
    /** @test */
    public function catches_non_alphabetical_imports()
    {
        $file = <<<file
<?php

use B\A as AA;
use A\Z\Z;

\$ok = 'thing';
file;

        $lints = (new TLint)->lint(
            new AlphabeticalImports($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function handles_duplicate_base_imports()
    {
        $file = <<<file
<?php

namespace Tests\Unit;

use App\J\M;
use App\J\M;
use App\J\V;
use App\J\V;
use App\J\V;
use App\J\V;
use D\J\J;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\stream_for;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\App;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

\$ok = 'thing';
file;

        $lints = (new TLint)->lint(
            new AlphabeticalImports($file)
        );

        $this->assertEmpty($lints);
    }
}
