<?php

namespace App\Models\Ozon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FboProduct extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'sku', 'name', 'quantity',
        'offer_id', 'price'
    ];
}
