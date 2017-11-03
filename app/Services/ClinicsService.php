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
use App\Models\ClinicsGroup;
use Illuminate\Support\Facades\DB;

class ClinicsService extends CoreService
{
    public static function getClinicsList()
    {
        $center_list = Center::get(['id','name'])->toArray();

        foreach ($center_list as $key => $value){
            $clinics = Clinics::where('center_id',$value['id'])->get(['id','name','mark'])->toArray();
            $clinics_group = collect($clinics)->groupBy('mark')->toArray();

            $center_list[$key]['clinics_list'] = isset($clinics_group[0]) ? $clinics_group[0] : [];
            $center_list[$key]['parallel_clinics_list'] = [];

            if(isset($clinics_group[1])){
                foreach ($clinics_group[1] as $k => $v){
                    $clinics_group_list = ClinicsGroup::leftJoin('clinics','clinics.id','=','clinics_group.clinics_id')
                        ->where('parent_clinics_id',$v['id'])
                        ->pluck('name')->toArray();
                    $clinics_group_list = collect($clinics_group_list)->implode(',');
                    $clinics_group[1][$k]['name'] = $v['name'] . '(' . $clinics_group_list  . ')';

                }
                $center_list[$key]['parallel_clinics_list']  = $clinics_group[1];
            }
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

    public static function addParallelClinics($center_id,$name,$clinics_list)
    {
        DB::beginTransaction();
        $result = new Clinics();
        $result->name = $name;
        $result->center_id = $center_id;
        $result->mark = 1;
        $clinics = $result->save();

        if(!$clinics){
            return self::currentReturnFalse([],'添加诊室错误 CLINICS-LABEL-ERROR-6000' . __LINE__);
        }

        foreach ($clinics_list as $key => $value){
            $clinics_group_data = [
                'parent_clinics_id' => $result->id,
                'clinics_id' => $value['id']
            ];

            $clinics_group = ClinicsGroup::firstOrCreate($clinics_group_data);
            if(!$clinics_group){
                return self::currentReturnFalse([],'添加诊室错误 CLINICS-GROUP-LABEL-ERROR-6000' . __LINE__);
            }
        }

        DB::commit();
        return true;
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