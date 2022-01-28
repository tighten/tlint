<?php

namespace Tighten\TLint;

use Closure;
use LogicException;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\Parser;

class BaseLinter
{
    public const DESCRIPTION = 'No Description for Linter.';

    protected $description;
    protected $filename;
    protected $code;
    protected $codeLines;

    public function __construct($code, $filename = null)
    {
        $this->description = static::DESCRIPTION;
        $this->filename = $filename;
        $this->code = $code;
        $this->codeLines = preg_split('/\r\n|\r|\n/', $code);
    }

    public static function appliesToPath(string $path): bool
    {
        return true;
    }

    public function lint(Parser $parser)
    {
        return $this->traverseAutomatically($parser);
    }

    public function getLintDescription()
    {
        return $this->description;
    }

    public function setLintDescription(string $description)
    {
        return $this->description = $description;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getCodeLine(int $line)
    {
        return $this->getCodeLines()[$line - 1];
    }

    public function getCodeLinesFromNode(Node $node)
    {
        return array_reduce(
            range($node->getStartLine(), $node->getEndLine()),
            function ($carry, $line) {
                return $carry . $this->getCodeLine($line);
            },
            ''
        );
    }

    public function getCodeLines()
    {
        return $this->codeLines;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    protected function visitor(): Closure
    {
        throw new LogicException('Custom linters must override either the `lint` or `visitor` method.');
    }

    protected function visitUsing(Parser $parser, Closure $callback): FindingVisitor
    {
        $traverser = new NodeTraverser;
        $traverser->addVisitor($visitor = new FindingVisitor($callback));
        $traverser->traverse($parser->parse($this->code));

        return $visitor;
    }

    private function traverseAutomatically(Parser $parser): array
    {
        return $this->visitUsing($parser, $this->visitor())->getFoundNodes();
    }
}
