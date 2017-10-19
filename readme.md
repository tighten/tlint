## Tighten linter
> not psr stuff, these are conventions agreed upon by Tighten developers
> intended for use alongside default php-cs-fixer (to cover aforementioned psr stuff)

## Install (Requires php7.1+)
```
composer global require loganhenson/tlint
```
> If you have other globally installed core laravel files you may need to update them to test this package.
> - laravel/valet

## Usage
For entire project (you must pass the lint command to use other options)
```
tlint
```
For individual files and specific directories
```
tlint lint routes/ViewWithOverArrayParamatersExample.php
```

You can also lint only diff files by
```
tlint lint --diff
```
OR
```
tlint lint src --diff
```

> output
```
Linting TestLaravelApp/routes/ViewWithOverArrayParamatersExample.php
============
Lints: 
============
! Prefer `view(...)->with(...)` over `view(..., [...])`.
5 : `    return view('test', ['test' => 'test']);``
```

## Lint this project
```
./bin/tlint
```

## Implemented lints
- Use with over array parameters in view(). `ViewWithOverArrayParamaters`
- No leading slashes in namespaces or static calls or instantiations. `RemoveLeadingSlashNamespaces`
- Fully qualified class name only when it's being used a string (class name). `QualifiedNamesOnlyForClassName`
- Blade directive spacing conventions. `NoSpaceAfterBladeDirectives`, `SpaceAfterBladeDirectives`
- Don’t use environment variables directly in code; instead, use them in config files and call config vars from code. `UseConfigOverEnv`
- There should only be rest methods in an otherwise purely restful controller. `PureRestControllers`
- Controller method order (rest methods follow docs). `RestControllersMethodOrder`
- Use the simplest `request(...)` wherever possible. `RequestHelperFunctionWherePossible`
- Use auth() helper over the Auth facade. `UseAuthHelperOverFacade`
- Remove method docblocks in migrations. `NoDocBlocksForMigrationUpDown`
- Import facades (don't use aliases). `ImportFacades`
- Mailable values (from and subject etc) should be set in build(). `MailableMethodsInBuild`
- No leading slashes on route paths. `NoLeadingSlashesOnRoutePaths`
- Apply middleware in routes (not controllers). `ApplyMiddlewareInRoutes`
- Model method order (relationships > scopes > accessors > mutators > boot). `ModelMethodOrder`
- Class "things" should be ordered traits, static constants, statics, constants, public properties, protected properties, private properties, constructor, public methods, protected methods, private methods, other magic methods. `ClassThingsOrder`
- Sort imports alphabetically `AlphabeticalImports`
- Trailing commas on arrays `TrailingCommasOnArrays`
- No parenthesis on empty instantiations `NoParensEmptyInstantiations`
- Space after sole not operator `SpaceAfterSoleNotOperator`

## Disabled Lints
- No non-model-specific methods in models (only relationships, scopes, accessors, mutators, boot). `NoNonModelMethods`

## Todo Lints
- MailableMethodsInBuild
- NoSpaceAfterBladeDirectives
- QualifiedNamesOnlyForClassName
- RequestHelperFunctionWherePossible
- RestControllersMethodOrder
- SpaceAfterBladeDirectives
- TrailingSpaceAfterBladeDirectives
- UseAuthHelperOverFacade
