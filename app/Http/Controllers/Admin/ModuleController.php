<?php

namespace App\Http\Controllers\Admin;

use App\Services\JobGradeService;
use App\Services\ModuleService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ModuleController extends Controller
{
    /*
     * 模块列表
     */
    protected function getModuleList(Request $request)
    {

    }

    /*
     * 模块详情
     */
    protected function getDetail(Request $request)
    {

    }

    /*
     * 添加模块
     */
    protected function addModule(Request $request)
    {
        $data['name']               = $request->get('name');
        $data['center_id']          = $request->get('center_id');
        $data['job_grades']         = json_decode($request->get('job_grades'),true); //TODO 执行人等级 JSON
        $data['service_time']       = $request->get('service_time');
        $data['service_after_time'] = $request->get('service_after_time');
        $data['whether_medical']    = $request->get('whether_medical');
        $data['min_age_limit']      = $request->get('min_age_limit');
        $data['max_age_limit']      = $request->get('max_age_limit');
        $data['gender_limit']       = $request->get('gender_limit');
        $data['considerations']     = $request->get('considerations','');
        $data['adverse_reaction']   = $request->get('adverse_reaction','');
        $data['description']        = $request->get('description','');
        $data['remark']             = $request->get('remark','');
        $data['expected_cost']      = $request->get('expected_cost');
        $data['working_part']       = json_decode($request->get('module_working_part_labels','[]'),true);//TODO 作用部位 JSON
        $data['contraindications']  = json_decode($request->get('module_contraindications_labels','[]'),true);//TODO 禁忌症 JSON
        $data['module_equipment']   = json_decode($request->get('module_equipment','[]'),true);//TODO 设备 JSON
        $data['module_supplies']    = json_decode($request->get('module_supplies','[]'),true);//TODO 用品 JSON
        $result = ModuleService::addAndEditModule($data);
        return $result ? $this->json($result) : $this->json(ModuleService::getLastData(),ModuleService::getLastMsg(),ModuleService::getLastStatus());
    }

    /*
     * 编辑模块
     */
    protected function editModule(Request $request)
    {

    }

    /*
     * 删除模块
     */
    protected function delModule(Request $request)
    {

    }

    /*
     * 搜索模块
     */
    protected function getModuleListForSearch(Request $request)
    {

    }

    /*
     * 执行人等级列表
     */
    protected function getJobGrade(Request $request)
    {
        $center_id = $request->get('center_id');
        $result = JobGradeService::getJobGrade($center_id);
        return $this->json($result);
    }

    /*
     * 验证 性别 年龄
     */
    protected function checkGenderAge(Request $request)
    {
        $equipment_list = $request->get('equipment_list');
        $supplies_list  = $request->get('supplies_list');
        $result = ModuleService::checkGenderAge($equipment_list,$supplies_list);
        return $this->json($result);

    }














}
