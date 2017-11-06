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

class CategoryService
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
        $category = new Category();
        $category->name = $name;
        $category->code = $code;
        $category->center_id = $center_id;
        return $category->save();
    }

    public static function editCategory($category_id,$center_id, $name, $code)
    {
        $category = Category::find($category_id);
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