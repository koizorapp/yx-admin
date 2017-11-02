<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use SoftDeletes;

    protected $table = 'modules';

    protected $fillable = [
        'code',
        'code_index',
        'name',
        'name_index',
        'center_id',
        'service_time',
        'service_after_time',
        'whether_medical',
        'min_age_limit',
        'max_age_limit',
        'gender_limit',
        'considerations',
        'adverse_reaction',
        'description',
        'expected_cost',
        'remark'
    ];
}
