# Changelog

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html), and the format of this changelog is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

Breaking changes are marked with ⚠️.

## [Unreleased]

## [v6.2.1] - 2021-11-12

**Fixed**

- Update suggested syntax of `NoJsonDirective` linter ([#272](https://github.com/tighten/tlint/pull/272)).
- Use `DIRECTORY_SEPARATOR` for Windows compatibility ([#275](https://github.com/tighten/tlint/pull/275)).
- Use `PHP_EOL` for Windows compatibility ([#276](https://github.com/tighten/tlint/pull/276)).

## [v6.2.0] - 2021-10-01

**Added**

- Added support for Symfony 6 ([#263](https://github.com/tighten/tlint/pull/263)).

**Fixed**

- Fixed `--only` argument to work with all linters/formatters (even the ones not in presets) ([#271](https://github.com/tighten/tlint/pull/271)).

## [v6.1.0] - 2021-09-17

**Added**

- Added `UseAnonymousMigrations` linter and formatter to prefer [anonymous migrations](https://github.com/laravel/framework/pull/36906) ([added in Laravel 8.37.0](https://github.com/laravel/framework/blob/8.x/CHANGELOG-8.x.md#v8370-2021-04-13)) ([#259](https://github.com/tighten/tlint/pull/259)).
- Added support for PHP 8.1 ([#260](https://github.com/tighten/tlint/pull/260)).
- Added a `FullyQualifiedFacades` linter and formatter to ensure Facades are imported using their full namespace (replaces the `ImportFacades` formatter added in ([#216](https://github.com/tighten/tlint/pull/216)) that never worked) ([#255](https://github.com/tighten/tlint/pull/255)).

**Changed**

- Removed `ImportFacades` linter from Tighten preset ([#255](https://github.com/tighten/tlint/pull/255)).

**Fixed**

- Fixed false positive `OneLineBetweenClassVisibilityChanges` lints with some comments between constants and properties ([#264](https://github.com/tighten/tlint/pull/264)).

## [v6.0.3] - 2021-07-20

**Changed**

- Add `ray()` to NoDump description ([#247](https://github.com/tighten/tlint/pull/247))

## [v6.0.2] - 2021-06-14

**Changed**

- Include `dd` and `dump` methods in NoDump ([#245](https://github.com/tighten/tlint/pull/245))

## [v6.0.1] - 2021-06-01

**Changed**

- Update Contributing doc with `bumpVersion` instructions.
- Add formatter config to readme ([#241](https://github.com/tighten/tlint/pull/241))
- No unused import attribute fix ([#243](https://github.com/tighten/tlint/pull/243))

## [v6.0.0] - 2021-04-23

**Changed**

- ⚠️ Use `Tighten\TLint` namespace ([#235](https://github.com/tighten/tlint/pull/235))
- Fix Laravel Preset Formatters import ([#236](https://github.com/tighten/tlint/pull/236))
- Fix invoke magic method order ([#232](https://github.com/tighten/tlint/pull/232))
- Drop PureRestControllers & ModelMethodOrder lints from the presets. ([#231](https://github.com/tighten/tlint/pull/231))
- Fix outdated link ([#234](https://github.com/tighten/tlint/pull/234))

---

For previous changes see the [Releases](https://github.com/tighten/tlint/releases) page.

[Unreleased]: https://github.com/tighten/tlint/compare/v6.2.1...HEAD
[v6.2.1]: https://github.com/tighten/tlint/compare/v6.2.0...v6.2.1
[v6.2.0]: https://github.com/tighten/tlint/compare/v6.1.0...v6.2.0
[v6.1.0]: https://github.com/tighten/tlint/compare/v6.0.3...v6.1.0
[v6.0.3]: https://github.com/tighten/tlint/compare/v6.0.2...v6.0.3
[v6.0.2]: https://github.com/tighten/tlint/compare/v6.0.1...v6.0.2
[v6.0.1]: https://github.com/tighten/tlint/compare/v6.0.0...v6.0.1
[v6.0.0]: https://github.com/tighten/tlint/compare/v5.0.16...v6.0.0
