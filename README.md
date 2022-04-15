# DatabaseBackupBundle

DatabaseBackupBundle is a Symfony Bundle to manage your databases backup.  

[![Build](https://github.com/Symandy/DatabaseBackupBundle/actions/workflows/build.yml/badge.svg?branch=main)](https://github.com/Symandy/DatabaseBackupBundle/actions/workflows/build.yml)

## Installation

```shell
composer require symandy/database-backup-bundle
```

If [Symfony Flex](https://github.com/symfony/flex) is not enabled yet for the bundle (A PR on 
[symfony/recipes-contrib](https://github.com/symfony/recipes-contrib) will be submitted soon), add the following lines
to `config/bundles.php`.

```php 
<?php

return [
    ...
    Symandy\DatabaseBackupBundle\SymandyDatabaseBackupBundle::class => ['all' => true],
    ...
];
```

## Configuration

### YAML configuration
As in the previous part, if Symfony Flex is not enabled, add the following file (`symandy_database_backup.yaml`) 
to `config/packages` directory.

```yaml
symandy_database_backup:
    connections:
        # Multiple connections can be added
        foo:
           # driver: !php/const \Symandy\DatabaseBackupBundle\Model\ConnectionDriver::MySQL
            driver: mysql
            
            # Usage of environment variables as parameters is recommended for connections configuration
            configuration:
                user: "%app.foo_db_user%"
                password: "%app.foo_db_password%"
                host: 127.0.0.1 # Already the default value, don't need to be added
                port: 3306 # Already the default value, don't need to be added
                databases: [foo, bar, baz] # Will only back up these databases
```

### Drivers

Only the `mysql` driver is currently available.

## Usage
Once the connections are configured, you only have to run the following command to generate the dumped databases files:

```shell
php bin/console symandy:databases:backup
```

It will generate one file by connection in the format `<connection_name>-<current_year>-<current_month>-<current_day>.sql`.
