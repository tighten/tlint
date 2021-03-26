<?php

namespace Tighten\Concerns;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\TraitUse;

trait IdentifiesClassThings
{
    private function isTraitUse(Stmt $stmt)
    {
        return $stmt instanceof TraitUse;
    }

    private function isPublicStaticProperty(Stmt $stmt)
    {
        return $stmt instanceof Property && $stmt->isStatic() && $stmt->isPublic();
    }

    private function isProtectedStaticProperty(Stmt $stmt)
    {
        return $stmt instanceof Property && $stmt->isStatic() && $stmt->isProtected();
    }

    private function isPrivateStaticProperty(Stmt $stmt)
    {
        return $stmt instanceof Property && $stmt->isStatic() && $stmt->isPrivate();
    }

    private function isPublicConstant(Stmt $stmt)
    {
        return $stmt instanceof ClassConst && $stmt->isPublic();
    }

    private function isProtectedConstant(Stmt $stmt)
    {
        return $stmt instanceof ClassConst && $stmt->isProtected();
    }

    private function isPrivateConstant(Stmt $stmt)
    {
        return $stmt instanceof ClassConst && $stmt->isPrivate();
    }

    private function isPublicProperty(Stmt $stmt)
    {
        return $stmt instanceof Property && $stmt->isPublic();
    }

    private function isProtectedProperty(Stmt $stmt)
    {
        return $stmt instanceof Property && $stmt->isProtected();
    }

    private function isPrivateProperty(Stmt $stmt)
    {
        return $stmt instanceof Property && $stmt->isPrivate();
    }

    private function isConstructor(Stmt $stmt)
    {
        return $stmt instanceof ClassMethod && ($stmt->name->name ?? null) === '__construct';
    }

    private function isPublicStaticMethod(Stmt $stmt)
    {
        return $stmt instanceof ClassMethod && $stmt->isPublic() && $stmt->isStatic();
    }

    private function isProtectedStaticMethod(Stmt $stmt)
    {
        return $stmt instanceof ClassMethod && $stmt->isProtected() && $stmt->isStatic();
    }

    private function isPrivateStaticMethod(Stmt $stmt)
    {
        return $stmt instanceof ClassMethod && $stmt->isPrivate() && $stmt->isStatic();
    }

    private function isPublicMethod(Stmt $stmt)
    {
        return $stmt instanceof ClassMethod && $stmt->isPublic() && ! $stmt->isStatic() && ! $stmt->isMagic();
    }

    private function isProtectedMethod(Stmt $stmt)
    {
        return $stmt instanceof ClassMethod && $stmt->isProtected() && ! $stmt->isStatic();
    }

    private function isPrivateMethod(Stmt $stmt)
    {
        return $stmt instanceof ClassMethod && $stmt->isPrivate() && ! $stmt->isStatic();
    }

    private function isMagicMethod(Stmt $stmt)
    {
        return $stmt instanceof ClassMethod && strpos($stmt->name, '__') === 0;
    }
}
