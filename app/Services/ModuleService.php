<?php
/**
 * Created by PhpStorm.
 * User: koizora
 * Date: 2017/11/1
 * Time: 18:03
 */

namespace App\Services;


use App\Models\Center;
use App\Models\Clinics;
use App\Models\ClinicsGroup;
use App\Models\Equipment;
use App\Models\Label;
use App\Models\Module;
use App\Models\ModuleClinics;
use App\Models\ModuleEquipment;
use App\Models\ModuleJobGrade;
use App\Models\ModuleLabel;
use App\Models\ModuleSupplies;
use App\Models\Personnel;
use App\Models\Supplies;
use Illuminate\Support\Facades\DB;

class ModuleService extends CoreService
{
    public static function getModuleList($current_page)
    {
        $columns = [
            'id',
            'code',
            'code_index',
            'name',
            'name_index',
            'center_id',
            'service_time',
            'service_after_time'

        ];
        $result = Module::paginate(self::$limit,$columns,'page',$current_page)->toArray();

        foreach ($result['data'] as $key => $value)
        {
            $result['data'][$key]['job_grade_list'] = collect(JobGradeService::getJobGradeByModuleId($value['id']))->pluck('name')->implode(',','name');
            $result['data'][$key]['center_name'] = Center::where('id',$value['center_id'])->value('name');
            $result['data'][$key]['name'] = $result['data'][$key]['name_index'] == 0 ? $result['data'][$key]['name'] : $result['data'][$key]['name'] . '_' . $result['data'][$key]['name_index'];
            $result['data'][$key]['code'] = $result['data'][$key]['code'] . '_' . $result['data'][$key]['code_index'];
            $result['data'][$key]['time'] = $result['data'][$key]['service_after_time'] ?  $result['data'][$key]['service_time'] . '+' . $result['data'][$key]['service_after_time'] . '分钟' : $result['data'][$key]['service_time'] . '分钟';
            unset($result['data'][$key]['center_id']);
            unset($result['data'][$key]['name_index']);
            unset($result['data'][$key]['code_index']);
            unset($result['data'][$key]['service_time']);
            unset($result['data'][$key]['service_after_time']);
        }

        $data['list'] = $result['data'];
        $data['total_page'] = $result['last_page'];
        $data['total_count'] = $result['total'];
        $data['current_page'] = $result['current_page'];
        return $data;
    }

    public static function getDetail($module_id)
    {
        $module = Module::find($module_id);
        $module->code = $module->code . '_' .str_pad($module->code_index,3,"0",STR_PAD_LEFT);
        unset($module->code_index);
        $module->name = $module->name_index == 0 ? $module->name : $module->name . '_' . $module->name_index;
        unset($module->name_index);
        $module->center_name = Center::where('id',$module->center_id)->value('name');
        $module->job_grades = self::getModuleJobGradesList([$module->id]);
        $module->personnel_list = self::getPersonnelList($module->job_grades);
        $module->module_equipment = self::getModuleEquipmentList([$module->id]);
        $module->module_supplies  = self::getModuleSuppliesList([$module->id]);
        $module->module_clinics   = self::getModuleClinicsList([$module->id]);
        $module->whether_medical_name  = $module->whether_medical ? '是' : '否';
        $module_label = self::getModuleLabelList([$module->id]);
        $module->module_working_part_labels      = isset($module_label[4]) ? $module_label[4] : [];
        $module->module_contraindications_labels = isset($module_label[2]) ? $module_label[2] : [];
        $module->module_indications_labels    = isset($module_label[1]) ? $module_label[1] : [];
        $module->module_function_labels          = isset($module_label[3]) ? $module_label[3] : [];
        $module->gender_limit_name               = self::$gender_data[$module->gender_limit];

        //年龄限制处理
        $module->age_limit = self::ageLimit($module->min_age_limit,$module->max_age_limit);


        //上一条
        $last_id = Module::where('id','<',$module->id)->max('id');
        $module->last_id = $last_id;
        //下一条
        $next_id = Module::where('id','>',$module->id)->min('id');
        $module->next_id = $next_id;


        return $module->toArray();
        //录入加模块和 TODO 详情  注意事项 不良反应 写入的时候添加

    }

