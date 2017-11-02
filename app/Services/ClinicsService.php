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
        $center_list = Center::get(['id','name'])->toArray();

        foreach ($center_list as $key => $value){
            $center_list[$key]['list'] = $clinics_list = Clinics::where('center_id',$value['id'])->get(['id','name'])->toArray();
        }

        return $center_list;
    }

    public static function getClinicsListByCenterId($center_id)
    {
        $clinics_list =  Clinics::where('center_id',$center_id)->get(['id','name'])->toArray();
        return $clinics_list;
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