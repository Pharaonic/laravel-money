<?php

namespace Pharaonic\Laravel\Money\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Money Model
 *
 * @version 1.0
 * @author Raggi <support@pharaonic.io>
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class Money extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'priceable_type', 'priceable_id',
        'name',
        'currency',
        'amount'
    ];

    /**
     * Casting Fields
     *
     * @var array
     */
    protected $casts = ['amount' => 'float'];

    ////////////////////////////////////////////////////////////
    //
    //                      ACTIONS
    //
    ////////////////////////////////////////////////////////////

    /**
     * Only Amount
     *
     * @return string
     */
    public function __toString()
    {
        return app('Money')->getCurrencyAmount($this->amount, $this->currency);
    }

    /**
     * Get Amount With Name
     *
     * @return string
     */
    public function withName()
    {
        return app('Money')->getCurrencyWithName($this->amount, $this->currency);
    }

    /**
     * Get Amount With Symbol
     *
     * @return string
     */
    public function withSymbol()
    {
        return app('Money')->getCurrencyWithSymbol($this->amount, $this->currency);
    }

    /**
     * Amount To String
     *
     * @return string
     */
    public function toString()
    {
        return app('Money')->read($this->amount, $this->currency);
    }

    /**
     * Withdraw Action
     *
     * @param float $amount
     * @return bool
     */
    public function withdraw(float $amount): bool
    {
        $this->amount -= $amount;
        $action = $this->save();

        if (method_exists($this->priceable, 'withdrew'))
            $this->priceable->withdrew($this->name, $this->currency, $this->amount);

        return $action;
    }

    /**
     * Deposit Action
     *
     * @param float $amount
     * @return bool
     */
    public function deposit(float $amount): bool
    {
        $this->amount += $amount;
        $action = $this->save();

        if (method_exists($this->priceable, 'deposited'))
            $this->priceable->deposited($this->name, $this->currency, $this->amount);

        return $action;
    }

    /**
     * Reset Action
     *
     * @return bool
     */
    public function reset(): bool
    {
        $this->amount = 0;
        $action = $this->save();

        if (method_exists($this->priceable, 'reset'))
            $this->priceable->reset($this->name, $this->currency);

        return $action;
    }

    /**
     * Check if amount == 0
     *
     * @return boolean
     */
    public function isZero(): bool
    {
        return $this->amount == 0;
    }

    /**
     * Check if amount > 0
     *
     * @return boolean
     */
    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    /**
     * Check if amount < 0
     *
     * @return boolean
     */
    public function isNegative(): bool
    {
        return $this->amount < 0;
    }

    ////////////////////////////////////////////////////////////
    //
    //                      RELATIONS
    //
    ////////////////////////////////////////////////////////////

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function priceable()
    {
        return $this->morphTo();
    }
}
