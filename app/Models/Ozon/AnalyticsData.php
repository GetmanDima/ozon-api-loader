<?php

namespace App\Models\Ozon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsData extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
      'city', 'delivery_type', 'is_premium',
      'payment_type_group_name', 'region', 'warehouse_id',
      'warehouse_name', 'fbo_id'
    ];
}
