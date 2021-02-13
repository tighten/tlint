<?php

namespace tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\Formatters\NoDatesPropertyOnModels;
use Tighten\TFormat;

class NoDatesPropertyOnModelsTest extends TestCase
{
    /** @test */
    function fixes_dates_property_by_converting_to_datetime_cast()
    {
        $file = <<<'file'
<?php

class Post extends Model
{
    protected $dates = ['email_verified_at'];

    protected $casts = [];
}
file;

        $expected = <<<'file'
<?php

class Post extends Model
{
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
file;

        $this->assertSame($expected, (new TFormat)->format(new NoDatesPropertyOnModels($file)));
    }

    /** @test */
    function can_add_cast_to_list_of_existing_attributes()
    {
        $file = <<<'file'
<?php

class Post extends Model
{
    protected $dates = [
        'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
file;

        $expected = <<<'file'
<?php

class Post extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];
}
file;

        $this->assertSame($expected, (new TFormat)->format(new NoDatesPropertyOnModels($file)));
    }

    /** @test */
    function removes_attributes_present_in_both_dates_and_casts()
    {
        // $casts already takes precendence over $dates

        $file = <<<'file'
<?php

class Page extends Model
{
    protected $dates = [
        'email_verified_at',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
file;

        $expected = <<<'file'
<?php

class Page extends Model
{
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
file;

        $this->assertSame($expected, (new TFormat)->format(new NoDatesPropertyOnModels($file)));
    }

    /** @test */
    function creates_casts_property_if_it_does_not_exist()
    {
        $file = <<<'file'
<?php

class User extends Authenticatable
{
    protected $dates = ['signed_up_at'];
}
file;

        $expected = <<<'file'
<?php

class User extends Authenticatable
{
    protected $casts = [
        'signed_up_at' => 'datetime',
    ];
}
file;

        $this->assertSame($expected, (new TFormat)->format(new NoDatesPropertyOnModels($file)));
    }

    /** @test */
    function orders_fixed_dates_property_attributes_alphabetically()
    {
        $file = <<<'file'
<?php

class User extends Authenticatable
{
    protected $dates = ['signed_up_at', 'email_verified_at'];
}
file;

        $expected = <<<'file'
<?php

class User extends Authenticatable
{
    protected $casts = [
        'email_verified_at' => 'datetime',
        'signed_up_at' => 'datetime',
    ];
}
file;

        $this->assertSame($expected, (new TFormat)->format(new NoDatesPropertyOnModels($file)));
    }

    /** @test */
    function doesnt_error_on_custom_cast_classes()
    {
        $file = <<<'file'
<?php

use App\Casts\Password;

class User extends Authenticatable
{
    protected $dates = ['email_verified_at'];
    protected $casts = [
        'password' => Password::class,
    ];
}
file;

        $expected = <<<'file'
<?php

use App\Casts\Password;

class User extends Authenticatable
{
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => Password::class,
    ];
}
file;

        $this->assertSame($expected, (new TFormat)->format(new NoDatesPropertyOnModels($file)));
    }
}
