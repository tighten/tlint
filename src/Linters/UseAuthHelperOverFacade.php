<?php

namespace Tighten\Linters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;
use Tighten\BaseLinter;
use Tighten\Illuminate\BladeCompiler;
use Tighten\Linters\Concerns\LintsBladeTemplates;

class UseAuthHelperOverFacade extends BaseLinter
{
    use LintsBladeTemplates;

    public const description = 'Prefer the `auth()` helper function over the `Auth` Facade.';

    public function __construct($code, $filename = null)
    {
        if (preg_match('/\.blade\.php$/i', $filename)) {
            $bladeCompiler = new BladeCompiler(null, sys_get_temp_dir());
            $code = $bladeCompiler->compileString($code);
        }

        parent::__construct($code, $filename);
    }

    public function lint(Parser $parser)
    {
        $traverser = new NodeTraverser;

        $visitor = new FindingVisitor(function (Node $node) {
            static $usesAuthFacade = false;

            if ($node instanceof Node\Stmt\UseUse && $node->name instanceof Node\Name) {
                if ($node->name->toString() === 'Illuminate\Support\Facades\Auth') {
                    $usesAuthFacade = true;
                }
            }

            return $node instanceof Node\Expr\StaticCall
                // use Illuminate\Support\Facades\Auth and calling Auth::
                && (($usesAuthFacade && $node->class instanceof Node\Name && $node->class->toString() === 'Auth')
                    // FQCN usage
                    || (
                        $node->class instanceof Node\Name
                        && $node->class->toString() === 'Illuminate\Support\Facades\Auth'
                    ))
                && ($node->class instanceof Node\Name && $node->name->name !== 'routes');
        });

        $traverser->addVisitor($visitor);

        $traverser->traverse($parser->parse($this->code));

        return $visitor->getFoundNodes();
    }
}
