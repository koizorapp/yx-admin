<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clinics extends Model
{
    use SoftDeletes;

    protected $table = 'clinics';

    protected $fillable = [
        'name','center_id'
    ];
}
