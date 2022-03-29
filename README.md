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
