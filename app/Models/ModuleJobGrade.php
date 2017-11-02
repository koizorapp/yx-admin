<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleJobGrade extends Model
{
    use SoftDeletes;

    protected $table = 'module_job_grades';

    protected $fillable = ['job_grade_id','module_id'];
}
