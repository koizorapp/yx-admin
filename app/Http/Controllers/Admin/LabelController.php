<?php

namespace App\Http\Controllers\Admin;

use App\Services\LabelService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LabelController extends Controller
{
    /*
     * 标签列表
     */
    protected function getLabelList(Request $request)
    {
        $list = LabelService::getLabelList();
        return $this->json($list);
    }

    /*
     * 添加标签
     */
    protected function addLabel(Request $request)
    {
        $name = $request->get('name');
        $label_category_id = $request->get('label_category_id');
        $result = LabelService::addLabel($name,$label_category_id);
        return $result ? $this->json() : $this->json([],'error',5000);
    }

    /*
     * 编辑标签
     */
    protected function editLabel(Request $request)
    {
        $name = $request->get('name');
        $label_category_id = $request->get('label_category_id');
        $label_id = $request->get('label_id');
        $result = LabelService::editLabel($name,$label_category_id,$label_id);
        return $result ? $this->json() :  $this->json([],'error',5000);
    }

    /*
     * 删除标签
     */
    protected function delLabel(Request $request)
    {
        $label_id = $request->get('label_id');
        $result = LabelService::delLabel($label_id);
        return $result ? $this->json() :  $this->json([],'error',5000);
    }
}
