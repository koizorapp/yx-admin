<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleLabel extends Model
{
    use SoftDeletes;

    protected $table = 'module_labels';

    protected $fillable = [
        'module_id','label_id','center_id','label_category_id'
    ];
}
