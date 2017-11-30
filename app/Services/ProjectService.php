<?php
/**
 * Created by PhpStorm.
 * User: koizora
 * Date: 2017/11/6
 * Time: 15:40
 */

namespace App\Services;


use App\Models\Category;
use App\Models\Center;
use App\Models\Label;
use App\Models\Module;
use App\Models\ModuleLabel;
use App\Models\Project;
use App\Models\ProjectLabel;
use App\Models\ProjectModule;
use Illuminate\Support\Facades\DB;

class ProjectService extends CoreService
{
    public static function getProjectList($current_page)
    {
        $columns = [
            'id',
            'code',
            'code_index',
            'name',
            'center_id',
            'category_id',
            'time'
        ];

        $result = Project::paginate(self::$limit,$columns,'page',$current_page)->toArray();

        foreach ($result['data'] as $key => $value)
        {
            $result['data'][$key]['center_name'] = Center::where('id',$value['center_id'])->value('name');
            $result['data'][$key]['category_name'] = Category::where('id',$value['category_id'])->value('name');
            $result['data'][$key]['code'] = $result['data'][$key]['code_index'] == 0 ? $result['data'][$key]['code'] : $result['data'][$key]['code'] . '_' . $result['data'][$key]['code_index'];
            unset($result['data'][$key]['center_id']);
            unset($result['data'][$key]['category_id']);
            unset($result['data'][$key]['code_index']);
        }

        $data['list'] = $result['data'];
        $data['total_page'] = $result['last_page'];
        $data['total_count'] = $result['total'];
        $data['current_page'] = $result['current_page'];
        return $data;
    }

    public static function getDetail($project_id)
    {
        $project = Project::find($project_id);
        if(!$project){
            return self::currentReturnFalse([],'该项目不存在.');
        }

        $project->code = $project->code . '_' . $project->code_index;
        $project->center_name = Center::where('id',$project->center_id)->value('name');
        unset($project->code_index);
        $project->category_name = Category::where('id',$project->category_id)->value('name');

        $module_list = ProjectModule::where('project_id',$project->id)->select(DB::raw('sort,module_id as id'))->get()->toArray();
        $module_list = $project_module_list = collect($module_list)->groupBy('sort')->toArray();

        foreach ($project_module_list as $key => $value){
            foreach ($value as $k => $v){
                if(count($value) > 1){
                    $project_module_list[$key][$k]['index'] = $key . '-' . ($k+1);
                }else{
                    $project_module_list[$key][$k]['index'] = $key;
                }
                $project_module_list[$key][$k]['name'] = Module::where('id',$v['id'])->value('name');
            }
        }
        $project->module_list = array_values($project_module_list);

        $module_list = self::getModuleDataForProject($module_list);
        $project->job_grades = $module_list['job_grades'];
        $project->module_equipment = $module_list['module_equipment'];
        $project->module_supplies = $module_list['module_supplies'];
        $project->module_clinics = $module_list['module_clinics'];
        $project->whether_medical_name = $module_list['whether_medical_name'];
        $project->module_working_part_labels = $module_list['module_working_part_labels'];
        $project->module_contraindications_labels = $module_list['module_contraindications_labels'];
        $project->module_indications_labels = $module_list['module_indications_labels'];
        $project->module_function_labels = $module_list['module_function_labels'];
        $project->gender_limit_name = $module_list['gender_limit_name'];
        $project->age_limit = $module_list['age_limit'];
        $project->expected_cost = $module_list['expected_cost'] == 0 ? '' : $module_list['expected_cost'];
        $project->module_list_view = collect($project->module_list)->collapse()->all();

        //上一条
        $last_id = Project::where('id','<',$project->id)->max('id');
        $project->last_id = $last_id;
        //下一条
        $next_id = Project::where('id','>',$project->id)->min('id');
        $project->next_id = $next_id;

        //模块注意事项 不良反应 备注
        $other_data = ModuleService::getModuleOtherData(collect($module_list)->collapse()->pluck('id')->all(),2,$project_id);
        $project->show_considerations = $other_data['considerations'];
        $project->show_adverse_reaction = $other_data['adverse_reaction'];
        $project->show_remark = $other_data['remark'];

        //价格
        $project->market_price = $project->market_price == 0 ? '' : $project->market_price;
        $project->member_price = $project->member_price == 0 ? '' : $project->member_price;
        return $project->toArray();
    }

