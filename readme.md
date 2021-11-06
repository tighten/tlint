![TLint Logo](https://raw.githubusercontent.com/tighten/tlint/master/tlint-banner.png)

<hr>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tightenco/tlint.svg?style=flat-square)](https://packagist.org/packages/tightenco/tlint)

## Install (Requires PHP 7.3+)

```
composer global require tightenco/tlint
```

## Upgrade

```
composer global update tightenco/tlint
```

## What Is It?

This is an opinionated code linter (with growing support for auto-formatting!) for Tighten flavored code conventions for Laravel and PHP.

For example, Laravel has many available ways to pass variables from a controller to a view:

> **A)**

```php
$value = 'Hello, World!';

return view('view', compact('value'));
```

> **B)**

```php
return view('view', ['value' => 'Hello, World!']);
```

> **C)**

```php
return view('view')
    ->with('value', 'Hello, World!');
```

> In this case [TLint](https://github.com/tighten/tlint) will warn if you are not using the **B)** method.
> This example is a sort of "meta layer" of code linting, allowing teams to avoid higher level sticking points of code review / discussions.

> TLint also has more immediately useful lints that can supplement your editor/IDE such as:

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
tlint lint --only=UseConfigOverEnv
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

## Formatting (Beta)

Using the same conventions as above, but using the format command, you can auto-fix some lints:

```
tlint format
```

## Linting Configuration

TLint Ships with 2 "preset" styles: Laravel & Tighten.
The Laravel preset is intended to match the conventions agreed upon by the Laravel framework contributors, while the Tighten preset is intended to match those agreed upon by Tighten team members.

The default configuration is "tighten" flavored, but you may change this by adding a `tlint.json` file to your project's root directory with the following schema:

> You may further customize the linters used by adding specific lint names to the `"disabled"` list (As shown below).
> You may disable linting for specific directories by adding them to the `"excluded"` list (As shown below).

```json
{
    "preset": "laravel",
    "disabled": ["NoInlineVarDocs"],
    "excluded": ["tests/"]
}
```

### Custom Configuration & Presets

You can also add your own custom preset and linters by providing a fully-qualified class name as the preset. For example, if you created a custom preset class:

```php
namespace App\Support\Linting;

/** use ... */

class Preset implements PresetInterface
{
  public function getLinters() : array
  {
    return [
      PrefixTestsWithTest::class,
      ModelMethodOrder::class,
    ];
  }

  public function getFormatters() : array
  {
    return [];
  }
}
```

Then your config could look like:

```json
{
    "preset": "App\\Support\\Linting\\Preset"
}
```

This lets you define whatever custom linting functionality, or modify the existing linters to your liking.

## Formatting Configuration (Beta)

Similar to linting there are two "preset" styles for formatting: Laravel & Tighten.

The default configuration is "tighten", but you may change this by adding a `tformat.json` file to your project's root directory with the following schema:

```json
{
    "preset": "laravel"
}
```

## Editor Integrations

### [PHPStorm](https://plugins.jetbrains.com/plugin/10703-tlint)

<img src="tlint-phpstorm.png" width="400px" />

### [Sublime](https://packagecontrol.io/packages/SublimeLinter-contrib-tlint)

<img src="tlint-sublime.png" width="400px" />

### [VSCode](https://marketplace.visualstudio.com/items?itemName=d9705996.tighten-lint)

<img src="tlint-vscode.png" width="400px" />

## Available Linters

<!-- linters -->
| Linter | Description |
| --- | --- |
| `AlphabeticalImports` | Imports should be ordered alphabetically. |
| `ApplyMiddlewareInRoutes` | Apply middleware in routes (not controllers). |
| `ArrayParametersOverViewWith` | Prefer `view(..., [...])` over `view(...)->with(...)`. |
| `ClassThingsOrder` | Class "things" should follow the ordering presented in the [handbook](https://gist.github.com/mattstauffer/1178946cb585b17a3941dd0edcbce0c4) |
| `ConcatenationNoSpacing` | There should be no space around `.` concatenations, and additional lines should always start with a `.` |
| `ConcatenationSpacing` | There should be 1 space around `.` concatenations, and additional lines should always start with a `.` |
| `FullyQualifiedFacades` | Import facades using their full namespace. |
| `ImportFacades` | Import facades (don't use aliases). |
| `MailableMethodsInBuild` | Mailable values (from and subject etc) should be set in build(). |
| `ModelMethodOrder` | Model method order should be: booting > boot > booted > custom_static > relationships > scopes > accessors > mutators > custom |
| `NewLineAtEndOfFile` | File should end with a new line |
| `NoCompact` | There should be no calls to `compact()` in controllers |
| `NoDatesPropertyOnModels` | The `$dates` property was deprecated in Laravel 8. Use `$casts` instead. |
| `NoDocBlocksForMigrationUpDown` | Remove doc blocks from the up and down method in migrations. |
| `NoDump` | There should be no calls to `dd()`, `dump()`, `ray()`, or `var_dump()` |
| `NoInlineVarDocs` | No /** @var ClassName $var */ inline docs. [ref](https://github.com/tighten/tlint/issues/108) |
| `NoJsonDirective` | Use blade `{{ $model }}` auto escaping for models, and double quotes via json_encode over @json blade directive: `<vue-comp :values='@json($var)'>` -> `<vue-comp :values="{{ $model }}">` OR `<vue-comp :values="{!! json_encode($var) !!}">` |
| `NoLeadingSlashesOnRoutePaths` | No leading slashes on route paths. |
| `NoMethodVisibilityInTests` | There should be no method visibility in test methods. [ref](https://github.com/tighten/tlint/issues/106#issuecomment-537952774) |
| `NoParensEmptyInstantiations` | No parenthesis on empty instantiations |
| `NoRequestAll` | No `request()->all()`. Use `request()->only(...)` to retrieve specific input values. |
| `NoSpaceAfterBladeDirectives` | No space between blade template directive names and the opening paren:`@section (` -> `@section(` |
| `NoStringInterpolationWithoutBraces` | Never use string interpolation without braces |
| `NoUnusedImports` | There should be no unused imports. |
| `OneLineBetweenClassVisibilityChanges` | Class members of differing visibility must be separated by a blank line |
| `PureRestControllers` | You should not mix restful and non-restful public methods in a controller |
| `QualifiedNamesOnlyForClassName` | Fully Qualified Class Names should only be used for accessing class names |
| `RemoveLeadingSlashNamespaces` | Prefer `Namespace\...` over `\Namespace\...`. |
| `RequestHelperFunctionWherePossible` | Use the request(...) helper function directly to access request values wherever possible |
| `RequestValidation` | Use `request()->validate(...)` helper function or extract a FormRequest instead of using `$this->validate(...)` in controllers |
| `RestControllersMethodOrder` | REST methods in controllers should match the ordering here: https://laravel.com/docs/controllers#restful-partial-resource-routes |
| `SpaceAfterBladeDirectives` | Put a space between blade control structure names and the opening paren:`@if(` -> `@if (` |
| `SpaceAfterSoleNotOperator` | There should be a space after sole `!` operators |
| `SpacesAroundBladeRenderContent` | Spaces around blade rendered content:`{{1 + 1}}` -> `{{ 1 + 1 }}` |
| `TrailingCommasOnArrays` | Multiline arrays should have trailing commas |
| `UseAnonymousMigrations` | Prefer anonymous class migrations. |
| `UseAuthHelperOverFacade` | Prefer the `auth()` helper function over the `Auth` Facade. |
| `UseConfigOverEnv` | Don’t use environment variables directly; instead, use them in config files and call config vars from code |
| `ViewWithOverArrayParameters` | Prefer `view(...)->with(...)` over `view(..., [...])`. |
<!-- /linters -->

### General PHP

- `AlphabeticalImports`
- `ClassThingsOrder`
- `ConcatenationSpacing`
- `NewLineAtEndOfFile`
- `NoInlineVarDocs`
- `NoParensEmptyInstantiations`
- `NoStringInterpolationWithoutBraces`
- `NoUnusedImports`
- `OneLineBetweenClassVisibilityChanges`
- `QualifiedNamesOnlyForClassName`
- `RemoveLeadingSlashNamespaces`
- `SpaceAfterSoleNotOperator`
- `TrailingCommasOnArrays`

### PHPUnit

- `NoMethodVisibilityInTests`

### Laravel

- `ApplyMiddlewareInRoutes`
- `ArrayParametersOverViewWith`
- `FullyQualifiedFacades`
- `ImportFacades`
- `MailableMethodsInBuild`
- `NoLeadingSlashesOnRoutePaths`
- `ModelMethodOrder`
- `NoDocBlocksForMigrationUpDown`
- `NoDump`
- `NoJsonDirective`
- `NoSpaceAfterBladeDirectives`, `SpaceAfterBladeDirectives`
- `PureRestControllers`
- `RequestHelperFunctionWherePossible`
- `RequestValidation`
- `RestControllersMethodOrder`
- `SpacesAroundBladeRenderContent`
- `UseAnonymousMigrations`
- `UseAuthHelperOverFacade`
- `UseConfigOverEnv`
- `ViewWithOverArrayParameters`

## Available Formatters (Beta)

**Notes about formatting**

- Formatting is designed to alter the least amount of code possible.
- Import related formatters are not designed to alter grouped imports.

<!-- formatters -->
| Formatter | Description |
| --- | --- |
| `AlphabeticalImports` | Alphabetizes import statements. |
| `ExcessSpaceBetweenAndAfterImports` | Removes excess newlines around use statements. |
| `FullyQualifiedFacades` | Import facades using their full namespace. |
| `NewLineAtEndOfFile` | Applies a newline at the end of files. |
| `NoDatesPropertyOnModels` | Use `$casts` instead of `$dates` on Eloquent models. |
| `NoDocBlocksForMigrationUpDown` | Removes doc blocks from the up and down method in migrations. |
| `UnusedImports` | Removes unused import statements. |
| `UseAnonymousMigrations` | Prefer anonymous class migrations. |
<!-- /formatters -->

### General PHP

- `AlphabeticalImports`
- `ExcessSpaceBetweenAndAfterImports`
- `NewLineAtEndOfFile`
- `UnusedImports`

### Laravel

- `FullyQualifiedFacades`
- `NoDatesPropertyOnModels`
- `NoDocBlocksForMigrationUpDown`
- `UseAnonymousMigrations`

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email hello@tighten.co instead of using the issue tracker.

## Credits

- [Logan Henson](https://github.com/loganhenson)
- [Jacob Baker-Kretzmar](https://github.com/bakerkretzmar)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
