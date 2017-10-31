<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentLabel extends Model
{
    use SoftDeletes;

    protected $table = 'equipment_labels';

    protected $fillable = [
        'equipment_id','label_id','center_id','label_category_id'
    ];
}
