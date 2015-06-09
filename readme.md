# NRGI



## Install

NRGI can be cloned from github repository and installed. Following the procedure given below:

* git clone GitHub url
* cd nrgi
* add `127.0.0.1 nrgi.dev` to your /etc/hosts file
* change your apache host file
* restart apache server

## Run

The app can be run with the command below:

* install the application dependencies using command: ` composer install `
* copy .env.example to .env and update your the database configurations
* give read/write permission to the storage folder using `chmod -R 777 storage`
* run migration using ` php artisan migrate `
* seed dummy data using ` php artisan db:seed `
* make a directory uploads inside public and give read/write permission to it
* access `http://nrgi.dev`

## Framework

The application is written in PHP based on the [Laravel](http://laravel.com) framework, current version of Laravel 
used for this project is 5.
 

## Tools and packages

This application uses many tools and packages, the packages can 
be seen in the [composer.json](http://gitlab.yipl.com.np/web-apps/agentcis/blob/master/composer.json) file and javascript
packages are listed in the [package.json](http://gitlab.yipl.com.np/web-apps/agentcis/blob/master/package.json) file.

Some major PHP packages used are listed below:

* [zizaco/entrust](https://packagist.org/packages/zizaco/entrust) - for user roles and permission

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

## Tests

For this project we use `php` unit tests using `PHPUnit` framework integrated with Shippable CI.


```
phpunit or ./bin/vendor/phpunit
```

## Deployment

We use Elastic Beanstalk CLI. 
