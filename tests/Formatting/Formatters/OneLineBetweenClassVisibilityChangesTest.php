<?php

namespace Tests\Formatting\Formatters;

use PHPUnit\Framework\TestCase;
use Tighten\TLint\Formatters\OneLineBetweenClassVisibilityChanges;
use Tighten\TLint\TFormat;

class OneLineBetweenClassVisibilityChangesTest extends TestCase
{
    /** @test */
    public function catches_missing_line_between_visibility_changes()
    {
        $file = <<<'file'
            <?php

            namespace App;

            class Thing
            {
                protected const OK = 1;
                protected const DOKEY = 1;
                protected $bar;
                private $ok;
                private $foo;
            }
            file;

        $expected = <<<'file'
            <?php

            namespace App;

            class Thing
            {
                protected const OK = 1;
                protected const DOKEY = 1;

                protected $bar;

                private $ok;
                private $foo;
            }
            file;

        $formatted = (new TFormat)->format(new OneLineBetweenClassVisibilityChanges($file));

        $this->assertSame($expected, $formatted);
    }

    /** @test */
    public function catches_missing_line_between_visibility_changes_with_doc_block()
    {
        $file = <<<'file'
            <?php

            namespace App;

            class Thing
            {
                protected const OK = 1;
                /**
                 * The description of something.
                 */
                private $ok;
                private $dokey;
            }
            file;

        $expected = <<<'file'
            <?php

            namespace App;

            class Thing
            {
                protected const OK = 1;

                /**
                 * The description of something.
                 */
                private $ok;
                private $dokey;
            }
            file;

        $formatted = (new TFormat)->format(new OneLineBetweenClassVisibilityChanges($file));

        $this->assertSame($expected, $formatted);
    }

    /** @test */
    public function ignores_doc_block_between_visibility_changes()
    {
        $file = <<<'file'
            <?php

            namespace App;

            class Thing
            {
                protected const OK = 1;

                /**
                 * The description of something.
                 */
                private $ok;
                private $dokey;
            }
            file;

        $formatted = (new TFormat)->format(new OneLineBetweenClassVisibilityChanges($file));

        $this->assertSame($file, $formatted);
    }

    /** @test */
    public function catches_missing_line_between_visibility_changes_with_comment()
    {
        $file = <<<'file'
            <?php

            namespace App;

            class Thing
            {
                protected const OK = 1;
                // Note to self
                private $ok;
                private $dokey;
            }
            file;

        $expected = <<<'file'
            <?php

            namespace App;

            class Thing
            {
                protected const OK = 1;

                // Note to self
                private $ok;
                private $dokey;
            }
            file;

        $formatted = (new TFormat)->format(new OneLineBetweenClassVisibilityChanges($file));

        $this->assertSame($expected, $formatted);
    }

    /** @test */
    public function catches_missing_line_between_visibility_changes_with_two_comments()
    {
        $file = <<<'file'
            <?php

            namespace App;

            class Thing
            {
                protected const OK = 1;
                // Note to self
                // Another
                private $ok;
                private $dokey;
            }
            file;

        $expected = <<<'file'
            <?php

            namespace App;

            class Thing
            {
                protected const OK = 1;

                // Note to self
                // Another
                private $ok;
                private $dokey;
            }
            file;

        $formatted = (new TFormat)->format(new OneLineBetweenClassVisibilityChanges($file));

        $this->assertSame($expected, $formatted);
    }

    /** @test */
    public function catches_missing_line_between_visibility_changes_with_many_comments()
    {
        $file = <<<'file'
            <?php

            namespace App;

            class Thing
            {
                protected const OK = 1;
                // Note to self
                // Another
                // And another
                // And another one
                private $ok;
                private $dokey;
            }
            file;

        $expected = <<<'file'
            <?php

            namespace App;

            class Thing
            {
                protected const OK = 1;

                // Note to self
                // Another
                // And another
                // And another one
                private $ok;
                private $dokey;
            }
            file;

        $formatted = (new TFormat)->format(new OneLineBetweenClassVisibilityChanges($file));

        $this->assertSame($expected, $formatted);
    }

    /** @test */
    public function ignores_comment_below_space_between_visibility_changes()
    {
        $file = <<<'file'
            <?php

            namespace App;

            class Thing
            {
                protected const OK = 1;

                // TODO
                private $ok;
                private $dokey;
            }
            file;

        $formatted = (new TFormat)->format(new OneLineBetweenClassVisibilityChanges($file));

        $this->assertSame($file, $formatted);
    }

    /** @test */
    public function ignores_comment_above_space_between_visibility_changes()
    {
        $file = <<<'file'
            <?php

            namespace App;

            class Thing
            {
                protected const OK = 1;
                // public const NOT_OK = 2;

                private $ok;
                private $dokey;
            }
            file;

        $formatted = (new TFormat)->format(new OneLineBetweenClassVisibilityChanges($file));

        $this->assertSame($file, $formatted);
    }

    /** @test */
    public function ignores_many_comments_between_visibility_changes()
    {
        $file = <<<'file'
            <?php

            namespace App;

            class Thing
            {
                protected const OK = 1;
                // public const NOT_OK = 2;

                // another one
                /**
                 * docblock!
                 */
                // Hi there
                //
                private $ok;
                private $dokey;
            }
            file;

        $formatted = (new TFormat)->format(new OneLineBetweenClassVisibilityChanges($file));

        $this->assertSame($file, $formatted);
    }

    /** @test */
    public function catches_missing_line_between_visibility_changes_in_anon_class()
    {
        $file = <<<'file'
            <?php

            namespace App;

            class Thing
            {
                public $test;

                public function getThing(): NodeVisitorAbstract
                {
                    return new class extends NodeVisitorAbstract
                    {
                        protected const OK = 1;
                        private $ok;
                        private $dokey;
                    };
                }
            }
            file;

        $expected = <<<'file'
            <?php

            namespace App;

            class Thing
            {
                public $test;

                public function getThing(): NodeVisitorAbstract
                {
                    return new class extends NodeVisitorAbstract
                    {
                        protected const OK = 1;

                        private $ok;
                        private $dokey;
                    };
                }
            }
            file;

        $formatted = (new TFormat)->format(new OneLineBetweenClassVisibilityChanges($file));

        $this->assertSame($expected, $formatted);
    }

    /** @test */
    public function catches_missing_line_between_types()
    {
        $file = <<<'file'
            <?php

            namespace App;

            class Thing
            {
                public const FOO = 'bar';
                public $bar = 'baz';
                protected $foo = 'bar';
                protected $bar = 'baz';
            }
            file;

        $expected = <<<'file'
            <?php

            namespace App;

            class Thing
            {
                public const FOO = 'bar';

                public $bar = 'baz';

                protected $foo = 'bar';
                protected $bar = 'baz';
            }
            file;

        $formatted = (new TFormat)->format(new OneLineBetweenClassVisibilityChanges($file));

        $this->assertSame($expected, $formatted);
    }
}
