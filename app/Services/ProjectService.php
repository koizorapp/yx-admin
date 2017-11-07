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
use App\Models\ModuleLabel;
use App\Models\Project;
use App\Models\ProjectLabel;
use App\Models\ProjectModule;
use Illuminate\Support\Facades\DB;

class ProjectService extends CoreService
{

    public static function addAndEditProject($data)
    {
//        $check_gender_age = self::checkGenderAge($data['module_equipment'],$data['module_supplies']);
//        if($check_gender_age == false){
//            return false;
//        }

        $check_name = Project::where('name',$data['name'])->where('center_id',$data['center_id'])->exists();
        if($check_name){
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
            Project::where('id',$data['project_id'])->delete();
            ProjectLabel::where('project_id',$data['project_id'])->delete();
            ProjectModule::where('project_id',$data['project_id'])->delete();
        }



        //项目标签表
        //读取模块的标签 禁忌症 作用功能
        $module_id_list = collect($data['project_module'])->pluck('id')->all();
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
                    'label_category_id' => $value['id'],
                ];
                $project_label = ProjectLabel::firstOrCreate($project_label_data);
                if(!$project_label){
                    return self::currentReturnFalse([],'添加项目错误 PROJECT-LABEL-ERROR-6000' . __LINE__);
                }
            }
        }

        //反写所选模块的 作用部位和适应症
        if(!empty($merge_project_labels)){
            foreach ($data['project_module'] as $k => $v){
                foreach ($merge_project_labels as $key => $value){
                    $module_label_data = [
                        'label_id' => $value['id'],
                        'module_id' => $v['id'],
                        'center_id' => $project->center_id,
                        'label_category_id' => $value['id'],
                    ];
                    $module_label = ModuleLabel::firstOrCreate($module_label_data);
                    if(!$module_label){
                        return self::currentReturnFalse([],'添加模块错误 PROJECT-MODULE-LABEL-ERROR-6000' . __LINE__);
                    }
                }
            }
        }

        //项目模块表  TODO 平行诊室
        if(!empty($data['project_module'])){
            foreach ($data['project_module'] as $key => $value){
                $project_module_data = [
                    'project_id'   => $project->id,
                    'module_id'    => $value['id']
                ];
                $project_module = ProjectModule::firstOrCreate($project_module_data);
                if(!$project_module){
                    return self::currentReturnFalse([],'添加项目错误 PROJECT-MODULE-ERROR-6000' . __LINE__);
                }
            }
        }

        DB::commit();
        return true;
    }

    public static function getModuleDataForProject($module_list)
    {

        $module_id_list = collect($module_list)->pluck('list')->collapse()->pluck('id')->all();
        $module_list = ModuleService::getModuleDetailForProject($module_id_list);
        return $module_list;
    }
}