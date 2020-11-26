<?php

namespace Pharaonic\Laravel\Money;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Pharaonic\Laravel\Money\Facades\Money as FacadesMoney;
use Pharaonic\Laravel\Money\Models\Money;

/**
 * Has-Money Trait
 *
 * @version 1.0
 * @author Raggi <support@pharaonic.io>
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
trait HasMoney
{

    /**
     * Money Atrributes on Save/Create
     *
     * @var array
     */
    protected static $moneyAttributesAction = [];

    /////////////////////////////////////////////////////////////
    //
    //                      ACTIONS
    //
    ////////////////////////////////////////////////////////////

    /**
     * @return void
     */
    public function initializeHasMoney()
    {
        $attrs = get_class_vars(self::class);
        $attrs = array_merge(config('Pharaonic.money.fields', []), $attrs['moneyAttributes'] ?? []);

        foreach ($attrs as $attr)
            $this->fillable[] = $attr;
    }

    /**
     * @return void
     */
    protected static function bootHasMoney()
    {
        $attrs = get_class_vars(self::class);
        $attrs = array_merge(config('Pharaonic.money.fields', []), $attrs['moneyAttributes'] ?? []);

        // Created
        self::creating(function ($model) use ($attrs) {
            foreach ($model->getAttributes() as $name => $value) {
                if (in_array($name, $attrs)) {
                    self::$moneyAttributesAction[$name] = $value;
                    unset($model->{$name});
                }
            }
        });

        // Created
        self::created(function ($model) {
            if (count(self::$moneyAttributesAction) > 0) {
                foreach (self::$moneyAttributesAction as $name => $money)
                    $model->money($name, FacadesMoney::getCurrencyCode(), $money ?? 0);
            }
        });

        // Retrieving
        self::retrieved(function ($model) use ($attrs) {
            try {
                foreach ($attrs as $attr) $model->addGetterAttribute($attr, '_getMoneyAttribute');
            } catch (\Throwable $e) {
                throw new Exception('You have to use Pharaonic\Laravel\Helpers\Traits\HasCustomAttributes as a trait in ' . get_class($model));
            }
        });

        // Deleting
        self::deleting(function ($model) {
            $model->clearMonies();
        });
    }

    /**
     * Getting Money
     */
    public function _getMoneyAttribute($key)
    {
        if ($this->isMoneyAttribute($key))
            return $this->monies()->where([
                'name'      => $key,
                'currency'  => app('Money')->currency
            ])->first();
    }

    /**
     * Getting monies attributes
     */
    public function getMoniesAttributes(): array
    {
        $fields = isset($this->moneyAttributes) && is_array($this->moneyAttributes) ? $this->moneyAttributes : [];
        return array_merge(config('Pharaonic.money.fields', []), $fields);
    }

    /**
     * Check if money attribute
     */
    public function isMoneyAttribute(string $key): bool
    {
        return in_array($key, $this->getMoniesAttributes());
    }

    /**
     * Check if money exists
     *
     * @param string $name
     * @param string|null $currency
     * @return boolean
     */
    public function hasMoney(string $name, string $currency = null)
    {
        return $this->monies()->where([
            'name'      => $name,
            'currency'  => $currency ?? app('Money')->getCurrencyCode()
        ])->exists();
    }

    /**
     * Set & Get Money
     *
     * @param string $name
     * @param string $currency
     * @param float|null $amount
     * @return bool|Money
     */
    public function money(string $name, string $currency, float $amount = null)
    {
        // Check Money Attribute
        if (!$this->isMoneyAttribute($name)) throw new Exception($name . ' is not a money attribue.');

        // Check Currency Allowing
        $currency = strtoupper($currency);
        if (!in_array($currency, app('Money')->allowed)) throw new Exception($currency . ' currency is not allowed.');

        // Get || Set Money
        $money = $this->monies()->where(['name' => $name, 'currency' => $currency])->first();

        if ($money) {
            $money->update(['amount' => $amount]);
        } else {
            $money = $this->monies()->create([
                'name'      => $name,
                'currency'  => $currency,
                'amount'    => $amount
            ]);
        }

        if (method_exists($this, 'setted'))
            $this->setted($name, $currency, $amount);

        return $money;
    }

    /**
     * Clear monies
     */
    public function clearMonies()
    {
        return $this->monies()->delete();
    }

    /**
     * MIN Money
     *
     * @param string $name
     * @param string|null $currency
     * @return float
     */
    public static function minMoney(string $name, string $currency = null)
    {
        return Money::where([
            'priceable_type'    => self::class,
            'name'              => $name,
            'currency'          => $currency ?? app('Money')->getCurrencyCode()
        ])->min('amount');
    }

    /**
     * MAX Money
     *
     * @param string $name
     * @param string|null $currency
     * @return float
     */
    public static function maxMoney(string $name, string $currency = null)
    {
        return Money::where([
            'priceable_type'    => self::class,
            'name'              => $name,
            'currency'          => $currency ?? app('Money')->getCurrencyCode()
        ])->max('amount');
    }

    /**
     * SUM Money
     *
     * @param string $name
     * @param string|null $currency
     * @return float
     */
    public static function sumMoney(string $name, string $currency = null)
    {
        return Money::where([
            'priceable_type'    => self::class,
            'name'              => $name,
            'currency'          => $currency ?? app('Money')->getCurrencyCode()
        ])->sum('amount');
    }

    /**
     * SUM-Negative Money
     *
     * @param string $name
     * @param string|null $currency
     * @return float
     */
    public static function sumNegativeMoney(string $name, string $currency = null)
    {
        return Money::where([
            'priceable_type'    => self::class,
            'name'              => $name,
            'currency'          => $currency ?? app('Money')->getCurrencyCode()
        ])->where('amount', '<', 0)->sum('amount') * -1;
    }

    /**
     * SUM-Positive Money
     *
     * @param string $name
     * @param string|null $currency
     * @return float
     */
    public static function sumPositiveMoney(string $name, string $currency = null)
    {
        return Money::where([
            'priceable_type'    => self::class,
            'name'              => $name,
            'currency'          => $currency ?? app('Money')->getCurrencyCode()
        ])->where('amount', '>', 0)->sum('amount');
    }

    /**
     * AVG Money
     *
     * @param string $name
     * @param string|null $currency
     * @return float
     */
    public static function avgMoney(string $name, string $currency = null)
    {
        return Money::where([
            'priceable_type'    => self::class,
            'name'              => $name,
            'currency'          => $currency ?? app('Money')->getCurrencyCode()
        ])->avg('amount');
    }

    /**
     * COUNT Money
     *
     * @param string $name
     * @param string|null $currency
     * @return integer
     */
    public static function countMoney(string $name = null, string $currency = null)
    {
        $money = Money::where('priceable_type', self::class);
        if ($name) $money->where('name', $name);
        if ($currency) $money->where('currency', $currency);

        return $money->count();
    }


    /////////////////////////////////////////////////////////////
    //
    //                      RELATIONS
    //
    ////////////////////////////////////////////////////////////


    /**
     * Get list of monies
     */
    public function monies()
    {
        return $this->morphMany(Money::class, 'priceable');
    }


    /////////////////////////////////////////////////////////////
    //
    //                      SCOPES
    //
    ////////////////////////////////////////////////////////////


    /**
     * Has Money
     */
    public function scopeWithoutMonies(Builder $query)
    {
        return $query->whereDoesntHave('monies');
    }

    /**
     * Has Money
     */
    public function scopeWithMoney(Builder $query, string $currency, ?string $name = null)
    {
        $currency = $currency ?: FacadesMoney::getCurrencyCode();

        return $query->whereHas('monies', function (Builder $q) use ($name, $currency) {
            if (!empty($name))
                $q->where('name', $name);

            $q->where('currency', $currency);
        });
    }

    /**
     * Has OneOrMany Monies
     */
    public function scopeWithAnyMoney(Builder $query, array $currencies, array $names = [])
    {
        return $query->whereHas('monies', function (Builder $q) use ($currencies, $names) {
            if (!empty($names))
                $q->whereIn('name', $names);

            $q->whereIn('currency', $currencies);
        });
    }
}
