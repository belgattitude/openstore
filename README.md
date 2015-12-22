# Openstore

An attempt to create a ZF2 ecommerce module 

[![Dependency Status](https://www.versioneye.com/user/projects/52cc2464ec1375e42b000049/badge.png)](https://www.versioneye.com/user/projects/52cc2464ec1375e42b000049)

## Installation

Clone in a directory

```sh
$ cd my/project/dir
$ git clone https://github.com/belgattitude/openstore.git .
$ composer update
```	

## Configure

Create configuration files

 See files in config/autoload directory

 Check all .dist files and rename in .php extension, editing values for your purpose

## Creating database

```sh
$ cd my/project/dir
$ php public/index.php openstore:schema-core:create
``` 

## Load initial fixtures

```sh
$ cd my/project/dir
$ php public/index.php openstore:fixture:load
``` 

## Test

Local server testing

```sh
$ cd /my/project/dir
$ php -S localhost:8082
```


## Updating

```sh
$ cd my/project/dir
$ git pull
$ composer update
```

## Updating database

Do not use in production !

```sh
$ cd my/project/dir
$ php public/index.php openstore:schema-core:update --dump-sql 
$ php public/index.php openstore:schema-core:recreate-extra --dump-sql
```
	
	