<?php

namespace Tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Formatters\FullyQualifiedFacades;
use Tighten\TLint\TFormat;

class FullyQualifiedFacadesTest extends TestCase
{
    /** @test */
    public function fixes_facade_aliases()
    {
        $file = <<<'file'
<?php

namespace Test;

use DB;
use Storage;

return true;
file;

        $formatted = (new TFormat)->format(new FullyQualifiedFacades($file));

        $expected = <<<'file'
<?php

namespace Test;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return true;
file;

        $this->assertSame($expected, $formatted);
    }

    /** @test */
    public function fixes_facade_aliases_when_file_not_namespaced()
    {
        $file = <<<'file'
<?php

use DB;
use Storage;

return true;
file;

        $formatted = (new TFormat)->format(new FullyQualifiedFacades($file));

        $expected = <<<'file'
<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return true;
file;

        $this->assertSame($expected, $formatted);
    }

    /** @test */
    public function fixes_facade_aliases_mixed_with_valid_use_statements()
    {
        $file = <<<'file'
<?php

namespace Test;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Storage;
use Tests\TestCase;

return true;
file;

        $formatted = (new TFormat)->format(new FullyQualifiedFacades($file));

        $expected = <<<'file'
<?php

namespace Test;

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
    public function fixes_str_and_arr_helpers()
    {
        $file = <<<'file'
<?php

namespace Test;

use Arr;
use Str;

return true;
file;

        $formatted = (new TFormat)->format(new FullyQualifiedFacades($file));

        $expected = <<<'file'
<?php

namespace Test;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

return true;
file;

        $this->assertSame($expected, $formatted);
    }

    /** @test */
    public function ignores_grouped_imports()
    {
        $file = <<<'file'
<?php

namespace Test;

use App\{ User, Post };

return true;
file;

        $formatted = (new TFormat)->format(new FullyQualifiedFacades($file));

        $this->assertSame($file, $formatted);
    }

    /** @test */
    public function ignores_files_in_same_directory_with_same_name_as_facade_alias()
    {
        $file = <<<'file'
<?php

namespace App\Utilities;

class Stuff
{
    public function doStuff()
    {
        return File::otherStuff();
    }
}
file;

        $formatted = (new TFormat)->format(new FullyQualifiedFacades($file));

        $this->assertSame($file, $formatted);
    }


    /** @test */
    public function ignores_unknown_aliases()
    {
        $file = <<<'file'
<?php

namespace Test;

use Shortcut;

return true;
file;

        $formatted = (new TFormat)->format(new FullyQualifiedFacades($file));

        $this->assertSame($file, $formatted);
    }
}
