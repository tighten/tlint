## Tighten linter
> not psr stuff, these are conventions and agreed upon best practices

## Install
```
composer global install tighten/tlint
```

## Usage
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

## Implemented lints
- use with over array parameters in view() `ViewWithOverArrayParamaters`
- no leading slashes in namespaces or static calls or instantiations `RemoveLeadingSlashNamespaces`
- fully qualified class name only when it's being used a string (class name)
- blade directive spacing conventions
- don’t use environment variables directly in code; instead, use them in config files and call config vars from code
- there should only be rest methods in an otherwise purely restful controller
- controller method order (rest methods follow docs, otherwise alphabetize)
- use the simplest `request(...)` wherever possible
- use auth() helper over the Auth facade
- remove method docblocks in migrations

## Todo Lints
- mailable values (from and subject etc) should be set in build() not constructor
- import facades
- alphabetize use statements
- Model method order (props, relationships, scopes, accessors, mutators, custom, boot) 
- Class "things" order (consts, statics, props, traits, methods)
- no leading slashes on route paths
- minimize number of public methods on controllers (8?)
- apply middleware in routes (not controllers)
