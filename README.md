## Introduction

Services is collection of your inner services.

**ONLY PHP 7.1 and upper**


## Install
```
composer require efureev/services
```

Insert in `providers` sections into `config/app.php`:
```
\Fureev\Services\ServiceProvider::class,
```

## Config (config/di.php)
All properties at your Provider class has defined into drivers section for concrete driver.

```php
 <?php
 
 return [
     //'name'    => 'testing', // Name component in DI. Default = 'services' 
     'drivers' => [
         'user'  => [
             // Allow load driver into app DI. Expl: app('services.user')
             'defer' => false
         ],
         'test'  => [
             // if it's not defined - use class CustomProvider  
             'provider' => \App\Services\Test\Provider::class,
             'count'    => 1 // local property into class \App\Services\Test\Provider
         ],
         'defer' => [
         ],
     ]
 ];
```

You may redefine ServiceManager and use build-in method:

```php
<?php

namespace App\Services;

use App\Services\User\Provider as UserProvider;

class ServiceManager extends \Fureev\Services\ServiceManager
{
    protected function createUserDriver(?array $driverConfig = [])
    {
        return $this->buildProvider(UserProvider::class, $driverConfig);
    }
}
``` 

and rebind `services` into DI. Example, in `\App\Providers\AppServiceProvider` in register method:
```php
$this->app->singleton(app('config')->get(ServiceManager::configSection() . '.name'), new ServiceManager($this->app));
```
