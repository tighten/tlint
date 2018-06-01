<?php

namespace Tighten\Concerns;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;

trait IdentifiesModelMethodTypes
{
    private static $relationshipMethods = [
        'hasOne',
        'belongsTo',
        'hasMany',
        'belongsToMany',
        'hasManyThrough',
        'morphTo',
        'morphMany',
        'morphToMany',
        'morphedByMany',
    ];

    private function getFirstCall(Node $node)
    {
        if (property_exists($node, 'expr')
            && property_exists($node->expr, 'name')
            && ! in_array($node->expr->name, self::$relationshipMethods)
        ) {
            return $this->getFirstCall($node->expr);
        }

        if (property_exists($node, 'var')
            && property_exists($node->var, 'name')
            && ! in_array($node->var->name, self::$relationshipMethods)
        ) {
            return $this->getFirstCall($node->var);
        }

        if (property_exists($node, 'expr')) {
            return $node->expr;
        }

        if (property_exists($node, 'var')) {
            return $node->var;
        }

        return $node;
    }

    private function isScopeMethod(ClassMethod $stmt)
    {
        return strpos($stmt->name, 'scope') === 0;
    }

    private function isAccessorMethod(ClassMethod $stmt)
    {
        return strpos($stmt->name, 'get') === 0
            && strpos($stmt->name, 'Attribute') === strlen($stmt->name) - 9;
    }

    private function isMutatorMethod(ClassMethod $stmt)
    {
        return strpos($stmt->name, 'set') === 0
            && strpos($stmt->name, 'Attribute') === strlen($stmt->name) - 9;
    }

    private function isBootMethod(ClassMethod $stmt)
    {
        return $stmt->name === 'boot';
    }
}
