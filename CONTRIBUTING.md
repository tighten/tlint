## Running Tests Locally
- If you want to run tests locally, you can run them normally through phpunit
```
> phpunit
```

- Alternatively if you have Docker Engine installed, you can run tests in multiple PHP environments at once. Just run the following commands from the root directory:

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