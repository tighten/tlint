<?php

namespace Tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Formatters\NoLeadingSlashesOnRoutePaths;
use Tighten\TLint\TFormat;

class NoLeadingSlashesOnRoutePathsTest extends TestCase
{
    /** @test */
    public function catches_leading_slashes_on_top_level_routes()
    {
        $file = <<<file
<?php

Route::get('/home', function () {
    return '';
});
file;

        $correctlyFormatted = <<<'file'
<?php

Route::get('home', function () {
    return '';
});
file;

        $formatted = (new TFormat)->format(
            new NoLeadingSlashesOnRoutePaths($file, '.php')
        );

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function catches_leading_slashes_in_route_groups()
    {
        $file = <<<file
<?php

Route::group(['middleware' => 'auth'], function () {
    Route::get('/home', function () {
        // Uses Auth Middleware
    });
});
file;

        $correctlyFormatted = <<<'file'
<?php

Route::group(['middleware' => 'auth'], function () {
    Route::get('home', function () {
        // Uses Auth Middleware
    });
});
file;

        $formatted = (new TFormat)->format(
            new NoLeadingSlashesOnRoutePaths($file, '.php')
        );

        $this->assertEquals($correctlyFormatted, $formatted);
    }

    /** @test */
    public function does_not_trigger_on_otherwise_empty_paths()
    {
        $file = <<<file
<?php

Route::get('/', function () {
    return '';
});
file;

        $formatted = (new TFormat)->format(
            new NoLeadingSlashesOnRoutePaths($file, '.php')
        );

        $this->assertEquals($file, $formatted);
    }

    /** @test */
    public function does_not_throw_on_dynamic_calls()
    {
        $file = <<<file
<?php

Route::get('/', function (\$class) {
    return  \$class::test();
});
file;

        $formatted = (new TFormat)->format(
            new NoLeadingSlashesOnRoutePaths($file, '.php')
        );

        $this->assertEquals($file, $formatted);
    }
}
