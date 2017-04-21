## Tighten linter
> not psr stuff, these are conventions and agreed upon best practices

## Install
```
composer global require loganhenson/tlint
```
> You will need to add `"minimum-stability": "dev"` to your `~/.composer.json` if it is not present for this dev version.

## Usage
For entire project
```
tlint
```
OR for individual files and specific directories
```
tlint lint routes/ViewWithOverArrayParamatersExample.php
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
tlint lint src
tlint lint tests
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

## Todo Lints
- Model method order (relationships, scopes, accessors, mutators, custom, boot).
- Class "things" order (consts, statics, props, traits, methods).
- Minimize number of public methods on controllers (8?).