    public static function addAndEditProject($data)
    {
        //TODO
//        $check_gender_age = self::checkGenderAge($data['module_equipment'],$data['module_supplies']);
//        if($check_gender_age == false){
//            return false;
//        }

        $check_name = Project::where('name',$data['name'])->where('center_id',$data['center_id'])->exists();
        if($check_name && !isset($data['project_id'])){
            return self::currentReturnFalse([],'项目名称重复');
        }

        DB::beginTransaction();

        if(isset($data['project_id'])){
            $project = Project::find($data['project_id']);
        }else{
            $project = new Project();
        }
        //项目表
        $maxIndex = Project::where('center_id',$data['center_id'])->max('code_index');
        $project->name             = $data['name'];
        if(!isset($data['module_id'])){
            $project->code_index   = $maxIndex+1;//str_pad($maxIndex+1,3,"0",STR_PAD_LEFT);
        }
        $project->code             = Center::where('id',$data['center_id'])->value('code') . '_' .Category::where('id',$data['category_id'])->value('code');
        $project->center_id        = $data['center_id'];
        $project->category_id      = $data['category_id'];
        $project->time             = $data['time'] ? $data['time'] : 0 ;
        $project->market_price     = $data['market_price'] ? $data['market_price'] : 0.00;
        $project->member_price     = $data['member_price'] ? $data['member_price'] : 0.00;
        $project->considerations   = $data['considerations'] ? $data['considerations'] : '';
        $project->description      = $data['description'] ? $data['description'] : '';
        $project->adverse_reaction = $data['adverse_reaction'] ? $data['adverse_reaction'] : '';
        $project->remark           = $data['remark'] ? $data['remark'] : '';

        $save_project = $project->save();


        if(!$save_project){
            return self::currentReturnFalse([],'添加项目错误 PROJECT-ERROR-6000' . __LINE__);
        }

        if(isset($data['project_id'])){
            ProjectLabel::where('project_id',$data['project_id'])->delete();
            ProjectModule::where('project_id',$data['project_id'])->delete();
        }



        //项目标签表
        //读取模块的标签 禁忌症 作用功能
        $module_id_list = collect($data['module_list'])->collapse()->pluck('id')->all();
        $module_label_list = ModuleService::getModuleLabelList($module_id_list);
        $module_label_list = collect($module_label_list)->collapse()->all();
        $merge_project_labels = array_merge($data['indications'],$data['working_part']);
        $merge_labels = array_merge($merge_project_labels,$module_label_list);
        $merge_labels = collect($merge_labels)->unique('id')->all();

        if(!empty($merge_labels)){
            foreach ($merge_labels as $key => $value){
                $project_label_data = [
                    'label_id' => $value['id'],
                    'project_id' => $project->id,
                    'center_id' => $project->center_id,
                    'label_category_id' => $value['label_category_id'],
                ];
                $project_label = ProjectLabel::firstOrCreate($project_label_data);
                if(!$project_label){
                    return self::currentReturnFalse([],'添加项目错误 PROJECT-LABEL-ERROR-6000' . __LINE__);
                }
            }
        }

        //反写所选模块的 作用部位和适应症
        if(!empty($merge_project_labels)){
            foreach ($module_id_list as $k => $v){
                foreach ($merge_project_labels as $key => $value){
                    $module_label_data = [
                        'label_id' => $value['id'],
                        'module_id' => $v,
                        'center_id' => $project->center_id,
                        'label_category_id' => $value['label_category_id'],
                    ];
                    $module_label = ModuleLabel::firstOrCreate($module_label_data);
                    if(!$module_label){
                        return self::currentReturnFalse([],'添加模块错误 PROJECT-MODULE-LABEL-ERROR-6000' . __LINE__);
                    }
                }
            }
        }

        //项目模块表
        if(!empty($data['module_list'])){
            foreach ($data['module_list'] as $key => $value){
                foreach ($value as $k => $v){
                    $project_module_data = [
                        'project_id'   => $project->id,
                        'module_id'    => $v['id'],
                        'sort'         => $key + 1
                    ];
                    $project_module = ProjectModule::firstOrCreate($project_module_data);
                    if(!$project_module) {
                        return self::currentReturnFalse([], '添加项目错误 PROJECT-MODULE-ERROR-6000' . __LINE__);
                    }

                }
            }
        }

        //更新项目的 注意事项 不良反应 备注
       /* $module = Module::whereIn('id',$module_id_list)->get(['considerations','adverse_reaction','remark'])->toArray();
        $module_considerations = collect($module)->pluck('considerations')->implode(',','considerations');
        $module_adverse_reaction = collect($module)->pluck('adverse_reaction')->implode(',','adverse_reaction');
        $module_remark =collect($module)->pluck('remark')->implode(',','remark');

        if(!empty($data['considerations'])){
            $data['considerations'] = $data['considerations'] . '|';
        }

        if(!empty($data['adverse_reaction'])){
            $data['adverse_reaction'] = $data['adverse_reaction'] . '|';
        }

        if(!empty($data['remark'])){
            $data['remark'] = $data['remark'] . '|';
        }
        $project = Project::find($project->id);
        $project->considerations     = $data['considerations'] . trim($module_considerations,',') ;
        $project->adverse_reaction   = $data['adverse_reaction'] . trim($module_adverse_reaction,',');
        $project->remark             = $data['remark'] . trim($module_remark,',');
        $project->save();*/

        DB::commit();

        return true;
    }

