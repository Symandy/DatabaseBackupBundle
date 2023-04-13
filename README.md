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

#### Basic configuration
If the only purpose is to back up the database of the current project, use the basic configuration file.

```yaml
symandy_database_backup:
    backups:
        app:
            connection:
                url: "%env(DATABASE_URL)%"
            strategy:
                max_files: 5
                backup_directory: "%kernel.project_dir%/backups"
```

#### Advanced usages
```yaml
symandy_database_backup:
    backups:
        foo:
            connection:
                # driver: !php/const \Symandy\DatabaseBackupBundle\Model\ConnectionDriver::MySQL
                driver: mysql

                # Usage of environment variables as parameters is recommended for connections configuration
                configuration:
                    user: "%app.foo_db_user%"
                    password: "%app.foo_db_password%"
                    host: 127.0.0.1 # Already the default value, don't need to be added
                    port: 3306 # Already the default value, don't need to be added
                    databases: [foo, bar, baz] # Will only back up these databases
            strategy:
                max_files: 5 # Number of files kept after a backup (per database)
                # backup_directory: "/var/www/backups" # The directory must be created and must have the right permissions
                backup_directory: "%kernel.project_dir%/backups"
                # backup_directory: ~ # The current directory will be used if no value is passed
                # backup_name_date_format: 'Y-m-d' # will be used if no value is passed

        bar:
            # Use Doctrine database url env parameter
            connection:
                url: "%env(DATABASE_URL)%" # url key will ALWAYS override array configuration
                configuration:
                    user: john # Overridden by url
```

### Drivers

Only the `mysql` driver is currently available.

## Usage
Once the backups are configured, you only have to run the following command to generate the dumped databases backup files:

```shell
php bin/console symandy:databases:backup
```

It will generate one file by database in the format `<backup_name>-<database>-<current_year>-<current_month>-<current_day>.sql`.
