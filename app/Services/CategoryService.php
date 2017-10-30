<?php
/**
 * Created by PhpStorm.
 * User: koizora
 * Date: 2017/10/30
 * Time: 10:54
 */

namespace App\Services;


use App\Models\Category;

class CategoryService
{
    public static function getCategoryList()
    {
        return Category::get()->toArray();
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