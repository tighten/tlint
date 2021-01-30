<?php

namespace tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\Formatters\ImportFacades;
use Tighten\TFormat;

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
