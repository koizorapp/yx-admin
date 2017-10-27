<?php
namespace App\Services;
use App\Models\Center;
/**
 * Created by PhpStorm.
 * User: koizora
 * Date: 2017/10/27
 * Time: 15:20
 */
class CenterService
{
    public static function getCenterList()
    {
        $list = Center::get()->toArray();
        return $list;
    }

    public static function addCenter($name,$code)
    {
        $center = new Center();
        $center->name = $name;
        $center->code = $code;
        return $center->save();
    }

    public static function editCenter($center_id,$name,$code)
    {
        $center = Center::find($center_id);
        $center->name = $name;
        $center->code = $code;
        return $center->save();
    }

    public static function delCenter($center_id)
    {
        return Center::destroy($center_id);
    }
}