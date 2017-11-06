<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $table = 'projects';

    protected $fillable = [
        'code',
        'code_index',
        'name',
        'center_id',
        'category_id',
        'description',
        'time',
        'market_price',
        'member_price',
        'considerations',
        'adverse_reaction',
        'remark'
    ];
}
