<?php

namespace Tighten\Presets;

class TightenPreset implements PresetInterface
{
    public function getLinters() : array
    {
        return [
            'AlphabeticalImports',
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
            'NoJsonDirective',
        ];
    }
}
