#! /bin/bash

echo "Building testing environment for PHP7.1..."
docker build -t tightenco/tests-php7.1 ./environments/php7.1/

echo "Building testing environment for PHP7.2..."
docker build -t tightenco/tests-php7.2 ./environments/php7.2/

echo "Building testing environment for PHP7.3..."
docker build -t tightenco/tests-php7.3 ./environments/php7.3/

echo "Building testing environment for PHP-Nightly..."
docker build -t tightenco/tests-php-nightly ./environments/nightly/
