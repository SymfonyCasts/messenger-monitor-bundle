Messenger Monitor
=================

[![CI](https://github.com/SymfonyCasts/messenger-monitor-bundle/actions/workflows/ci.yaml/badge.svg)](https://github.com/SymfonyCasts/messenger-monitor-bundle/actions/workflows/ci.yaml)

> **Note**
> **THIS BUNDLE IS EXPERIMENTAL & UNSTABLE** and may not work and probably isn't ready for production.
> It's also super rough and currently is in a development phase.

A Symfony Bundle to show you information about your Messenger queues/transports.

### Implemented Features

* Show queue length in console (configure interval) 

### Planned Features

* Add admin route to see the queues in the browser
* Auto Refresh
* Refactor queue information to allow additional data
* Collect data (how? TBD)
* Show more queue information (avg time, ago, ...)

### Phase 2

* Realtime updates in the browser (use TURTED_reactphp)

Installation
------------

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```console
$ composer require symfonycasts/messenger-monitor-bundle
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require symfonycasts/messenger-monitor-bundle
```

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    SymfonyCasts\MessengerMonitorBundle\SymfonyCastsMessengerMonitorBundle::class => ['all' => true],
];
```

Usage
-----

- `bin/console messenger:monitor` to refresh every 3 seconds (default)
- `bin/console messenger:monitor -i 0` to get the information only once
- `bin/console messenger:monitor -i 1` to refresh every second

Check `bin/console help messenger:monitor` for more information.
