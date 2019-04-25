<?php

namespace Tighten\Presets;

class LaravelPreset implements PresetInterface
{
    public function getLinters() : array
    {
        return [
            'ApplyMiddlewareInRoutes',
            'ArrayParametersOverViewWith',
            'ClassThingsOrder',
            'ConcatenationSpacing',
            'ImportFacades',
            'MailableMethodsInBuild',
            'ModelMethodOrder',
            'NewLineAtEndOfFile',
            'NoCompact',
            'NoDd',
            'NoDocBlocksForMigrationUpDown',
            'NoInlineVarDocs',
            'NoLeadingSlashesOnRoutePaths',
            'NoMethodVisibilityInTests',
            'NoParensEmptyInstantiations',
            'NoSpaceAfterBladeDirectives',
            'NoStringInterpolationWithoutBraces',
            'NoUnusedImports',
            'OneLineBetweenClassVisibilityChanges',
            'PureRestControllers',
            'QualifiedNamesOnlyForClassName',
            'RemoveLeadingSlashNamespaces',
            'RequestHelperFunctionWherePossible',
            'RequestValidation',
            'RestControllersMethodOrder',
            'SpaceAfterBladeDirectives',
            'SpaceAfterSoleNotOperator',
            'SpacesAroundBladeRenderContent',
            'TrailingCommasOnArrays',
            'UseAuthHelperOverFacade',
            'UseConfigOverEnv',
        ];
    }
}
