# Openstore

An attempt to create a ZF2 ecommerce module 

[![Dependency Status](https://www.versioneye.com/user/projects/52cc2464ec1375e42b000049/badge.png)](https://www.versioneye.com/user/projects/52cc2464ec1375e42b000049)

NOTE : If you want to contribute don't hesitate, I'll review any PR.

## Installation

Clone in a directory

```sh
	cd my/project/dir
	git clone https://github.com/belgattitude/openstore.git .
	php composer.phar self-update
	php composer.phar install
```	

Create configuration files

 Rename all .dist config files and adapt with database name...

Installing database

```sh
	cd my/project/dir
	php public/index.php openstore recreatedb
``` 

Test it with local webserver

```sh
   cd /my/project/dir
   php -S localhost:8082
```


## Updating

```sh
	cd my/project/dir
	git pull
```

	
	