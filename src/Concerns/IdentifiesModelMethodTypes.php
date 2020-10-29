<?php

namespace Tighten\Concerns;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;

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

    private function isBootingMethod(ClassMethod $stmt)
    {
        return $stmt->isPublic()
            && $stmt->isStatic()
            && $stmt->name == 'booting';
    }

    private function isBootMethod(ClassMethod $stmt)
    {
        return $stmt->isPublic()
            && $stmt->isStatic()
            && $stmt->name == 'boot';
    }

    private function isBootedMethod(ClassMethod $stmt)
    {
        return $stmt->isPublic()
            && $stmt->isStatic()
            && $stmt->name == 'booted';
    }

    private function isCustomStaticMethod(ClassMethod $stmt)
    {
        return $stmt->isStatic();
    }

    private function isCustomMethod(ClassMethod $stmt)
    {
        if ($stmt->isAbstract()) {
            return true;
        }

        if (! $stmt->isPublic()) {
            return true;
        }

        if ($stmt->isStatic()) {
            return true;
        }

        return false;
    }

    private function isRelationshipMethod(ClassMethod $stmt)
    {
        if (! empty($stmt->getParams())) {
            return false;
        }

        /** @see NullableType */
        $returnType = (string) ($stmt->getReturnType()->type ?? $stmt->getReturnType());
        if (in_array(lcfirst($returnType), self::$relationshipMethods)) {
            return true;
        }

        if (empty($stmt->getStmts())) {
            return false;
        }

        $returnStmts = array_filter($stmt->getStmts(), function (Stmt $stmt) {
            return $stmt instanceof Return_;
        });
        $returnStmt = array_shift($returnStmts);

        if (is_null($returnStmt)) {
            return false;
        }

        $expr = $returnStmt->expr ?? null;
        $var = $expr->var ?? null;
        while ($var !== null && property_exists($var, 'var')) {
            $expr = $var;
            $var = $var->var;
        }

        if (
            strval($var->name ?? null) === 'this'
            && in_array($expr->name, self::$relationshipMethods)
        ) {
            return true;
        }

        return false;
    }
}
