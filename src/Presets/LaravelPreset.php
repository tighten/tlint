<?php

namespace Tighten\Presets;
 
use Tighten\Linters\ApplyMiddlewareInRoutes;
use Tighten\Linters\ArrayParametersOverViewWith;
use Tighten\Linters\ClassThingsOrder;
use Tighten\Linters\ImportFacades;
use Tighten\Linters\MailableMethodsInBuild;
use Tighten\Linters\ModelMethodOrder;
use Tighten\Linters\NewLineAtEndOfFile;
use Tighten\Linters\NoCompact;
use Tighten\Linters\NoDd;
use Tighten\Linters\NoLeadingSlashesOnRoutePaths;
use Tighten\Linters\NoParensEmptyInstantiations;
use Tighten\Linters\NoSpaceAfterBladeDirectives;
use Tighten\Linters\NoStringInterpolationWithoutBraces;
use Tighten\Linters\OneLineBetweenClassVisibilityChanges;
use Tighten\Linters\PureRestControllers;
use Tighten\Linters\QualifiedNamesOnlyForClassName;
use Tighten\Linters\RemoveLeadingSlashNamespaces;
use Tighten\Linters\RequestHelperFunctionWherePossible;
use Tighten\Linters\RequestValidation;
use Tighten\Linters\RestControllersMethodOrder;
use Tighten\Linters\SpaceAfterBladeDirectives;
use Tighten\Linters\SpaceAfterSoleNotOperator;
use Tighten\Linters\SpacesAroundBladeRenderContent;
use Tighten\Linters\TrailingCommasOnArrays;
use Tighten\Linters\UseAuthHelperOverFacade;
use Tighten\Linters\UseConfigOverEnv;

class LaravelPreset implements PresetInterface
{
    public function getLinters() : array
    {
        return [
            ApplyMiddlewareInRoutes::class,
            ArrayParametersOverViewWith::class,
            ClassThingsOrder::class,
            ImportFacades::class,
            MailableMethodsInBuild::class,
            ModelMethodOrder::class,
            NewLineAtEndOfFile::class,
            NoCompact::class,
            NoDd::class,
            NoLeadingSlashesOnRoutePaths::class,
            NoParensEmptyInstantiations::class,
            NoSpaceAfterBladeDirectives::class,
            NoStringInterpolationWithoutBraces::class,
            OneLineBetweenClassVisibilityChanges::class,
            PureRestControllers::class,
            QualifiedNamesOnlyForClassName::class,
            RemoveLeadingSlashNamespaces::class,
            RequestHelperFunctionWherePossible::class,
            RequestValidation::class,
            RestControllersMethodOrder::class,
            SpaceAfterBladeDirectives::class,
            SpaceAfterSoleNotOperator::class,
            SpacesAroundBladeRenderContent::class,
            TrailingCommasOnArrays::class,
            UseAuthHelperOverFacade::class,
            UseConfigOverEnv::class,
        ];
    }

    public function getFormatters() : array
    {
        return [];
    }
}
