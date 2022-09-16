<?php

namespace App\Models\Ozon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductLastId extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'last_id', 'client_id'
    ];
}
