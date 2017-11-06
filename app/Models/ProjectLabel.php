<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectLabel extends Model
{
    use SoftDeletes;

    protected $table = 'project_labels';

    protected $fillable = [
        'project_id','label_id','center_id','label_category_id'
    ];
}