    public static function addAndEditModule($data)
    {
        $check_gender_age = self::checkGenderAge($data['module_equipment'],$data['module_supplies'],$data['center_id']);
        if($check_gender_age == false){
            return false;
        }

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
        if(!isset($data['module_id'])){
            $module->code_index         = $maxIndex+1;//str_pad($maxIndex+1,3,"0",STR_PAD_LEFT);
        }
        $module->code               = Center::where('id',$data['center_id'])->value('code');
        $module->name_index         = $name_index;
        $module->center_id          = $data['center_id'];
        $module->service_time       = $data['service_time'];
        $module->service_after_time = $data['service_after_time'];
        $module->whether_medical    = $data['whether_medical'];
        $module->min_age_limit      = $data['min_age_limit'];
        $module->max_age_limit      = $data['max_age_limit'];
        $module->gender_limit       = $data['gender_limit'];
//        $module->considerations     = $data['considerations'];//TODO
//        $module->adverse_reaction   = $data['adverse_reaction'];//TODO
        $module->description        = $data['description'];
        $module->expected_cost      = $data['expected_cost'];
        $module->remark             = $data['remark'];

        $save_module = $module->save();


        if(!$save_module){
            return self::currentReturnFalse([],'添加模块错误 MODULE-ERROR-6000' . __LINE__);
        }

        if(isset($data['module_id'])){
            ModuleJobGrade::where('module_id',$data['module_id'])->delete();
            ModuleLabel::where('module_id',$data['module_id'])->delete();
            ModuleEquipment::where('module_id',$data['module_id'])->delete();
            ModuleSupplies::where('module_id',$data['module_id'])->delete();
            ModuleClinics::where('module_id',$data['module_id'])->delete();
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
        $mergeLabels = array_merge($data['contraindications'],$data['function']);
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
            $equipment_list = collect($data['module_equipment'])->pluck('id')->all();
            $equipment_string = Equipment::whereIn('id',$equipment_list)->get(['considerations','adverse_reaction'])->toArray();
            $equipment_considerations = collect($equipment_string)->implode(',','considerations');
            $equipment_adverse_reaction = collect($equipment_string)->implode(',','adverse_reaction');
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
            $supplies_list = collect($data['module_supplies'])->pluck('id')->all();
            $supplies_string = Equipment::whereIn('id',$supplies_list)->get(['considerations','adverse_reaction'])->toArray();
            $supplies_considerations = collect($supplies_string)->implode(',','considerations');
            $supplies_adverse_reaction = collect($supplies_string)->implode(',','adverse_reaction');
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

        //更新模块的 注意事项 不良反应
        $module = Module::find($module->id);
        $module->considerations     = $data['considerations'] . '|' . $equipment_considerations . '|' . $supplies_considerations;
        $module->adverse_reaction   = $data['adverse_reaction'] . '|' . $equipment_adverse_reaction . '|' . $supplies_adverse_reaction;
        $module->save();



        //模块诊室表 TODO 需要做特殊处理
        if(!empty($data['module_clinics'])){
            $clinics_mark = [];
            $clinics_id   = [];
            foreach ($data['module_clinics'] as $key => $value){
                if($value['mark'] == 1){
                    $clinics_mark[] = $value;
                }else{
                    $clinics_id[] = $value['id'];
                }
            }

            $clinics_mark_id = collect($clinics_mark)->pluck('id')->all();
            $clinics_group_id = [];
            if($clinics_mark_id){
                //查询平行诊室下的诊室列表
                $clinics_group_id = ClinicsGroup::whereIn('parent_clinics_id',$clinics_mark_id)->groupBy('clinics_id')->pluck('clinics_id')->toArray();

            }
            foreach ($clinics_id as $key => $value){
                if(in_array($value,$clinics_group_id)){
                    unset($clinics_id[$key]);
                }
            }

            $clinics_id = array_unique(array_merge($clinics_id,$clinics_mark_id));

            if(!empty($clinics_id)){
                foreach ($clinics_id as $key => $value){
                    $module_clinics_data = [
                        'clinics_id' => $value,
                        'module_id'  => $module->id
                    ];
                    $module_clinics = ModuleClinics::firstOrCreate($module_clinics_data);
                    if(!$module_clinics){
                        return self::currentReturnFalse([],'添加模块错误 MODULE-CLINICS-ERROR-6000' . __LINE__);
                    }
                }
            }

        }

        DB::commit();
        return true;
    }

    public static function delModule($module_id)
    {
        Module::where('id',$module_id)->delete();
        ModuleLabel::where('module_id',$module_id)->delete();
        ModuleEquipment::where('module_id',$module_id)->delete();
        ModuleSupplies::where('module_id',$module_id)->delete();
        ModuleClinics::where('module_id',$module_id)->delete();
        ModuleJobGrade::where('module_id',$module_id)->delete();
        return true;
    }

    public static function getModuleListForSearch($center_id,$label_category_id,$label_key_word)
    {
        $label = [];
        if($label_key_word){
            $label = Label::where('name','like','%'.$label_key_word.'%')->pluck('id')->toArray();
        }

        $module_id_list = new ModuleLabel();
        if($center_id){
            $module_id_list = $module_id_list->where('center_id',$center_id);
        }
        if($label_category_id){
            $module_id_list = $module_id_list->where('label_category_id',$label_category_id);
        }
        if($label){
            $module_id_list = $module_id_list->whereIn('label_id',$label);
        }
        $module_id_list = $module_id_list->groupBy('module_id')->pluck('module_id')->toArray();

        if(empty($module_id_list)){
            return [];
        }

        $columns = [
            'id',
            'code',
            'code_index',
            'name',
            'name_index',
            'center_id',
            'service_time',
            'service_after_time'
        ];
        $result = Module::whereIn('id',$module_id_list)->get($columns)->toArray();

        foreach ($result as $key => $value)
        {
            $result[$key]['job_grade_list'] = collect(JobGradeService::getJobGradeByModuleId($value['id']))->pluck('name')->implode(',','name');
            $result[$key]['center_name'] = Center::where('id',$value['center_id'])->value('name');
            $result[$key]['name'] = $result[$key]['name_index'] == 0 ? $result[$key]['name'] : $result[$key]['name'] . '_' . $result[$key]['name_index'];
            $result[$key]['code'] = $result[$key]['code'] . '_' . $result[$key]['code_index'];
            $result[$key]['time'] = $result[$key]['service_after_time'] ?  $result[$key]['service_time'] . '+' . $result[$key]['service_after_time'] . '分钟' : $result[$key]['service_time'] . '分钟';
            unset($result[$key]['center_id']);
            unset($result[$key]['name_index']);
            unset($result[$key]['code_index']);
            unset($result[$key]['service_time']);
            unset($result[$key]['service_after_time']);
        }

        return $result;
    }

    public static function checkGenderAge($equipment_list,$supplies_list,$center_id)
    {
        if(empty($supplies_list) && empty($equipment_list)){
            return ['gender' => 0 ,'min_age_limit' => '' ,'max_age_limit' => ''];
        }
        $equipment_id_list = collect($equipment_list)->pluck('id')->all();
        $supplies_id_list  = collect($supplies_list)->pluck('id')->all();
        $equipment = Equipment::whereIn('id',$equipment_id_list)->get(['min_age_limit','max_age_limit','gender_limit'])->toArray();
        $supplies  = Supplies::whereIn('id',$supplies_id_list)->get(['min_age_limit','max_age_limit','gender_limit'])->toArray();

        $limit = array_merge($equipment,$supplies);
        $min_age_limit = collect($limit)->pluck('min_age_limit')->all();
        $max_age_limit = collect($limit)->pluck('max_age_limit')->all();
        $gender_limit = collect($limit)->pluck('gender_limit')->unique()->all();

        //TODO 处理性别限制
        $min_age = max($min_age_limit);
        $max_age = min($max_age_limit);

        $gender_sum = collect($gender_limit)->sum();
        $gender = $gender_sum; //TODO 性别限制的值不能修改
        if($gender_sum == 3){
            return self::currentReturnFalse([],'性别限制冲突,请修改设备或者用品的组合.');
        }

        if($min_age > $max_age){
            return self::currentReturnFalse([],'年龄限制冲突,请修改设备或者用品的组合.');
        }

        if($max_age == 151){
            $max_age = '';
        }

        //返回诊室列表
        $clinics_center_list = Clinics::where('center_id',$center_id)->get(['id','name'])->toArray();
        $clinics_equipment_list = Equipment::whereIn('id',$equipment_id_list)->pluck('clinics_id')->toArray();
        foreach ($clinics_center_list as $key => $value){
            $clinics_center_list[$key]['status'] = 0;
            if(in_array($value['id'],$clinics_equipment_list)){
                $clinics_center_list[$key]['status'] = 1;
            }
        }


        return ['gender' => $gender ,'min_age_limit' => $min_age ,'max_age_limit' => $max_age ,'clinics_list' => $clinics_center_list];
    }

    public static function getModuleJobGradesList($module_id_list)
    {
        $list = ModuleJobGrade::leftJoin('job_grade','job_grade.id','=','module_job_grades.job_grade_id')->whereIn('module_id',$module_id_list)->select(DB::raw('yx_job_grade.id,yx_job_grade.name'))->get()->toArray();
        return $list;
    }

    public static function getModuleEquipmentList($module_id_list)
    {
        $list = ModuleEquipment::leftJoin('equipments','equipments.id','=','module_equipments.equipment_id')->whereIn('module_id',$module_id_list)->select(DB::raw('yx_equipments.id,yx_equipments.name,yx_equipments.name_index'))->get()->toArray();
        foreach ($list as $key => $value){
            $list[$key]['name'] = $value['name_index'] == 0 ? $value['name'] : $value['name'] . '_' . $value['name_index'];
            unset($list[$key]['name_index']);
        }
        return $list;
    }

    public static function getModuleSuppliesList($module_id_list)
    {
        $list = ModuleSupplies::leftJoin('supplies','supplies.id','=','module_supplies.supplies_id')->whereIn('module_id',$module_id_list)->select(DB::raw('yx_supplies.id,yx_supplies.name,yx_supplies.name_index'))->get()->toArray();
        foreach ($list as $key => $value){
            $list[$key]['name'] = $value['name_index'] == 0 ? $value['name'] : $value['name'] . '_' . $value['name_index'];
            unset($list[$key]['name_index']);
        }
        return $list;
    }

    public static function getModuleClinicsList($module_id_list)
    {
        $list = ModuleClinics::leftJoin('clinics','clinics.id','=','module_clinics.clinics_id')->whereIn('module_id',$module_id_list)->select(DB::raw('yx_clinics.id,yx_clinics.name'))->get()->toArray();
        return $list;
    }

    public static function getModuleLabelList($module_id_list)
    {
        $list = ModuleLabel::leftJoin('labels','labels.id','=','module_labels.label_id')->whereIn('module_id',$module_id_list)->select(DB::raw('yx_labels.id,yx_labels.name,yx_labels.label_category_id'))->get()->toArray();
        $list = collect($list)->groupBy('label_category_id')->toArray();
        return $list;
    }

    public static function getPersonnelList($job_grade_list)
    {
        $job_grade_id_list = collect($job_grade_list)->pluck('id')->all();
        $list = Personnel::whereIn('job_grade_id',$job_grade_id_list)->get(['id','name'])->toArray();
        return $list;
    }

    public static function ageLimit($min_age,$max_age)
    {
        $age_limit = '不限';
        if($min_age == 0 && $max_age < 151){
            $age_limit = "≤$max_age";
        }else if($min_age > 0 && $max_age < 151){
            $age_limit = "$min_age 至 $max_age 岁";
        }else if($min_age > 0 && $max_age == 151){
            $age_limit = "≥$min_age";
        }
        return $age_limit;
    }
}