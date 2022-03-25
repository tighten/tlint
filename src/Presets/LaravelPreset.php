<?php

namespace Tighten\TLint\Presets;

use Tighten\TLint\Linters;

class LaravelPreset implements PresetInterface
{
    public function getLinters(): array
    {
        return [
            Linters\ApplyMiddlewareInRoutes::class,
            Linters\ArrayParametersOverViewWith::class,
            Linters\MailableMethodsInBuild::class,
            Linters\NoDatesPropertyOnModels::class,
            Linters\NoLeadingSlashesOnRoutePaths::class,
            Linters\NoParensEmptyInstantiations::class,
            Linters\NoSpaceAfterBladeDirectives::class,
            Linters\OneLineBetweenClassVisibilityChanges::class,
            Linters\QualifiedNamesOnlyForClassName::class,
            Linters\RemoveLeadingSlashNamespaces::class,
            Linters\RequestHelperFunctionWherePossible::class,
            Linters\RequestValidation::class,
            Linters\RestControllersMethodOrder::class,
            Linters\SpaceAfterBladeDirectives::class,
            Linters\SpaceAfterSoleNotOperator::class,
            Linters\SpacesAroundBladeRenderContent::class,
            Linters\TrailingCommasOnArrays::class,
            Linters\UseAuthHelperOverFacade::class,
            Linters\UseConfigOverEnv::class,
        ];
    }

    public function getFormatters(): array
    {
        return [];
    }
}
