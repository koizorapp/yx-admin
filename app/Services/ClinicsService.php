<?php
/**
 * Created by PhpStorm.
 * User: koizora
 * Date: 2017/10/30
 * Time: 14:52
 */

namespace App\Services;


use App\Models\Center;
use App\Models\Clinics;

class ClinicsService
{
    public static function getClinicsList()
    {
        $clinicsList = Clinics::get()->toArray();
        $data = collect($clinicsList)->groupBy('center_id')->toArray();
        $list = [];
        foreach ($data as $key => $value){
            $list[$key]['id'] = $key;
            $list[$key]['name'] = Center::where('id',$key)->value('name');
            $list[$key]['list'] = $value;
        }
        $list = array_values($list);
        return $list;
    }

    public static function addClinics($name,$center_id)
    {
        $result = new Clinics();
        $result->name = $name;
        $result->center_id = $center_id;
        return $result->save();
    }

    public static function editClinics($name,$center_id,$clinics_id)
    {
        $clinics = Clinics::find($clinics_id);
        $clinics->name = $name;
        $clinics->center_id = $center_id;
        return $clinics->save();
    }

    public static function delClinics($clinics_id)
    {
        return Clinics::destroy($clinics_id);
    }
}