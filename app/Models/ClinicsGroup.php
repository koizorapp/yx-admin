<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClinicsGroup extends Model
{
    use SoftDeletes;

    protected $table = 'clinics_group';

    protected $fillable = [
        'parent_clinics_id','clinics_id'
    ];
}
