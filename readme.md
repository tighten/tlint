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
