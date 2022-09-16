<?php

namespace App\Models\Ozon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Fbo extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'cancel_reason_id', 'created_at', 'in_process_at',
        'order_id', 'order_number', 'posting_number',
        'status', 'client_id'
    ];

    public function products(): HasMany
    {
        return $this->hasMany(FboProduct::class, 'fbo_id');
    }

    public function financialDataProducts(): HasMany
    {
        return $this->hasMany(FinancialDataProduct::class, 'fbo_id');
    }

    public function analyticsData(): HasOne
    {
        return $this->hasOne(AnalyticsData::class, 'fbo_id');
    }
}
