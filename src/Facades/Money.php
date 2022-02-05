<?php

namespace Pharaonic\Laravel\Money\Facades;

/**
 * Money Facade
 *
 * @version 1.0
 * @author Raggi <support@pharaonic.io>
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
final class Money
{
    /**
     * Languages List
     *
     * @var array
     */
    private static $languages = ['ar', 'en', 'fr', 'de', 'es'];

    /**
     * Currencies List
     *
     * @var array
     */
    public $currencies;

    /**
     * And List
     *
     * @var array
     */
    private $and;

    /**
     * Allowed List
     */
    public $allowed;

    /**
     * Names List
     *
     * @var array
     */
    public $names;

    /**
     * Default Language
     *
     * @var string
     */
    public $language;

    /**
     * Default Currency
     *
     * @var string
     */
    public $currency;

    /**
     * Select specific currencies
     *
     * @var array
     */
    public $only = [];

    /**
     * Except specific currencies
     *
     * @var array
     */
    public $except = [];



    /**
     * Initialization
     *
     * @return void
     */
    public function init()
    {
        $this->language = config('Pharaonic.money.language', 'en');
        $this->currency = config('Pharaonic.money.currency', 'USD');
        $this->currencies = require(__DIR__ . '/../data/currencies.php');
        $this->and = require(__DIR__ . '/../data/and.php');
        $this->names = require(__DIR__ . '/../data/lang/' . $this->language . '.php');
        $this->only = config('Pharaonic.money.only', []);
        $this->except = config('Pharaonic.money.except', []);
        $this->allowed = array_keys($this->names);

        if (!empty($this->only)) {
            $this->allowed = array_filter($this->allowed, function ($c) {
                return in_array(strtoupper($c), $this->only);
            });
        } else if (!empty($this->except)) {
            foreach ($this->except as $c) {
                $c = array_search(strtoupper($c), $this->allowed);
                if ($c !== false)
                    unset($this->allowed[$c]);
            }
        }
    }


    ///////////////////////////////////////////////////////
    //
    //                      STATIC
    //
    ///////////////////////////////////////////////////////


    /**
     * Set Language
     *
     * @param string $language
     * @return void
     */
    public static function setLanguage(string $language)
    {
        $language = strtolower($language);
        $language = in_array($language, self::$languages) ? $language : config('Pharaonic.money.language', 'en');

        $appMoney = app('Money');
        $appMoney->language = $language;
        $appMoney->names = require(__DIR__ . '/../data/lang/' . $appMoney->language . '.php');
    }

    /**
     * Set Currency
     *
     * @param string $currency
     * @return void
     */
    public static function setCurrency(string $currency)
    {
        $appMoney = app('Money');
        $currency = strtoupper($currency);
        $currency = in_array($currency, array_keys($appMoney->currencies)) ? $currency : config('Pharaonic.money.currency', 'USD');

        $appMoney->currency = $currency;
        // config(['Pharaonic.money.currency' => $currency]);
    }

    /**
     * Select specific currencies
     *
     * @param array $currencies
     * @return void
     */
    public static function only(array $currencies)
    {
        if (!empty($currencies)) {
            $appMoney = app('Money');
            $appMoney->allowed = array_keys($appMoney->names);
            $appMoney->only = $currencies;

            $appMoney->allowed = array_filter($appMoney->allowed, function ($c) {
                return in_array(strtoupper($c), $appMoney->only);
            });
        }
    }

    /**
     * Except specific currencies
     *
     * @param array $currencies
     * @return void
     */
    public static function except(array $currencies)
    {
        if (!empty($currencies)) {
            $appMoney = app('Money');
            $appMoney->allowed = array_keys($appMoney->names);
            $appMoney->except = $currencies;

            foreach ($appMoney->except as $c) {
                $c = array_search(strtoupper($c), $appMoney->allowed);
                if ($c !== false)
                    unset($appMoney->allowed[$c]);
            }
        }
    }

    /**
     * Get Current Language
     *
     * @return string
     */
    public static function getLanguage()
    {
        return app('Money')->language;
    }

    /**
     * Get Current Currency Code
     *
     * @return string
     */
    public static function getCurrencyCode()
    {
        return app('Money')->currency;
    }

    /**
     * Get Current Currency Name
     *
     * @return string
     */
    public static function getCurrencyName()
    {
        $appMoney = app('Money');
        return $appMoney->names[$appMoney->currency]['name'];
    }

    /**
     * Get Current Currency Symbol
     *
     * @return string
     */
    public static function getCurrencySymbol()
    {
        $appMoney = app('Money');
        return $appMoney->currencies[$appMoney->currency]['major']['symbol'];
    }

    /**
     * Get Current Currency Symbol
     *
     * @return string
     */
    public static function getCurrenciesList()
    {
        $names = array_map(function ($currency) {
            return $currency['name'];
        }, app('Money')->names);

        asort($names);

        return $names;
    }


    ///////////////////////////////////////////////////////
    //
    //                      PUBLIC
    //
    ///////////////////////////////////////////////////////

    /**
     * Get Currency with name
     *
     * @return string
     */
    public function getCurrencyAmount($amount, string $currency = null): ?string
    {
        $currency = strtoupper($currency);
        if (!in_array($currency, $this->allowed)) throw new \Exception($currency . ' Currency is not allowed.');

        $info = $this->currencies[$currency];

        return ReadableDecimal(
            $amount,
            $info['format']['decimals'],
            $info['format']['decimal_mark'],
            $info['format']['thousands_separator'],
        );
    }

    /**
     * Get Currency with name
     *
     * @return string
     */
    public function getCurrencyWithName($amount, string $currency = null): ?string
    {
        return $this->getCurrencyAmount($amount, $currency) . ' ' . $currency;
    }

    /**
     * Get Currency with Symbol
     *
     * @return string
     */
    public function getCurrencyWithSymbol($amount, string $currency = null): ?string
    {
        $amount = $this->getCurrencyAmount($amount, $currency);
        $info = $this->currencies[$currency];
        $symbol = $info['major']['symbol'];

        if ($info['format']['symbol_first']) {
            $amount = $symbol . ' ' . $amount;
        } else {
            $amount = $amount . ' ' . $symbol;
        }

        return  $amount;
    }

    /**
     * Amount To String
     *
     * @param int|float|double|string $amount
     * @param string $currency
     * @return string|null
     */
    public function read($amount, string $currency = null): ?string
    {
        $amount = $this->getCurrencyAmount($amount, $currency);

        $info = $this->currencies[$currency];
        $name = $this->names[$currency];

        $amount = explode($info['format']['decimal_mark'], $amount);
        $decimal = (int)($amount[1] ?? 0);
        $amount = (int)str_replace($info['format']['thousands_separator'], '', $amount[0]);

        return ReadableNumberToString($amount, $this->language) . ' ' . $name['major'] . ($decimal ?  ' ' . $this->and[$this->language] . ReadableNumberToString($decimal, $this->language) . ' ' . $name['minor'] : '');
    }
}
