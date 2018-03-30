<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\ImportFacades;
use Tighten\Linters\NoLeadingSlashesOnRoutePaths;
use Tighten\TLint;

class NoLeadingSlashesOnRoutePathsTest extends TestCase
{
    /** @test */
    public function catches_leading_slashes_on_top_level_routes()
    {
        $file = <<<file
<?php

Route::get('/', function () {
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
        $file = <<<file
<?php

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', function ()    {
        // Uses Auth Middleware
    });

    Route::get('user/profile', function () {
        // Uses Auth Middleware
    });
});
file;

        $lints = (new TLint)->lint(
            new NoLeadingSlashesOnRoutePaths($file)
        );

        $this->assertEquals(4, $lints[0]->getNode()->getLine());
    }
}
