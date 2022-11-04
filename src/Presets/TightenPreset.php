<?php

namespace Tighten\TLint\Presets;

use Tighten\TLint\Formatters;
use Tighten\TLint\Linters;

class TightenPreset implements PresetInterface
{
    public function getLinters(): array
    {
        return [
            Linters\ApplyMiddlewareInRoutes::class,
            Linters\ArrayParametersOverViewWith::class,
            Linters\FullyQualifiedFacades::class,
            Linters\MailableMethodsInBuild::class,
            Linters\NoDatesPropertyOnModels::class,
            Linters\NoDocBlocksForMigrationUpDown::class,
            Linters\NoLeadingSlashesOnRoutePaths::class,
            Linters\NoSpaceAfterBladeDirectives::class,
            Linters\OneLineBetweenClassVisibilityChanges::class,
            Linters\QualifiedNamesOnlyForClassName::class,
            Linters\RemoveLeadingSlashNamespaces::class,
            Linters\RequestHelperFunctionWherePossible::class,
            Linters\RequestValidation::class,
            Linters\RestControllersMethodOrder::class,
            Linters\SpaceAfterBladeDirectives::class,
            Linters\SpacesAroundBladeRenderContent::class,
            Linters\UseAuthHelperOverFacade::class,
            Linters\NoJsonDirective::class,
        ];
    }

    public function getFormatters(): array
    {
        return [
            Formatters\FullyQualifiedFacades::class,
            Formatters\NoDocBlocksForMigrationUpDown::class,
        ];
    }
}
