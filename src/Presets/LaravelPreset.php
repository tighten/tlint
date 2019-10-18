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
            'ImportFacades',
            'MailableMethodsInBuild',
            'ModelMethodOrder',
            'NewLineAtEndOfFile',
            'NoCompact',
            'NoDd',
            'NoLeadingSlashesOnRoutePaths',
            'NoParensEmptyInstantiations',
            'NoSpaceAfterBladeDirectives',
            'NoStringInterpolationWithoutBraces',
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
