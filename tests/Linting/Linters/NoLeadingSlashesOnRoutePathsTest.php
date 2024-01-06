<?php

namespace Tests\Linting\Linters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Linters\NoLeadingSlashesOnRoutePaths;
use Tighten\TLint\TLint;

class NoLeadingSlashesOnRoutePathsTest extends TestCase
{
    /** @test */
    public function catches_leading_slashes_on_top_level_routes()
    {
        $file = <<<'file'
            <?php

            Route::get('/home', function () {
                return '';
            });
            file;

        $lints = (new TLint)->lint(
            new NoLeadingSlashesOnRoutePaths($file)
        );

        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function catches_leading_slashes_in_route_groups()
    {
        $file = <<<'file'
            <?php

            Route::group(['middleware' => 'auth'], function () {
                Route::get('/home', function ()    {
                    // Uses Auth Middleware
                });
            });
            file;

        $lints = (new TLint)->lint(
            new NoLeadingSlashesOnRoutePaths($file)
        );

        $this->assertEquals(4, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function does_not_trigger_on_otherwise_empty_paths()
    {
        $file = <<<'file'
            <?php

            Route::get('/', function () {
                return '';
            });
            file;

        $lints = (new TLint)->lint(
            new NoLeadingSlashesOnRoutePaths($file)
        );

        $this->assertEmpty($lints);
    }

    /** @test */
    public function does_not_throw_on_dynamic_calls()
    {
        $file = <<<'file'
            <?php

            Route::get('/', function ($class) {
                return  $class::test();
            });
            file;

        $lints = (new TLint)->lint(
            new NoLeadingSlashesOnRoutePaths($file)
        );

        $this->assertEmpty($lints);
    }
}
