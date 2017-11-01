<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuppliesLabel extends Model
{
    use SoftDeletes;

    protected $table = 'supplies_labels';

    protected $fillable = [
        'supplies_id','label_id','center_id','label_category_id'
    ];
}
