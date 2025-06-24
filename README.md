# Secret Server

A simple Symfony API project that stores secret messages, and returns them if the correct hash is provided for it.

## Setup

```sh
composer install
php bin/console doctrine:database:create
php bin/console doctrine:schema:create
```

## Run
```sh
symfony server:start
```

## Setup Test

```sh
php bin/console doctrine:database:create --env=test
php bin/console doctrine:schema:create --env=test
```

## Run tests
```sh
php bin/phpunit
```