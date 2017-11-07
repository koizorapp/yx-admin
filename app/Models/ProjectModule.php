<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectModule extends Model
{
    use SoftDeletes;

    protected $table = 'project_modules';

    protected $fillable = [
        'project_id','module_id','sort'
    ];
}
