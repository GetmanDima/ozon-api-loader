<?php

namespace App\Models\Ozon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FinancialDataProduct extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'client_price', 'commission_amount', 'commission_percent',
        'old_price', 'payout', 'price',
        'product_id', 'quantity', 'total_discount_percent',
        'total_discount_value', 'fbo_id'
    ];

    public function actions(): HasMany
    {
        return $this->hasMany(FinancialDataProductAction::class, 'fin_product_id');
    }

    public function picking(): HasOne
    {
        return $this->hasOne(FinancialDataProductPicking::class, 'fin_product_id');
    }
}
