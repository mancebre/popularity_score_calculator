# GitHub Popularity Score Calculator

## Table of Contents

- [Overview](#overview)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
- [Usage](#usage)
  - [JSON API Endpoint](#json-api-endpoint)
  - [Documentation](#documentation)
- [Testing](#testing)
  - [Set up Testing Database](#set-up-testing-database)
  - [Running Tests](#running-tests)
- [Docker Configuration](#docker-configuration)
  - [Docker Structure](#docker-structure)
  - [Nginx Dockerfile](#nginx-dockerfile)
  - [PHP Dockerfile](#php-dockerfile)
  - [Additional Docker Build Files](#additional-docker-build-files)
- [Project Structure](#project-structure)
- [Using the JSON API](#using-the-json-api)
  - [Endpoint](#endpoint)
  - [Example](#example)
  - [Error Handling](#error-handling)
  - [Caching](#caching)
  - [Notes](#notes)
- [Logging](#logging)
  - [Overview](#overview-1)
  - [Log File](#log-file)
  - [Logging Implementation](#logging-implementation)
    - [Example - Logging in Code](#example---logging-in-code)
  - [Accessing Logs](#accessing-logs)
  - [Note](#note)

## Overview

This project calculates the popularity score of a given word by searching GitHub issues. The score is based on the number of results for "{word} rocks" as a positive indicator and "{word} sucks" as a negative indicator. The score is a ratio of positive results to the total, ranging from 0 to 10. Results are stored in a local database for future queries, and the system is designed for extensibility with future providers.

## Getting Started

### Prerequisites

- Docker
- Composer
- Git
- GitHub API Documentation: [GitHub API](https://docs.github.com/en/rest/reference/search#search-issues-and-pull-requests)

### Installation

1. Clone the repository.
2. Build and run Docker containers: `docker-compose up --build`
3. Install dependencies using composer: `composer install`
4. Set up your database: `symfony console doctrine:migrations:migrate`

## Usage

### JSON API Endpoint

- Endpoint: `GET http://localhost:10302/score?term=php`
- Example Response:

```json
{
	"term": "php",
	"score": 3.33
}
```

### Documentation

For detailed information on consuming the JSON API and practical examples, please refer to the [Using the JSON API](#using-the-json-api) section.

## Testing

### Set up Testing Database

Before running tests, you need to set up the testing database.

Follow these steps:

1. Create the testing database:

```bash
symfony console doctrine:database:create --env=test
```

2. Run database migrations for the testing environment:

```bash
symfony console doctrine:migrations:migrate --env=test
```

### Running Tests

Once the testing database is set up, you can run tests using PHPUnit. Execute the following command:

```bash
symfony php bin/phpunit
```

This command will trigger PHPUnit to run all tests in the project. Make sure to review the output for any test failures or errors.

#### Additional Testing Commands

- To run tests for a specific file or directory:

```bash
symfony php bin/phpunit path/to/your/testfile.php
```

## Docker Configuration

### Docker Structure

```
docker/
├── Dockerfile-nginx
├── Dockerfile-php
└── build
    ├── nginx
    └── php
```

### Nginx Dockerfile

```Dockerfile
FROM nginx:latest
COPY build/nginx/default.conf /etc/nginx/conf.d/

RUN echo "upstream php-upstream { server fpm:9000; }" > /etc/nginx/conf.d/upstream.conf
RUN usermod -u 1000 www-data
```

### PHP Dockerfile

```Dockerfile
FROM php:8.2-fpm

# ... (installing dependencies and extensions)

COPY build/php/opcache.ini /usr/local/etc/php/conf.d/
COPY build/php/custom.ini /usr/local/etc/php/conf.d/

# ... (setting up Composer, PHPUnit, and user permissions)

WORKDIR /var/www/project/
```

### Additional Docker Build Files

- `docker/build/nginx/default.conf`: Nginx server configuration.
- `docker/build/php/custom.ini`: Custom PHP configuration.
- `docker/build/php/opcache.ini`: OPcache configuration.

## Project Structure

```
├── README.md
├── bin
│   ├── console
│   └── phpunit
├── compose.override.yaml
├── composer.json
├── composer.lock
├── config
│   ├── bundles.php
│   ├── packages
│   ├── preload.php
│   ├── routes
│   ├── routes.yaml
│   └── services.yaml
├── docker
│   ├── Dockerfile-nginx
│   ├── Dockerfile-php
│   └── build
│       ├── nginx
│       └── php
├── docker-compose.yml
├── migrations
│   └── Version20240202122212.php
├── phpunit.xml
├── phpunit.xml.bak
├── phpunit.xml.dist
├── project
├── public
│   └── index.php
├── src
│   ├── Controller
│   │   └── SearchScoreController.php
│   ├── Entity
│   │   └── SearchResult.php
│   ├── EventListener
│   │   └── ExceptionListener.php
│   ├── Kernel.php
│   ├── Provider
│   │   ├── GitHubSearchProvider.php
│   │   └── SearchProviderInterface.php
│   └── Repository
│       └── SearchResultRepository.php
├── symfony.lock
├── tests
│   ├── Controller
│   │   └── SearchScoreControllerTest.php
│   ├── Provider
│   │   └── GitHubSearchProviderTest.php
│   └── bootstrap.php
├── var
│   ├── cache
│   └── log
└── vendor
    └── (vendor dependencies)
```

## Using the JSON API

### Endpoint

To calculate the popularity score of a word, make a `GET` request to the following endpoint:

```
GET http://localhost:10302/score?term={your_search_term}
```

Replace `{your_search_term}` with the word you want to analyze.

### Example

#### Request

```bash
curl -X GET "http://localhost:10302/score?term=programming"
```

#### Response

```json
{
	"term": "programming",
	"score": 7.52
}
```

### Error Handling

If the request encounters an error, the API will return a JSON response with an `errors` field containing details about the issues.

#### Example - Validation Error

If the search term fails validation (e.g., empty, too short, or containing invalid characters), a `400 Bad Request` response will be returned:

```json
{
	"errors": [
		"Search term must not be blank.",
		"Search term must be at least 3 characters long."
	]
}
```

#### Example - Internal Server Error

If there's an unexpected internal error, a `500 Internal Server Error` response will be returned:

```json
{
	"error": "An unexpected error occurred."
}
```

#### Example 2 - Internal Server Error

If there are issues or GitHub API request failures), a `500 Internal Server Error` response will be returned:

```json
{
	"error": "Error interacting with the GitHub API. Please try again later."
}
```

### Caching

If the result for a specific term is already stored in the local database, the API will return a cached result. The response will include a `cached` field set to `true`.

#### Example - Cached Result

```json
{
	"term": "programming",
	"score": 7.52,
	"cached": true
}
```

### Notes

- The API expects a search term as the only query parameter.
- The response will always be in JSON format.
- Ensure proper error handling on the client side to handle various scenarios gracefully.

## Logging

### Overview

Logging is an essential part of any application for tracking errors, debugging, and monitoring. In this project, logging is implemented to capture errors and relevant information.

### Log File

All error logs are stored in the `var/log/dev.log` file. This file contains detailed information about errors, warnings, and other loggable events during the application's execution.

### Logging Implementation

The logging in this project is implemented using Symfony's logging components. The `LoggerInterface` is utilized to log messages, and an `ExceptionListener` is set up to catch and log exceptions.

#### Example - Logging in Code

Here's an example of how logging is used in the code:

```php
// src/EventListener/ExceptionListener.php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

class ExceptionListener
{
    public function __construct(
        private LoggerInterface $loggerInterface
    ) {
    }

    /**
     * Handle an exception and set a JSON response.
     *
     * @param ExceptionEvent $event The exception event
     * @throws \Exception description of exception
     * @return JsonResponse
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $this->loggerInterface->error('Exception of type ' . get_class($exception) . ' occurred: ' . $exception->getMessage());

        $errorMessage = 'An unexpected error occurred.';

        if ($exception instanceof \Exception) {
            $errorMessage = $exception->getMessage();
        }

        $response = new JsonResponse(['error' => $errorMessage], 500);
        $event->setResponse($response);
    }
}
```

In this example, the `ExceptionListener` catches exceptions and logs detailed information, including the type of exception and its message.

### Accessing Logs

To access the logs, you can view the contents of the `var/log/dev.log` file. You may also consider using tools like `tail` or `cat` in the terminal for real-time log monitoring.

### Note:

- In a production environment, logging configurations may differ, and logs might be stored in separate files or forwarded to external services. Ensure to check and configure logging appropriately based on your deployment environment.
