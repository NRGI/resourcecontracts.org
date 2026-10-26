# NRGI

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/NRGI/resourcecontracts.org/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/NRGI/resourcecontracts.org/?branch=master)


## Install

NRGI can be cloned from github repository and installed. Following the procedure given below:

* git clone https://github.com/NRGI/resourcecontracts.org
* cd resourcecontracts.org
* add `127.0.0.1 resourcecontracts.dev` to your `/etc/hosts` file
* change your apache host file
* restart apache server

## Requirements

* PHP 8.3 or 8.4 with the `pdo_pgsql`, `mbstring`, `intl`, `gd`, `zip`, `xml`, `xsl`, `curl` and `bcmath` extensions
* PostgreSQL 12+
* beanstalkd (production queue driver; `QUEUE_CONNECTION=sync` works for local development)
* Composer 2

## Run

The app can be run with the commands below:

* install the application dependencies: `composer install`
* copy `.env.example` to `.env`, set the database connection and run `php artisan key:generate`
* give write permission to the `storage` and `bootstrap/cache` folders
* run migrations: `php artisan migrate`
* seed roles, permissions and the default users: `php artisan db:seed`
* start the dev server: `php artisan serve` and open `http://localhost:8000`
  (seeded login: `admin@nrgi.app` / `admin123`)

## Framework

The application is written in PHP based on the [Laravel](https://laravel.com) framework.
The current version is **Laravel 13** running on **PHP 8.4** (see `composer.json` and `Dockerfile`).

The HTML/Form builder (`Form::` / `HTML::` facades) is vendored in `app/Support/Html`
(formerly the abandoned `laravelcollective/html` package).


## Tools and packages

This application uses many tools and packages, the packages can 
be seen in the [composer.json](https://github.com/NRGI/resourcecontracts.org/blob/master/composer.json) file and javascript
packages are listed in the [package.json](https://github.com/NRGI/resourcecontracts.org/blob/master/package.json) file.

Some major PHP packages used are listed below:

* [gghughunishvili/entrust](https://packagist.org/packages/gghughunishvili/entrust) - maintained fork of zizaco/entrust for user roles and permissions
* [maatwebsite/excel](https://packagist.org/packages/maatwebsite/excel) - spreadsheet import/export
* [phpoffice/phpword](https://packagist.org/packages/phpoffice/phpword) - Word document generation
* [aws/aws-sdk-php](https://packagist.org/packages/aws/aws-sdk-php) - S3 storage and Mechanical Turk

## Structure

The application is structured in a very simple way in `app\Nrgi` folder.

Nrgi folder contains other 3 folders
- Repositories: Contains all the classes for storage and retrival from database. 
- Entities: Contains all the eloquent model classes.
- Services: Contains the classes which serves as the intermediate for Controllers and Repositories. All the application logic are handled here. Logger is also implemented inside services. The purpose of using services is to keep our controllers slim.

Classes inside each of the above directories are properly written within corresponding modules namespace. 

## Check code quality

We follow [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) for 
coding standard  

## Coding Conventions

We follow PSR-2

## Tests and static analysis

Feature tests run against a real PostgreSQL database (see `phpunit.xml` for the connection
defaults; a `docker run -p 5433:5432 -e POSTGRES_USER=rc -e POSTGRES_PASSWORD=rc -e POSTGRES_DB=rc_admin_test postgres:16-alpine`
container matches them).

```
vendor/bin/phpunit
vendor/bin/phpstan analyse
```

GitHub Actions (`.github/workflows/ci.yml`) runs lint, PHPStan and the test suite on PHP 8.3 and 8.4 for every push and pull request.

## Deployment

The application ships as a Docker image (`Dockerfile`: Ubuntu 24.04, PHP 8.4, Apache, beanstalkd,
supervisor, cron) deployed to AWS ECS. The image is built by AWS CodeBuild
(`configuration/buildspec-master.yml`) or by the `docker-image.yml` GitHub workflow.
Runtime configuration is injected through environment variables rendered into `.env` by
`conf/init.sh` (template: `conf/env.template`); the init script also warms the config, route and view caches.

## Support

For any queries and support, you may either [create a github issue](https://github.com/NRGI/resourcecontracts.org/issues/new) or send us an email at resourcecontracts@yipl.com.np

## Copyright and license

Code copyright Natural Resource Governance Institute. Code released under the [ MIT license ](LICENSE.md)

