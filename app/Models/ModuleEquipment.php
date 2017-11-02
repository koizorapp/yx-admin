<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleEquipment extends Model
{
    use SoftDeletes;

    protected $table = 'module_equipments';

    protected $fillable = ['equipment_id','module_id'];
}
