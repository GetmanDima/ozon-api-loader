<?php

namespace App\Models\Ozon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialDataProductAction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
      'name', 'fin_product_id'
    ];
}
