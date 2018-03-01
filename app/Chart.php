<?php

namespace App;

use App\Scopes\ChartScope;
use App\Enums\ChartTypeEnum;
use App\Enums\ChartAssetTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Sleimanx2\Plastic\Searchable;

class Chart extends Model
{
    // use Searchable;
    // public $searchable = ['id',
    // 'code',
    // 'name',
    // 'type',
    // 'country',
    // 'taxpayer_id',
    // 'sub_type',
    // 'is_accountable'];

    //Assign General Scope to all Queries. Simplifies not having to individuall call a scope.
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ChartScope);
    }

    //Brings all Cash and Bank accounts.
    public function scopeMoneyAccounts($query)
    {
        return $query
        ->where('type', 1)
        ->where(function ($x)
        {
            $x
            ->where('sub_type', 1)
            ->orWhere('sub_type', 3);
        });
    }

    //Brings all Fixed Asset Type accounts into list.
    public function scopeFixedAssets($query)
    {
        return $query
        ->where('type', 1)
        ->where('sub_type', 9);
    }

    //Brings all Item accounts (formally known as Cost Centers) into Sales Detail
    public function scopeSalesAccounts($query)
    {
        return $query
        ->where(function ($x)
        {
            $x
            //Bring all Income Types.
            //Without worring about sub_types because we need to bring all.
            ->where('type', 4)
            //Bring sub_type = Fixed Asset (Type = Asset) incase you want to sell a car or house.
            ->orWhere(function ($y)
            {
                $y
                ->where('type', 1)
                ->where('sub_type', 9);
            });
        });
    }

    //Brings all Item Accounts (formally known as Cost Centers) into Purchase Detail
    public function scopePurchaseAccounts($query)
    {
        return $query
        ->where(function ($x)
        {
            $x
            ->where(function ($y)
            {
                $y
                ->where('type', 5)
                ->orWhere(function ($z)
                {
                    //Bring all Expenses except for Wages, Depreciation, these accounts you cannot purchase.
                    $z->where('sub_type', 2);
                    $z->orWhere('sub_type', 3);
                    $z->orWhere('sub_type', 6);
                    $z->orWhere('sub_type', 7);
                    $z->orWhere('sub_type', 9);
                    $z->orWhere('sub_type', 10);
                });
            })
            ->where(function ($y)
            {
                $y
                ->where('type', 1)
                ->where('sub_type', 9);
            });
        });
    }

    //Debit VAT Accounts are used in Sales & Debit Notes
    public function scopeVATDebitAccounts($query)
    {
        return $query
        ->where('type', 2)
        ->where('sub_type', 3);
    }

    //Credit VAT Accounts are used in Purchases & Credit Notes
    public function scopeVATCreditAccounts($query)
    {
        return $query
        ->where('type', 1)
        ->where('sub_type', 12);
    }


    /**
    * Get the version that owns the model.
    *
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function version()
    {
        return $this->belongsTo(ChartVersion::class);
    }

    /**
    * Get the aliases for the model.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function aliases()
    {
        return $this->hasMany(ChartAlias::class, 'foreign_key', 'local_key');
    }

    /**
    * Get the owner that owns the model.
    *
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function taxPayer()
    {
        return $this->belongsTo(Taxpayer::class);
    }

    /**
    * Get the partner that owns the model.
    *
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function partner()
    {
        return $this->belongsTo(Taxpayer::class);
    }

    /**
    * Get the fixedAssets for the model.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function fixedAssets()
    {
        return $this->hasMany(FixedAsset::class);
    }

    /**
    * Get the transactions for the model.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
    * Get the accountMovements for the model.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function accountMovements()
    {
        return $this->hasMany(AccountMovement::class);
    }

    /**
    * Get the productionDetails for the model.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function productionDetails()
    {
        return $this->hasMany(ProductionDetail::class);
    }

    /**
    * Get the transactionDetails for the model.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    /**
    * Get the journalDetails for the model.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function journalDetails()
    {
        return $this->hasMany(JournalDetail::class);
    }

    /**
    * Get the journalSimDetail for the model.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function journalSimDetail()
    {
        return $this->hasMany(JournalSimDetail::class);
    }

    /**
    * Get the journalTemplateDetails for the model.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function journalTemplateDetails()
    {
        return $this->hasMany(JournalTemplateDetail::class);
    }

    /**
    * Get the cycleBudgets for the model.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function cycleBudgets()
    {
        return $this->hasMany(CycleBudget::class);
    }
}
