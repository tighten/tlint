<?php

namespace Tighten\TLint\Formatters;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Tighten\TLint\BaseFormatter;
use Tighten\TLint\Concerns\IdentifiesFacades;

class FullyQualifiedFacades extends BaseFormatter
{
    use IdentifiesFacades;

    public const DESCRIPTION = 'Import facades using their full namespace.';

    public function format(Parser $parser): string
    {
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new CloningVisitor);

        $oldStmts = $parser->parse($this->code);
        $oldTokens = $parser->getTokens();

        $newStmts = $traverser->traverse($oldStmts);

        $traverser = new NodeTraverser;
        $traverser->addVisitor($currentFullyQualifiedFacadesVisitor = $this->currentFullyQualifiedFacadesVisitor());
        $traverser->traverse($newStmts);

        $traverser = new NodeTraverser;
        $traverser->addVisitor($this->removeDuplicatesVisitor(
            $currentFullyQualifiedFacadesVisitor->getCurrentFullyQualifiedFacades()
        ));
        $newStmts = $traverser->traverse($newStmts);

        $traverser = new NodeTraverser;
        $traverser->addVisitor($this->transformFacadesVisitor());
        $newStmts = $traverser->traverse($newStmts);

        return preg_replace('/\r?\n/', PHP_EOL, (new Standard)->printFormatPreserving($newStmts, $oldStmts, $oldTokens));
    }

    private function currentFullyQualifiedFacadesVisitor(): NodeVisitorAbstract
    {
        return new class extends NodeVisitorAbstract
        {
            public $currentFullyQualifiedFacades = [];

            public function getCurrentFullyQualifiedFacades()
            {
                return $this->currentFullyQualifiedFacades;
            }

            public function leaveNode(Node $node)
            {
                if ($node instanceof Use_
                    && in_array((string) $node->uses[0]->name, FullyQualifiedFacades::$aliases)
                ) {
                    $this->currentFullyQualifiedFacades[] = (string) $node->uses[0]->name;
                }
            }
        };
    }

    private function removeDuplicatesVisitor(array $currentFullyQualifiedFacades): NodeVisitorAbstract
    {
        return new class($currentFullyQualifiedFacades) extends NodeVisitorAbstract
        {
            private $currentFullyQualifiedFacades;

            public function __construct(array $currentFullyQualifiedFacades)
            {
                $this->currentFullyQualifiedFacades = $currentFullyQualifiedFacades;
            }

            public function leaveNode(Node $node)
            {
                if (! $node instanceof Use_) {
                    return null;
                }

                $qualifiedUseStatement = FullyQualifiedFacades::$aliases[(string) $node->uses[0]->name] ?? null;

                if (in_array($qualifiedUseStatement, $this->currentFullyQualifiedFacades)) {
                    return NodeTraverser::REMOVE_NODE;
                }
            }
        };
    }

    private function transformFacadesVisitor(): NodeVisitorAbstract
    {
        return new class extends NodeVisitorAbstract
        {
            public function enterNode(Node $node): Node|int|null
            {
                if (! $node instanceof Use_) {
                    return null;
                }

                $facades = FullyQualifiedFacades::$aliases;
                $useStatement = (string) $node->uses[0]->name;

                if (! array_key_exists($useStatement, $facades)) {
                    return null;
                }

                return new Use_([new UseItem(new Name($facades[$useStatement]))]);
            }
        };
    }
}
