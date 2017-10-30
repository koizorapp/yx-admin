<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobGrade extends Model
{
    protected $table = 'job_grade';

    protected $fillable = [
        'center_id','name','hourly_wage'
    ];
}
