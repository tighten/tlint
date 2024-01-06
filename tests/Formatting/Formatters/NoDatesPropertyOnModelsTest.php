<?php

namespace Tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Formatters\NoDatesPropertyOnModels;
use Tighten\TLint\TFormat;

class NoDatesPropertyOnModelsTest extends TestCase
{
    /** @test */
    public function converts_dates_property_to_datetime_cast()
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
    public function adds_attributes_to_existing_casts()
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

    /**
     * @test
     * This is safe to do because $casts already takes precendence over $dates in Laravel.
     */
    public function drops_date_attributes_already_in_casts()
    {
        $file = <<<'file'
            <?php

            class Page extends Model
            {
                protected $dates = [
                    'email_verified_at',
                ];
                protected $casts = [
                    'email_verified_at' => 'date',
                ];
            }
            file;

        $expected = <<<'file'
            <?php

            class Page extends Model
            {
                protected $casts = [
                    'email_verified_at' => 'date',
                ];
            }
            file;

        $this->assertSame($expected, (new TFormat)->format(new NoDatesPropertyOnModels($file)));
    }

    /** @test */
    public function creates_casts_property_if_it_doesnt_exist()
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
    public function orders_casts_alphabetically()
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
    public function doesnt_error_on_custom_cast_classes()
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

    /** @test */
    public function doesnt_error_on_empty_dates()
    {
        $file = <<<'file'
            <?php

            class User extends Authenticatable
            {
                protected $casts = [
                    'admin' => 'boolean',
                ];
                protected $dates = [];
            }
            file;

        $expected = <<<'file'
            <?php

            class User extends Authenticatable
            {
                protected $casts = [
                    'admin' => 'boolean',
                ];
            }
            file;

        $this->assertSame($expected, (new TFormat)->format(new NoDatesPropertyOnModels($file)));
    }
}
