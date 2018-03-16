<?php

use PHPUnit\Framework\TestCase;
use Tighten\Linters\NoStringInterpolationWithoutBraces;
use Tighten\Linters\PureRestControllers;
use Tighten\TLint;

class NoStringInterpolationWithoutBracesTest extends TestCase
{
    /** @test */
    public function catches_string_interpolation_without_braces()
    {
        $file = <<<file
<?php

\$a = 1;

\$next = "\$a";
file;

        $lints = (new TLint)->lint(
            new NoStringInterpolationWithoutBraces($file)
        );

        $this->assertEquals(5, $lints[0]->getNode()->getLine());
    }

    /** @test */
    public function it_does_not_trigger_on_string_interpolation_with_braces()
    {
        $file = <<<file
<?php

\$a = 1;

\$next = "{\$a}";
file;

        $lints = (new TLint)->lint(
            new NoStringInterpolationWithoutBraces($file)
        );

        $this->assertEmpty($lints);
    }
    
    /** @test */
    public function does_not_trigger_for_object_properties_with_braces()
    {
        $file = <<<file
<?php

\$next = "{\$a->b}";
file;
        
        $lints = (new TLint)->lint(
            new NoStringInterpolationWithoutBraces($file)
        );
        
        $this->assertEmpty($lints);
    }
    
    /** @test */
    public function does_trigger_for_object_properties_without_braces()
    {
        $file = <<<file
<?php

\$next = "\$a->b";
file;
        
        $lints = (new TLint)->lint(
            new NoStringInterpolationWithoutBraces($file)
        );
    
        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }
    
    /** @test */
    public function does_trigger_for_nested_object_properties_without_braces()
    {
        $file = <<<file
<?php

\$next = "\$a->b->c";
file;
        
        $lints = (new TLint)->lint(
            new NoStringInterpolationWithoutBraces($file)
        );
        
        $this->assertEquals(3, $lints[0]->getNode()->getLine());
    }
    
    /**
     * @test
     * @group foo
     */
    public function does_not_trigger_for_nested_object_properties_with_braces()
    {
        $file = <<<file
<?php

\$next = "{\$a->b->c->d->e}";
file;
        
        $lints = (new TLint)->lint(
            new NoStringInterpolationWithoutBraces($file)
        );
        
        $this->assertEmpty($lints);
    }
}
