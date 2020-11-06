


<p align="center"><a href="https://pharaonic.io" target="_blank"><img src="https://raw.githubusercontent.com/Pharaonic/logos/main/money.jpg" width="470"></a></p>

<p align="center">
<a href="https://github.com/Pharaonic/laravel-money" target="_blank"><img src="http://img.shields.io/badge/source-pharaonic/laravel--money-blue.svg?style=flat-square" alt="Source"></a> <a href="https://packagist.org/packages/pharaonic/laravel-money" target="_blank"><img src="https://img.shields.io/packagist/v/pharaonic/laravel-money?style=flat-square" alt="Packagist Version"></a><br>
<a href="https://laravel.com" target="_blank"><img src="https://img.shields.io/badge/Laravel->=6.0-red.svg?style=flat-square" alt="Laravel"></a> <img src="https://img.shields.io/packagist/dt/pharaonic/laravel-money?style=flat-square" alt="Packagist Downloads"> <img src="http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Source">
</p>


##### Laravel Money provides a quick and easy methods to manage your model money.



----------------



#### Install

Install the latest version using [Composer](https://getcomposer.org/):
```bash
$ composer require pharaonic/laravel-money
```

then publish the migration & config files

```bash
$ php artisan vendor:publish --tag=laravel-money
```

------------------------------



#### Configuration & Options

- [Configuration](#config)

- [Options](#options)

  

#### Usage
- [How to include it in your model](#include)

- [Setting Money](#set)

- [Getting & Displaying Money](#get)

- [Money Actions](#actions)

- [Relations & Scopes](#relations_scopes)

- [Aggregates](#aggregates)

- [Events](#events)

  
--------------------------------------



<a  name="config"></a>

##### Configuration
```php
// config/Pharaonic/money.php

return [

    // Default Language
    'language'  => 'en',

    // Default Currency
    'currency'  => 'USD',

    // Select specific currencies
    'only'      => [],

    // Except specific currencies
    'except'    => [],

    // Default fields names
    'fields'    => ['price']
];
```



<a  name="options"></a>

##### Options
```php
use Pharaonic\Laravel\Money\Facades\Money;

// Setting default currency
Money::setCurrency('USD');

// Setting default language
// Supported language [ar, en, fr, de, es]
Money::setLanguage('ar');

// Select specific currencies 
Money::only(['USD']);
    
// Except specific currencies
Money::except(['USD']);

// Get current language
$language 		= Money::getLanguage();

// Get currency Code, Name, Symbol
$currency_code 		= Money::getCurrencyCode();
$currency_name 		= Money::getCurrencyName();
$currency_symbol 	= Money::getCurrencySymbol();

// Get currencies list (code => name)
$currencies 		= Money::getCurrenciesList();

```



--------------------------



<a  name="include"></a>

##### How to include it in your model

```php
...
use Pharaonic\Laravel\Helpers\Traits\HasCustomAttributes;
use Pharaonic\Laravel\Money\HasMoney;

class Person extends Model
{
    use HasCustomAttributes, HasMoney;
    
    // You can include your all monies names here.
	protected $moneyAttributes = ['balance'];
    ...
}
```



<a  name="set"></a>

##### Setting Money

```php
// Setting money to exists person
$person = Person::find(1);
$person->money('balance', 'USD', 100);

// Setting money to new person with current currency ($currency_code)
$person = Person::create([
    'balance'
    ...
]);
```



<a  name="get"></a>

##### Getting & Displaying Money

```php
$person = Person::find(1);

// Get money with specific currency
echo $person->money('balance', 'USD');

echo $person->balance; 					// 100.00
echo $person->balance->amount; 			// 100
echo $person->balance->withName(); 		// 100.00 USD
echo $person->balance->withSymbol();	// $ 100.00
echo $person->balance->toString() 		// one hundred dollars {PHP Extension intl}
```



<a  name="actions"></a>

##### Money Actions

```php
$person = Person::find(1);

$person->balance->withdraw(0.50); 	// withdraw 50 cents
$person->balance->deposit(10.50); 	// deposit 10 dollars and 50 cents
$person->balance->reset();			// resetting money to zero
```



<a  name="relations_scopes"></a>

##### Relations & Scopes

```php
// Getting monies with all currencies
$monies = $person->monies;

// Getting all People who has no monies
dd(Person::withoutMonies()->get());

// with Currency only
$pplWithMoney = Person::withMoney('USD')->get(); 
// with Currency and name
$pplWithMoney = Person::withMoney('USD', 'balance')->get(); 

// with Currencies only
$pplWithMoney = Person::withAnyMoney(['USD'])->get();
// with Currencies and names
$pplWithMoney = Person::withAnyMoney(['USD'], ['balance'])->get(); 
```



<a  name="aggregates"></a>

##### Aggregates

```php
// Getting MIN Money
echo Person::minMoney('balance');
echo Person::minMoney('balance', 'USD');

// Getting MAX Money
echo Person::maxMoney('balance');
echo Person::maxMoney('balance', 'USD');

// Getting SUM All Monies
echo Person::sumMoney('balance');
echo Person::sumMoney('balance', 'USD');

// Getting SUM Negative Monies
echo Person::sumNegativeMoney('balance');
echo Person::sumNegativeMoney('balance', 'USD');

// Getting SUM Positive Monies
echo Person::sumPositiveMoney('balance');
echo Person::sumPositiveMoney('balance', 'USD');

// Getting Average Monies
echo Person::avgMoney('balance');
echo Person::avgMoney('balance', 'USD');

// Getting Count OF Monies Rows
echo Person::countMoney();
echo Person::countMoney('balance');
echo Person::countMoney(null, 'USD');
echo Person::countMoney('balance', 'USD');
```



<a  name="events"></a>

##### Events

```php
...
class Person extends Model
{
    ...
    /**
     * Setted Money with (Create/New/money method) Event
     *
     * @param string $name
     * @param string $currency
     * @param float $amount
     * @return void
     */
    public function setted(string $name, string $currency, float $amount)
    {
        //
    }
    
    /**
     * Withdrew Money Event
     *
     * @param string $name
     * @param string $currency
     * @param float $amount
     * @return void
     */
    public function withdrew(string $name, string $currency, float $amount)
    {
        //
    }

    /**
     * Deposited Money Event
     *
     * @param string $name
     * @param string $currency
     * @param float $amount
     * @return void
     */
    public function deposited(string $name, string $currency, float $amount)
    {
        //
    }

    /**
     * Reset Money Event
     *
     * @param string $name
     * @param string $currency
     * @return void
     */
    public function reset(string $name, string $currency)
    {
        //
    }
    ...
}
```



#### License

[MIT license](LICENSE.md)
