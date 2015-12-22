# Openstore

An attempt to create a ZF2 ecommerce module 

[![Dependency Status](https://www.versioneye.com/user/projects/52cc2464ec1375e42b000049/badge.png)](https://www.versioneye.com/user/projects/52cc2464ec1375e42b000049)

NOTE : If you want to contribute don't hesitate, I'll review any PR.

## Installation

Clone in a directory

```sh
$ cd my/project/dir
$ git clone https://github.com/belgattitude/openstore.git .
$ composer update
```	

Create configuration files

 Rename all .dist config files and adapt with database name...

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

	
	