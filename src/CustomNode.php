<?php

namespace Tighten\TLint;

use PhpParser\NodeAbstract;

class CustomNode extends NodeAbstract
{
    public function getSubNodeNames(): array
    {
        return [];
    }

    public function getType(): string
    {
        return '';
    }
}
