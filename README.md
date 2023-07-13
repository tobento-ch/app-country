# App Country

Country support for the app.

## Table of Contents

- [Getting Started](#getting-started)
    - [Requirements](#requirements)
- [Documentation](#documentation)
    - [App](#app)
    - [Country Boot](#country-boot)
        - [Available Country Interfaces](#available-country-interfaces)
        - [Retrieve Countries](#retrieve-countries)
        - [Add Or Customize Countries](#add-or-customize-countries)
    - [Learn More](#learn-more)
        - [Using Countries In Forms](#using-countries-in-forms)
    - [Country Groups](#country-groups)
- [Credits](#credits)
___

# Getting Started

Add the latest version of the app country project running this command.

```
composer require tobento/app-country
```

## Requirements

- PHP 8.0 or greater

# Documentation

## App

Check out the [**App Skeleton**](https://github.com/tobento-ch/app-skeleton) if you are using the skeleton.

You may also check out the [**App**](https://github.com/tobento-ch/app) to learn more about the app in general.

## Country Boot

The country boot does the following:

* installs country files
* implements country interfaces

```php
use Tobento\App\AppFactory;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');
    
// Adding boots
$app->boot(\Tobento\App\Country\Boot\Country::class);

// Run the app
$app->run();
```

### Available Country Interfaces

The following interfaces are available after booting:

```php
use Tobento\App\AppFactory;
use Tobento\Service\Country\CountryFactoryInterface;
use Tobento\Service\Country\CountriesFactoryInterface;
use Tobento\Service\Country\CountryRepositoryInterface;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');
    
// Adding boots
$app->boot(\Tobento\App\Country\Boot\Country::class);
$app->booting();

$countryFactory = $app->get(CountryFactoryInterface::class);
$countriesFactory = $app->get(CountriesFactoryInterface::class);
$countryRepository = $app->get(CountryRepositoryInterface::class);

// Run the app
$app->run();
```

Check out the [**Country Service**](https://github.com/tobento-ch/service-country) to learn more about the interfaces.

### Retrieve Countries

You can retrieve countries by using the ```CountryRepositoryInterface::class```:

```php
use Tobento\App\AppFactory;
use Tobento\Service\Country\CountryRepositoryInterface;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');
    
// Adding boots
$app->boot(\Tobento\App\Country\Boot\Country::class);
$app->booting();

$countryRepository = $app->get(CountryRepositoryInterface::class);

// Run the app
$app->run();
```

Check out the [**Country Repository Interface**](https://github.com/tobento-ch/service-country#country-repository-interface) to learn more about it.

### Add Or Customize Countries

Currently, countries are available in the locales ```en```, ```de```, ```fr``` and ```it```.

You might add or customize/override new countries by the following way:

```php
use Tobento\App\AppFactory;
use Tobento\Service\Country\CountryRepositoryInterface;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');
    
// Add country directory with higher priority:
$this->app->dirs()->dir(
    dir: $this->app->dir('app').'countries-custom/',
    
    // do not use 'countries' as name for migration purposes
    name: 'countries.custom',
    
    group: 'countries',
    
    // add higher priority as default countries dir:
    priority: 300,
);

// Adding boots
$app->boot(\Tobento\App\Country\Boot\Country::class);

// Run the app
$app->run();
```

Then just add the countries files you wish to override in the defined directory. If the file does not exist, the file from the default country directory is used.

## Learn More

### Using Countries In Forms

The following examples require the [App View](https://github.com/tobento-ch/app-view) bundle with the following boots:

```php
// ...

$app->boot(\Tobento\App\View\Boot\View::class);
$app->boot(\Tobento\App\View\Boot\Form::class);

// ...
```

**Example using countries column**

```php
use Tobento\Service\Country\CountryRepositoryInterface;
use Tobento\Service\Country\CountryInterface;
use Tobento\Service\View\ViewInterface;

$countryRepository = $app->get(CountryRepositoryInterface::class);
$countries = $countryRepository->findCountries(locale: 'de');

// You may filter countries:
$countries = $countries->only(['CH', 'US']);

$view = $app->get(ViewInterface::class);

$content = $view->render(view: 'countries/select', data: [
    'countries' => $countries->column(column: 'name', index: 'code'),
]);
```

The ```views/countries/select.php``` view:

```php
<?= $view->form()->select(
    name: 'countries[]',
    items: $countries,
    emptyOption: ['none', '---'],
) ?>
/*
<select name="countries[]" id="countries">
    <option value="none">---</option>
    <option value="CH">Schweiz</option>
    <option value="US">Vereinigte Staaten</option>
</select>
*/
```

You may check out the [**Select Form Element**](https://github.com/tobento-ch/service-form#select) to learn more about it.

**Example grouping countries by continents**

```php
use Tobento\Service\Country\CountryRepositoryInterface;
use Tobento\Service\Country\CountryInterface;
use Tobento\Service\View\ViewInterface;

$countryRepository = $app->get(CountryRepositoryInterface::class);
$countries = $countryRepository->findCountries(locale: 'de');

// Grouped column:
$groupedCountries = $countries->groupedColumn(group: 'continent', column: 'name', index: 'code');
ksort($groupedCountries);

$view = $app->get(ViewInterface::class);

$content = $view->render(view: 'countries/select', data: [
    'countries' => $groupedCountries,
]);
```

The ```views/countries/select.php``` view:

```php
<?= $view->form()->select(
    name: 'countries[]',
    items: $countries,
    selectAttributes: [],
    optionAttributes: [],
    optgroupAttributes: [],
) ?>
/*
<select name="countries[]" id="countries">
    <optgroup label="Afrika">
        <option value="DZ">Algerien</option>
        ...
    </optgroup>
    <optgroup label="Antarktika">
        <option value="AQ">Antarktis</option>
        ...
    </optgroup>
    ...
</select>
*/
```

**Example grouping countries by custom group**

```php
use Tobento\Service\Country\CountryRepositoryInterface;
use Tobento\Service\Country\CountryInterface;
use Tobento\Service\View\ViewInterface;

$countryRepository = $app->get(CountryRepositoryInterface::class);
$countries = $countryRepository->findCountries(locale: 'de');

// Handle grouping:
$countries = $countries->map(function(CountryInterface $c) {
    if (in_array($c->code(), ['CH', 'FR'])) {
        return $c->withGroup('Near By')->withPriority(100);
    }
    return $c->withGroup('All Others');
});

// You may sort it by priority:
$countries = $countries->sort(
    fn(CountryInterface $a, CountryInterface $b): int => $b->priority() <=> $a->priority()
);

// Grouped column:
$groupedCountries = $countries->groupedColumn(group: 'group', column: 'name', index: 'code');

$view = $app->get(ViewInterface::class);

$content = $view->render(view: 'countries/select', data: [
    'countries' => $groupedCountries,
]);
```

The ```views/countries/select.php``` view:

```php
<?= $view->form()->select(
    name: 'countries[]',
    items: $countries,
    selectAttributes: [],
    optionAttributes: [],
    optgroupAttributes: [],
) ?>
/*
<select name="countries[]" id="countries">
    <optgroup label="Near By">
        <option value="FR">Frankreich</option>
        <option value="CH">Schweiz</option>
    </optgroup>
    <optgroup label="All Others">
        <option value="AF">Afghanistan</option>
        <option value="AX">Ålandinseln</option>
        <option value="AL">Albanien</option>
        ...
    </optgroup>
</select>
*/
```

# Credits

- [Tobias Strub](https://www.tobento.ch)
- [All Contributors](../../contributors)