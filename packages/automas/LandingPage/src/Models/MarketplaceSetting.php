<?php

namespace Automas\LandingPage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MarketplaceSetting extends Model
{
    protected $fillable = [
        'title',
        'module',
        'subtitle',
        'config_sections'
    ];

    protected $casts = [
        'config_sections' => 'array'
    ];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('marketplace_settings');
        });

        static::deleted(function () {
            Cache::forget('marketplace_settings');
        });
    }
}