<?php
/**
 * Created by PhpStorm.
 * User: koizora
 * Date: 2017/10/30
 * Time: 10:54
 */

namespace App\Services;


use App\Models\Category;
use App\Models\Center;

class CategoryService extends CoreService
{
    public static function getCategoryList()
    {
        $center_list = Center::get(['id','name'])->toArray();

        foreach ($center_list as $key => $value){
            $center_list[$key]['list'] = $clinics_list = Category::where('center_id',$value['id'])->get(['id','name','code'])->toArray();
        }
        return $center_list;
    }

    public static function getCategoryListByCenterId($center_id)
    {
        $category_list =  Category::where('center_id',$center_id)->get(['id','name'])->toArray();
        return $category_list;
    }

    public static function addCategory($center_id,$name,$code)
    {
        $category = Category::firstOrCreate([
            'name' => $name,
            'code' => $code,
            'center_id' => $center_id
        ]);
        return $category;
    }

    public static function editCategory($category_id,$center_id, $name, $code)
    {
        $exists   = Category::where('center_id',$center_id)->where('name',$name)->where('code',$code)->value('id');
        if($exists != $category_id){
            return self::currentReturnFalse([],'该数据已经存在,请勿重复添加.');
        }
        $category = Category::find($category_id);
        if(!$category){
            return self::currentReturnFalse([],'该类别不存在,请核实.');
        }
        $category->name = $name;
        $category->code = $code;
        $category->center_id = $center_id;
        return $category->save();
    }

    public static function delCategory($category_id)
    {
        return Category::destroy($category_id);
    }
}