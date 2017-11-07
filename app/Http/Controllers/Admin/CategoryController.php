<?php

namespace App\Http\Controllers\Admin;

use App\Services\CategoryService;
use App\Services\CoreService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    /*
     * 类别列表
     */
    protected function getCategoryList(Request $request)
    {
        $list = CategoryService::getCategoryList();
        return $this->json($list);
    }

    /*
     * 获取中心类别
     */
    protected function getCategoryListByCenterId(Request $request)
    {
        CoreService::validate($request,
            [
                'center_id' => 'required | numeric | min:1'
            ]
        );
        $center_id = $request->get('center_id');
        $result    = CategoryService::getCategoryListByCenterId($center_id);
        return $this->json($result);
    }

    /*
     * 添加类别
     */
    protected function addCategory(Request $request)
    {
        CoreService::validate($request,[
                'center_id' => 'required | numeric | min:1',
                'name'      => 'required',
                'code'      => 'required'  //TODO 自定义 只能是大写英文字母
        ]);
        $center_id = $request->get('center_id');
        $name      = $request->get('name');
        $code      = $request->get('code');
        $result    = CategoryService::addCategory($center_id, $name, $code);
        return $result ? $this->json() : $this->json([], 'error', 5000);
    }

    /*
     * 编辑类别
     */
    protected function editCategory(Request $request)
    {
        CoreService::validate($request,[
            'center_id'   => 'required | numeric | min:1',
            'category_id' => 'required | numeric | min:1',
            'name'        => 'required',
            'code'        => 'required'
        ]);
        $category_id = $request->get('category_id');
        $center_id   = $request->get('center_id');
        $name        = $request->get('name');
        $code        = $request->get('code');
        $result      = CategoryService::editCategory($category_id,$center_id, $name, $code);
        return $result ? $this->json() : $this->json(CategoryService::getLastData(),CategoryService::getLastMsg(),CategoryService::getLastStatus());
    }

    /*
     * 删除类别
     */
    protected function delCategory(Request $request)
    {
        CoreService::validate($request,[
            'category_id' => 'required | numeric | min:1'
        ]);
        $category_id = $request->get('category_id');
        $result      = CategoryService::delCategory($category_id);
        return $result ? $this->json() : $this->json([],'该数据不存在，删除失败',5000);
    }
}