    public static function delProject($project_id)
    {
        Project::where('id',$project_id)->delete();
        ProjectLabel::where('project_id',$project_id)->delete();
        ProjectModule::where('project_id',$project_id)->delete();
        return true;
    }

    public static function getModuleDataForProject($module_list)
    {
        $module_id_list = collect($module_list)->collapse()->pluck('id')->all();
        $module_list = ModuleService::getModuleDetailForProject($module_id_list);
        return $module_list;
    }

    public static function getProjectListForSearch($center_id,$label_category_id,$label_key_word)
    {
        $label = [];
        if($label_key_word){
            $label = Label::where('name','like','%'.$label_key_word.'%')->pluck('id')->toArray();
        }

        $project_id_list = new ProjectLabel();
        if($center_id){
            $project_id_list = $project_id_list->where('center_id',$center_id);
        }
        if($label_category_id){
            $project_id_list = $project_id_list->where('label_category_id',$label_category_id);
        }
        if($label){
            $project_id_list = $project_id_list->whereIn('label_id',$label);
        }
        $project_id_list = $project_id_list->groupBy('project_id')->pluck('project_id')->toArray();

        if(empty($project_id_list)){
            return [];
        }

        $columns = [
            'id',
            'code',
            'code_index',
            'name',
            'center_id',
            'category_id',
            'time'
        ];
        $result = Project::whereIn('id',$project_id_list)->get($columns)->toArray();

        foreach ($result as $key => $value)
        {
            $result[$key]['center_name'] = Center::where('id',$value['center_id'])->value('name');
            $result[$key]['category_name'] = Category::where('id',$value['category_id'])->value('name');
            $result[$key]['code'] = $result[$key]['code_index'] == 0 ? $result[$key]['code'] : $result[$key]['code'] . '_' . $result[$key]['code_index'];
            unset($result[$key]['center_id']);
            unset($result[$key]['category_id']);
            unset($result[$key]['code_index']);
        }

        return $result;
    }
}