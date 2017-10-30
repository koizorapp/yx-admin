<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Personnel extends Model
{
    use SoftDeletes;

    protected $table = 'personnel';

    protected $fillable = [
        'name','center_id','job_grade_name','job_grade_id','hourly_wage'
    ];
}
