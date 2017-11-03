<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleClinics extends Model
{
    use SoftDeletes;

    protected $table = 'module_clinics';

    protected $fillable = ['clinics_id','module_id'];
}
