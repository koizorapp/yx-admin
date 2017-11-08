<?php

namespace App\Http\Controllers\Admin;

use App\Services\CoreService;
use App\Services\ModuleService;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProjectController extends Controller
{
    /*
     * 项目列表
     */
    protected function getProjectList(Request $request)
    {
        CoreService::validate($request,[
            'current_page' => 'required | numeric | min:1'
        ]);
        $current_page = $request->get('current_page');
        $list         = ProjectService::getProjectList($current_page);
        return $this->json($list);
    }

    /*
     * 项目详情
     */
    protected function getDetail(Request $request)
    {
        CoreService::validate($request,[
            'project_id' => 'required | numeric | min:1'
        ]);

        $project_id = $request->get('project_id');
        $result = ProjectService::getDetail($project_id);
        return $result ? $this->json($result) : $this->json(ProjectService::getLastData(),ProjectService::getLastMsg(),ProjectService::getLastStatus());
    }

    /*
     * 添加项目
     */
    protected function addProject(Request $request)
    {
        CoreService::validate($request,[
            'name' => 'required',
            'center_id' => 'required | numeric | min:1',
            'category_id' => 'required | numeric | min:1',
            'module_list' => 'required | json'
        ]);
        $data['name']             = $request->get('name');
        $data['center_id']        = $request->get('center_id');
        $data['category_id']      = $request->get('category_id');
        $data['module_list']      = json_decode($request->get('module_list'),true);
        $data['description']      = $request->get('description');
        $data['time']             = $request->get('time');
        $data['considerations']   = $request->get('considerations');
        $data['adverse_reaction'] = $request->get('adverse_reaction');
        $data['remark']           = $request->get('remark');
        $data['working_part']     = json_decode($request->get('module_working_part_labels','[]'),true);
        $data['indications']      = json_decode($request->get('module_indications_labels','[]'),true);
        $data['market_price']     = $request->get('market_price');
        $data['member_price']     = $request->get('member_price');
        $result = ProjectService::addAndEditProject($data);
        return $result ? $this->json() : $this->json(ProjectService::getLastData(),ProjectService::getLastMsg(),ProjectService::getLastStatus());

    }

    /*
     * 编辑项目
     */
    protected function editProject(Request $request)
    {
        CoreService::validate($request,[
            'name' => 'required',
            'center_id' => 'required | numeric | min:1',
            'category_id' => 'required | numeric | min:1',
            'module_list' => 'required | json'
        ]);
        $data['name']             = $request->get('name');
        $data['center_id']        = $request->get('center_id');
        $data['category_id']      = $request->get('category_id');
        $data['module_list']      = json_decode($request->get('module_list'),true);
        $data['description']      = $request->get('description');
        $data['time']             = $request->get('time');
        $data['considerations']   = $request->get('considerations');
        $data['adverse_reaction'] = $request->get('adverse_reaction');
        $data['remark']           = $request->get('remark');
        $data['working_part']     = json_decode($request->get('module_working_part_labels','[]'),true);
        $data['indications']      = json_decode($request->get('module_indications_labels','[]'),true);
        $data['market_price']     = $request->get('market_price');
        $data['member_price']     = $request->get('member_price');
        $data['project_id']       = $request->get('project_id');
        $result = ProjectService::addAndEditProject($data);
        return $result ? $this->json() : $this->json(ProjectService::getLastData(),ProjectService::getLastMsg(),ProjectService::getLastStatus());
    }

    /*
     * 删除项目
     */
    protected function delProject(Request $request)
    {
        $project_id = $request->get('project_id');
        $result = ProjectService::delProject($project_id);
        return $result ? $this->json() : $this->json(ProjectService::getLastData(),ProjectService::getLastMsg(),ProjectService::getLastStatus());
    }

    /*
     * 搜索项目
     */
    protected function getProjectListForSearch(Request $request)
    {
        $center_id = $request->get('center_id');
        $label_category_id = $request->get('label_category_id');
        $label_key_word = $request->get('label_key_word');
        $result = ProjectService::getProjectListForSearch($center_id,$label_category_id,$label_key_word);
        return $result ? $this->json($result) : $this->json(ProjectService::getLastData(),ProjectService::getLastMsg(),ProjectService::getLastStatus());
    }

    /*
     * 验证模块
     */
    protected function getModuleDataForProject(Request $request)
    {
        CoreService::validate($request,[
            'module_list' => 'required | json'
        ]);
        $module_list = json_decode($request->get('module_list'),true);
        $result = ProjectService::getModuleDataForProject($module_list);
        return $result ? $this->json($result) : $this->json(ModuleService::getLastData(),ModuleService::getLastMsg(),ModuleService::getLastStatus());
    }
}
