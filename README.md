## Dev

```bash
composer install
symfony console doctrine:migrations:migrate
```

## Test

```bash
symfony console doctrine:database:create --env=test
symfony console doctrine:migrations:migrate --env=test
```

## Run tests

```bash
symfony php bin/phpunit
```
