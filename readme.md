[<img src="./tlint.svg" width="400">]()

<hr>

[![Build Status](https://travis-ci.com/tightenco/tlint.svg?branch=master)](https://travis-ci.org/tightenco/tlint)

## Install (Requires php7.1+)
```
composer global require tightenco/tlint
```

## What is it?

This is an opinionated code linter for Tighten flavored code conventions for Laravel and Php.

For example, Laravel has many available ways to pass variables from a controller to a view:

> **A)**
```php
return view('view', ['value' => 'Hello, World!']);
```

> **B)**
```php
$value = 'Hello, World!';

return view('view', compact('value'));
```

> **C)**
```php
return view('view')
    ->with('value', 'Hello, World!');
```

> In this case [TLint](https://github.com/tightenco/tlint) will warn if you are not using the **C)** method.
> This example is a sort of "meta layer" of code linting, allowing teams to avoid higher level sticking points of code review / discussions.

> TLint also has more immediately useful lints that can supplement your editor/ide such as:
- `NoUnusedImports`
- `TrailingCommasOnArrays`
- And many more! (See below for full listing)

## Usage
For entire project (you must pass the lint command to use other options)
```
tlint
```
For individual files and specific directories
```
tlint lint index.php
tlint lint app
```

You can also lint only diff files by running the following with unstaged git changes
```
tlint lint --diff
tlint lint src --diff
```

Want the output from a file as JSON? (Primarily used for integration with editor plugins)
```
tlint lint test.php --json
```

Want to only run a single linter?
```
tlint --only=UseConfigOverEnv
```

## Example Output
```bash
Linting TestLaravelApp/routes/web.php
============
Lints: 
============
! Prefer `view(...)->with(...)` over `view(..., [...])`.
5 : `    return view('test', ['test' => 'test']);``
```

## Editor Integrations

### [PHPStorm](https://plugins.jetbrains.com/plugin/10703-tlint)
[<img src="./tlint-phpstorm.png" width="400">]()

## Lints
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
- One blank line between class constants / properties of different visibility `OneLineBetweenClassVisibilityChanges`
- Spaces around blade rendered content `SpacesAroundBladeRenderContent`
- Never use string interpolation without braces `NoStringInterpolationWithoutBraces`
- Spaces around concat operators, and start additional lines with concat `ConcatenationSpacing`
- File should end with a new line `NewLineAtEndOfFile`
- No /** @var ClassName $var */ inline docs `NoInlineVarDocs`
- There should be no unused imports `NoUnusedImports`
- There should be no calls to `dd()` `NoDd`
- Use `request()->validate(...)` helper function or extract a FormRequest instead of using `$this->validate(...)` in controllers `RequestValidation`
- There should be no method visibility in test methods `NoMethodVisibilityInTestsTest`
