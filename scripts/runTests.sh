#! /bin/bash

echo "Running tests in PHP7.3..."
docker run -v $PWD:/home/tighten/ tighten/tests-php7.3

echo "Running tests in PHP7.4..."
docker run -v $PWD:/home/tighten/ tighten/tests-php7.4

echo "Running tests in PHP-Nightly..."
docker run -v $PWD:/home/tighten/ tighten/tests-php-nightly
