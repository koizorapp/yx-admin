<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\CenterService;

class CenterController extends Controller
{
    /*
     * 中心列表
     */
    protected function getCenterList(Request $request)
    {
        $list = CenterService::getCenterList();
        return $this->json($list);

    }

    /*
     * 添加中心
     */
    protected function addCenter(Request $request)
    {
        $name = $request->get('name');
        $code = $request->get('code');
        $result = CenterService::addCenter($name,$code);
        return $result ? $this->json() : $this->json([],'error',5000);
    }

    /*
     * 编辑中心
     */
    protected function editCenter(Request $request)
    {
        $center_id = $request->get('center_id');
        $name      = $request->get('name');
        $code      = $request->get('code');
        $result    = CenterService::editCenter($center_id,$name,$code);
        return $result ? $this->json() : $this->json([],'error',5000);
    }

    /*
     * 删除中心
     */
    protected function delCenter(Request $request)
    {
        $center_id = $request->get('center_id');
        $result    = CenterService::delCenter($center_id);
        return $result ? $this->json() : $this->json([],'error',5000);
    }



}
