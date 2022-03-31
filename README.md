# DatabaseBackupBundle

DatabaseBackupBundle is a Symfony Bundle to manage your databases backup.  

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
As in the previous part, if Symfony Flex is not enabled, add the following file (`symandy_database_backup.yaml`) 
to `config/packages` directory.

```yaml
symandy_database_backup:
    connections:
        # Multiple connections can be added
        foo:
           # driver: !php/const \Symandy\DatabaseBackupBundle\Model\ConnectionDriver::MySQL
            driver: mysql
            configuration:
                user: "%app.foo_db_user%"
                password: "%app.foo_db_password%"
                host: localhost # Already the default value, don't need to be added
                port: 3306 # Already the default value, don't need to be added

```
