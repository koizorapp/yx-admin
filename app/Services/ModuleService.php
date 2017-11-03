<?php
/**
 * Created by PhpStorm.
 * User: koizora
 * Date: 2017/11/1
 * Time: 18:03
 */

namespace App\Services;


use App\Models\Center;
use App\Models\Module;
use App\Models\ModuleClinics;
use App\Models\ModuleEquipment;
use App\Models\ModuleJobGrade;
use App\Models\ModuleLabel;
use App\Models\ModuleSupplies;
use Illuminate\Support\Facades\DB;

class ModuleService extends CoreService
{
    public static function getModuleList()
    {

    }

    public static function getDetail()
    {

    }

    public static function addAndEditModule($data)
    {
        $check_name = Module::where('name',$data['name'])->where('center_id',$data['center_id'])->max('name_index');
        $name_index = 0;
        if($check_name){
            $name_index = $check_name + 1;
        }
        DB::beginTransaction();

        if(isset($data['module_id'])){
            $module = Module::find($data['module_id']);
        }else{
            $module = new Module();
        }
        //模块表
        $maxIndex = Module::where('center_id',$data['center_id'])->max('code_index');
        $module->name               = $data['name'];
        $module->code_index         = str_pad($maxIndex+1,3,"0",STR_PAD_LEFT);
        $module->code               = Center::where('id',$data['center_id'])->value('code');
        $module->name_index         = $name_index;
        $module->center_id          = $data['center_id'];
        $module->service_time       = $data['service_time'];
        $module->service_after_time = $data['service_after_time'];
        $module->whether_medical    = $data['whether_medical'];
        $module->min_age_limit      = $data['min_age_limit'];
        $module->max_age_limit      = $data['max_age_limit'];
        $module->gender_limit       = $data['gender_limit'];
        $module->considerations     = $data['considerations'];
        $module->adverse_reaction   = $data['adverse_reaction'];
        $module->description        = $data['description'];
        $module->expected_cost      = $data['expected_cost'];
        $module->remark             = $data['remark'];

        $save_module = $module->save();


        if(!$save_module){
            return self::currentReturnFalse([],'添加模块错误 MODULE-ERROR-6000' . __LINE__);
        }

        //模块职位等级关联表
        if($data['job_grades']){
            foreach ($data['job_grades'] as $key => $value){
                $module_job_grade_data = [
                    'job_grade_id' => $value['id'],
                    'module_id'    => $module->id
                ];
                $module_job_grade = ModuleJobGrade::firstOrCreate($module_job_grade_data);
                if(!$module_job_grade){
                    return self::currentReturnFalse([],'添加模块错误 MODULE—JOB_GRADE-LABEL-ERROR-6000' . __LINE__);
                }
            }
        }

        //模块标签表
        $mergeLabels = array_merge($data['contraindications'],$data['working_part']);
        if(!empty($mergeLabels)){
            foreach ($mergeLabels as $key => $value){
                $module_label_data = [
                    'label_id' => $value['id'],
                    'module_id' => $module->id,
                    'center_id' => $module->center_id,
                    'label_category_id' => $value['id'],
                ];
                $module_label = ModuleLabel::firstOrCreate($module_label_data);
                if(!$module_label){
                    return self::currentReturnFalse([],'添加模块错误 MODULE-LABEL-ERROR-6000' . __LINE__);
                }
            }
        }

        //模块设备表
        if(!empty($data['module_equipment'])){
            foreach ($data['module_equipment'] as $key => $value){
                $module_equipment_data = [
                    'equipment_id' => $value['id'],
                    'module_id'    => $module->id
                ];
                $module_equipment = ModuleEquipment::firstOrCreate($module_equipment_data);
                if(!$module_equipment){
                    return self::currentReturnFalse([],'添加模块错误 MODULE-EQUIPMENT-ERROR-6000' . __LINE__);
                }
            }
        }

        //模块用品表
        if(!empty($data['module_supplies'])){
            foreach ($data['module_supplies'] as $key => $value){
                $module_supplies_data = [
                    'supplies_id'  => $value['id'],
                    'module_id'    => $module->id
                ];
                $module_supplies = ModuleSupplies::firstOrCreate($module_supplies_data);
                if(!$module_supplies){
                    return self::currentReturnFalse([],'添加模块错误 MODULE-SUPPLIES-ERROR-6000' . __LINE__);
                }
            }
        }


        //模块诊室表 TODO 需要做特殊处理


        if(!empty($data['module_clinics'])){
            foreach (($data['module_clinics']) as $key => $value){
                $module_clinics_data = [
                    'clinics_id' => $value['id'],
                    'module_id'  => $module->id
                ];
                $module_clinics = ModuleClinics::firstOrCreate($module_clinics_data);
                if(!$module_clinics){
                    return self::currentReturnFalse([],'添加模块错误 MODULE-CLINICS-ERROR-6000' . __LINE__);
                }
            }
        }

        DB::commit();
        return true;

        //中心设备列表
        //中心用品列表
        //录入加模块和












    }

    public static function delModule()
    {

    }

    public static function getModuleListForSearch()
    {

    }

    public static function checkGenderAge($equipment_list,$supplies_list)
    {

    }
}