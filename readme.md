![TLint Logo](https://raw.githubusercontent.com/tighten/tlint/master/tlint-banner.png)

<hr>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tightenco/tlint.svg?style=flat-square)](https://packagist.org/packages/tightenco/tlint)

## Install (Requires PHP 8.1+)

> **Note**
> TLint is intended to work with the tools included in [Duster](https://github.com/tighten/duster). To receive the best coverage we recommend using Duster to install and configure TLint.

```bash
# Include in project
composer require tightenco/tlint --dev

# Include globally
composer global require tightenco/tlint
```

## Upgrade

```bash
# Upgrade in project
composer update tightenco/tlint

#Upgrade globally
composer global update tightenco/tlint
```

### Upgrading from 8.x to 9.x

TLint 9 requires PHP >= 8.1.

`tformat.json` has been dropped in favor of a single `tlint.json` file.

Now linting the following files and directories:

-   `public/`
-   `bootstrap/`
-   `server.php`
-   `app/Http/Middleware/RedirectIfAuthenticated.php`
-   `Exceptions/Handler.php`
-   `app/Http/Controllers/Auth/`
-   `app/Http/Kernel.php`

To continue excluding these files and directories add them to your `tlint.json` file under `excluded`.

### Upgrading from 7.x to 8.x

A significant number of formatters were added between the 7.x and 8.x releases.
If you want to roll these out gradually or disable them altogether, you can use the `disabled` setting in your `tlint.json` config.

### Upgrading from 6.x to 7.x

TLint focuses on linting and formatting issues other tools are not able to catch.
The `7.x` release removes lints and formatters covered by tools in [Duster](https://github.com/tighten/duster). If you need to add these back you can grab them from an earlier version of TLint and follow the [Custom Configuration](#custom-configuration--presets) documentation.

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

In this case TLint will warn if you are not using the **B)** method.
This example is a sort of "meta layer" of code linting, allowing teams to avoid higher level sticking points of code review / discussions.

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

Want the output from a file as a [checkstyle XML report](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/v3.22.0/doc/schemas/fix/checkstyle.xsd)? (Primarily used with CI tools like [reviewdog](https://github.com/reviewdog/reviewdog) and [cs2pr](https://github.com/staabm/annotate-pull-request-from-checkstyle))

```
tlint lint test.php --checkstyle
```

Want to only run a single linter?

```
tlint lint --only=ArrayParametersOverViewWith
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

## Formatting

Using the same conventions as above, but using the format command, you can auto-fix some lints:

```
tlint format
```

## Linting Configuration

TLint Ships with 2 "preset" styles: Laravel & Tighten.
The Laravel preset is intended to match the conventions agreed upon by the Laravel framework contributors, while the Tighten preset is intended to match those agreed upon by Tighten team members.

The default configuration is "tighten" flavored, but you may change this by adding a `tlint.json` file to your project's root directory with the following schema:

> You may further customize the linters used by adding specific lint names to the `"disabled"` list.
> You may disable linting for specific directories by adding them to the `"excluded"` list.
> You may provide custom paths by adding them to the `"paths"` lists.

```json
{
    "preset": "laravel",
    "disabled": ["ArrayParametersOverViewWith"],
    "excluded": ["tests/"],
    "paths": [
        {
            "controllers": ["app/Domain/Http/Controllers"]
        }
    ]
}
```

### Custom Configuration & Presets

You can also add your own custom preset and linters by providing a fully-qualified class name as the preset. For example, if you created a custom preset class:

```php
namespace App\Support\Linting;

use Tighten\TLint\Presets\PresetInterface;

class Preset implements PresetInterface
{
  public function getLinters() : array
  {
    return [
      CustomLinter::class,
    ];
  }

  public function getFormatters() : array
  {
    return [
        CustomFormatter::class,
    ];
  }
}
```

Then your config could look like:

```json
{
    "preset": "App\\Support\\Linting\\Preset"
}
```

This lets you define custom linting/formatting functionality, or modify the existing linters/formatters to your liking.

## Editor Integrations

### [PHPStorm](https://plugins.jetbrains.com/plugin/10703-tlint)

<img src="tlint-phpstorm.png" width="400px" />

### [Sublime](https://packagecontrol.io/packages/SublimeLinter-contrib-tlint)

<img src="tlint-sublime.png" width="400px" />

### [VSCode](https://marketplace.visualstudio.com/items?itemName=d9705996.tighten-lint)

<img src="tlint-vscode.png" width="400px" />

## Available Linters

| Linter                                 | Description                                                                                                                                                                                                                                    |
| -------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `ApplyMiddlewareInRoutes`              | Apply middleware in routes (not controllers).                                                                                                                                                                                                  |
| `ArrayParametersOverViewWith`          | Prefer `view(..., [...])` over `view(...)->with(...)`.                                                                                                                                                                                         |
| `FullyQualifiedFacades`                | Import facades using their full namespace.                                                                                                                                                                                                     |
| `MailableMethodsInBuild`               | Mailable values (from and subject etc) should be set in build().                                                                                                                                                                               |
| `NoDatesPropertyOnModels`              | The `$dates` property was deprecated in Laravel 8. Use `$casts` instead.                                                                                                                                                                       |
| `NoDocBlocksForMigrationUpDown`        | Remove doc blocks from the up and down method in migrations.                                                                                                                                                                                   |
| `NoJsonDirective`                      | Use blade `{{ $model }}` auto escaping for models, and double quotes via json_encode over @json blade directive: `<vue-comp :values='@json($var)'>` -> `<vue-comp :values="{{ $model }}">` OR `<vue-comp :values="{!! json_encode($var) !!}">` |
| `NoLeadingSlashesOnRoutePaths`         | No leading slashes on route paths.                                                                                                                                                                                                             |
| `NoRequestAll`                         | No `request()->all()`. Use `request()->only(...)` to retrieve specific input values.                                                                                                                                                           |
| `NoSpaceAfterBladeDirectives`          | No space between blade template directive names and the opening paren:`@section (` -> `@section(`                                                                                                                                              |
| `OneLineBetweenClassVisibilityChanges` | Class members of differing visibility must be separated by a blank line                                                                                                                                                                        |
| `PureRestControllers`                  | You should not mix restful and non-restful public methods in a controller                                                                                                                                                                      |
| `QualifiedNamesOnlyForClassName`       | Fully Qualified Class Names should only be used for accessing class names                                                                                                                                                                      |
| `RemoveLeadingSlashNamespaces`         | Prefer `Namespace\...` over `\Namespace\...`.                                                                                                                                                                                                  |
| `RequestHelperFunctionWherePossible`   | Use the request(...) helper function directly to access request values wherever possible                                                                                                                                                       |
| `RequestValidation`                    | Use `request()->validate(...)` helper function or extract a FormRequest instead of using `$this->validate(...)` in controllers                                                                                                                 |
| `SpaceAfterBladeDirectives`            | Put a space between blade control structure names and the opening paren:`@if(` -> `@if (`                                                                                                                                                      |
| `SpacesAroundBladeRenderContent`       | Spaces around blade rendered content:`{{1 + 1}}` -> `{{ 1 + 1 }}`                                                                                                                                                                              |
| `UseAnonymousMigrations`               | Prefer anonymous class migrations.                                                                                                                                                                                                             |

### General PHP

-   `OneLineBetweenClassVisibilityChanges`
-   `QualifiedNamesOnlyForClassName`
-   `RemoveLeadingSlashNamespaces`

### Laravel

-   `ApplyMiddlewareInRoutes`
-   `ArrayParametersOverViewWith`
-   `FullyQualifiedFacades`
-   `MailableMethodsInBuild`
-   `NoLeadingSlashesOnRoutePaths`
-   `NoDocBlocksForMigrationUpDown`
-   `NoJsonDirective`
-   `NoSpaceAfterBladeDirectives`, `SpaceAfterBladeDirectives`
-   `PureRestControllers`
-   `RequestHelperFunctionWherePossible`
-   `RequestValidation`
-   `SpacesAroundBladeRenderContent`
-   `UseAnonymousMigrations`

## Available Formatters

**Notes about formatting**

-   Formatting is designed to alter the least amount of code possible.
-   Import related formatters are not designed to alter grouped imports.

| Formatter                              | Description                                                                                                                    |
| -------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------ |
| `ArrayParametersOverViewWith`          | Prefer `view(..., [...])` over `view(...)->with(...)`.                                                                         |
| `FullyQualifiedFacades`                | Import facades using their full namespace.                                                                                     |
| `MailableMethodsInBuild`               | Mailable values (from and subject etc) should be set in build().                                                               |
| `NoDatesPropertyOnModels`              | Use `$casts` instead of `$dates` on Eloquent models.                                                                           |
| `NoDocBlocksForMigrationUpDown`        | Removes doc blocks from the up and down method in migrations.                                                                  |
| `NoLeadingSlashesOnRoutePaths`         | No leading slashes on route paths.                                                                                             |
| `NoSpaceAfterBladeDirectives`          | No space between blade template directive names and the opening paren:`@section (` -> `@section(`                              |
| `OneLineBetweenClassVisibilityChanges` | Class members of differing visibility must be separated by a blank line                                                        |
| `RemoveLeadingSlashNamespaces`         | Prefer `Namespace\...` over `\Namespace\...`.                                                                                  |
| `RequestHelperFunctionWherePossible`   | Use the request(...) helper function directly to access request values wherever possible                                       |
| `RequestValidation`                    | Use `request()->validate(...)` helper function or extract a FormRequest instead of using `$this->validate(...)` in controllers |
| `SpaceAfterBladeDirectives`            | Put a space between blade control structure names and the opening paren:`@if(` -> `@if (`                                      |
| `SpacesAroundBladeRenderContent`       | Spaces around blade rendered content:`{{1 + 1}}` -> `{{ 1 + 1 }}`                                                              |
| `UseAnonymousMigrations`               | Prefer anonymous class migrations.                                                                                             |

### General PHP

-   `OneLineBetweenClassVisibilityChanges`
-   `RemoveLeadingSlashNamespaces`

### Laravel

-   `ArrayParametersOverViewWith`
-   `FullyQualifiedFacades`
-   `MailableMethodsInBuild`
-   `NoDatesPropertyOnModels`
-   `NoDocBlocksForMigrationUpDown`
-   `NoLeadingSlashesOnRoutePaths`
-   `NoSpaceAfterBladeDirectives`
-   `RequestHelperFunctionWherePossible`
-   `RequestValidation`
-   `SpaceAfterBladeDirectives`
-   `SpacesAroundBladeRenderContent`
-   `UseAnonymousMigrations`

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email hello@tighten.co instead of using the issue tracker.

## Credits

-   [Logan Henson](https://github.com/loganhenson)
-   [Jacob Baker-Kretzmar](https://github.com/bakerkretzmar)
-   [Anthony Clark](https://github.com/driftingly)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
