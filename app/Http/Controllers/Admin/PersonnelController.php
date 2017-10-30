<?php

namespace App\Http\Controllers\Admin;

use App\Services\PersonnelService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PersonnelController extends Controller
{
    /*
     * 人员列表
     */
    protected function getPersonnelList(Request $request)
    {
        $list = PersonnelService::getPersonnelList();
        return $this->json($list);
    }

    /*
     * 添加人员
     */
    protected function addPersonnel(Request $request)
    {
        $name      = $request->get('name');
        $center_id = $request->get('center_id');
        $job_grade_name = $request->get('job_grade_name');
        $hourly_wage = $request->get('hourly_wage');
        $result = PersonnelService::addPersonnel($name,$center_id,$job_grade_name,$hourly_wage);
        return $result ? $this->json() : $this->json([],'error',5000);
    }

    /*
     * 编辑人员
     */
    protected function editPersonnel(Request $request)
    {
        $name      = $request->get('name');
        $center_id = $request->get('center_id');
        $job_grade_name = $request->get('job_grade_name');
        $hourly_wage = $request->get('hourly_wage');
        $personnel_id = $request->get('personnel_id');
        $result = PersonnelService::editPersonnel($name,$center_id,$job_grade_name,$hourly_wage,$personnel_id);
        return $result ? $this->json() : $this->json([],'error',5000);
    }

    /*
     * 删除人员
     */
    protected function delPersonnel(Request $request)
    {
        $personnel_id = $request->get('personnel_id');
        $result = PersonnelService::delPersonnel($personnel_id);
        return $result ? $this->json() : $this->json([],'error',5000);
    }
}
