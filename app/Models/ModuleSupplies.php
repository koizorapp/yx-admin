<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleSupplies extends Model
{
    use SoftDeletes;

    protected $table = 'module_supplies';

    protected $fillable = ['supplies_id','module_id'];
}
