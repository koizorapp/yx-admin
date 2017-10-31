<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use SoftDeletes;

    protected $table = 'equipments';

    protected $fillable = [
        'code',
        'name',
        'name_index',
        'english_name',
        'storage_name',
        'center_id',
        'brands',
        'production_area',
        'specifications',
        'purchase_price',
        'market_price',
        'once_cost',
        'clinics_id',
        'min_age_limit',
        'max_age_limit',
        'gender_limit',
        'considerations',
        'adverse_reaction',
        'description',
        'remark',
    ];
}
