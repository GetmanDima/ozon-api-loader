<?php

namespace App\Models\Ozon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialDataProductPicking extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
      'amount', 'moment', 'fin_product_id'
    ];
}
