<?php

namespace tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Formatters\ImportFacades;
use Tighten\TLint\TFormat;

class ImportFacadesTest extends TestCase
{
    /** @test */
    function fixes_facade_aliases()
    {
        $file = <<<'file'
<?php

use DB;
use Storage;

return true;
file;

        $formatted = (new TFormat)->format(new ImportFacades($file));

        $expected = <<<'file'
<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return true;
file;

        $this->assertSame($expected, $formatted);
    }

    /** @test */
    function fixes_facade_aliases_mixed_with_valid_use_statements()
    {
        $file = <<<'file'
<?php

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Storage;
use Tests\TestCase;

return true;
file;

        $formatted = (new TFormat)->format(new ImportFacades($file));

        $expected = <<<'file'
<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

return true;
file;

        $this->assertSame($expected, $formatted);
    }

    /** @test */
    function fixes_str_and_arr_helpers()
    {
        $file = <<<'file'
<?php

use Arr;
use Str;

return true;
file;

        $formatted = (new TFormat)->format(new ImportFacades($file));

        $expected = <<<'file'
<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

return true;
file;

        $this->assertSame($expected, $formatted);
    }

    /** @test */
    function ignores_grouped_imports()
    {
        $file = <<<'file'
<?php

use App\{ User, Post };

return true;
file;

        $formatted = (new TFormat)->format(new ImportFacades($file));

        $this->assertSame($file, $formatted);
    }

    /** @test */
    function ignores_unknown_aliases()
    {
        $file = <<<'file'
<?php

use Shortcut;

return true;
file;

        $formatted = (new TFormat)->format(new ImportFacades($file));

        $this->assertSame($file, $formatted);
    }
}
