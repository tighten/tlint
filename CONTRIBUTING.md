# Contributing

Contributions are **welcome** and will be fully **credited**.

Please read and understand the contribution guide before creating an issue or pull request.

## Etiquette

Be kind.

## Viability

When requesting or submitting new features, first consider whether it might be useful to others. Open source projects are used by many developers, who may have entirely different needs to your own. Think about whether or not your feature is likely to be used by other users of the project.

## Procedure

Before filing an issue:

-   Attempt to replicate the problem, to ensure that it wasn't a coincidental incident.
-   Check to make sure your feature suggestion isn't already present within the project.
-   Check the pull requests tab to ensure that the bug doesn't have a fix in progress.
-   Check the pull requests tab to ensure that the feature isn't already in progress.

Before submitting a pull request:

-   Check the codebase to ensure that your feature doesn't already exist.
-   Check the pull requests to ensure that another person hasn't already submitted the feature or fix.

## Requirements

-   **[PSR-2 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)** - The easiest way to apply the conventions is to install [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer).

-   **Tlint styles** - Changes to tlint should pass tlint checks (default, tighten flavor).

-   **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

## Running Tests Locally

-   If you want to run tests locally, you can run them normally through phpunit

```
> phpunit
```

-   Alternatively if you have Docker Engine installed, you can run tests in multiple PHP environments at once. Just run the following commands from the root directory:

```
> sudo composer buildTestEnvironments
> sudo composer runTests
```

`buildTestEnvironments` will build the Docker environments, and `runTests` will execute them. You only need to run `buildTestEnvironments` the first time you run them, and any time
a new PHP environment is added to the testing environments directory.

## Check Coverage

```
> phpdbg -qrr ./vendor/bin/phpunit --coverage-text
```
