## Tighten linter
> not psr stuff, these are conventions and agreed upon best practices

## Install
```
composer global install tighten/tlint
```

## Usage
```
tlint lint routes/web.php
```

> output
```
Linting TestLaravelApp/routes/web.php
============
Lints: 
============
4: Prefer `view(...)->with(...)` over `view(..., [...])`.
```

## Implemented lints
- use with over array parameters in view() `ViewWithOverArrayParamaters`

## Todo Lints
- no leading slashes in namespaces
- import facades
- use ->input() over ->get()
- alphabetize use statements
- remove method docblocks in stubs
- blade conventions
- controller method order (rest methods follow docs, otherwise alphabetize)
- Model method order (props, relationships, scopes, accessors, mutators, custom, boot) 
- no leading slashes on route paths
