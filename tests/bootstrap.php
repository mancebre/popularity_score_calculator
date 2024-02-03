<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/config/bootstrap.php')) {
    require dirname(__DIR__) . '/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
} else {
    throw new LogicException('You need to set up the project dependencies using the following commands:' . PHP_EOL . 'wget http://getcomposer.org/composer.phar' . PHP_EOL . 'php composer.phar install');
}
