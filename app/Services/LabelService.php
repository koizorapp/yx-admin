<?php
/**
 * Created by PhpStorm.
 * User: koizora
 * Date: 2017/10/30
 * Time: 13:46
 */

namespace App\Services;


use App\Models\Label;

class LabelService
{
    protected static $labelCategory = [
        1 => '适应症',
        2 => '禁忌症',
        3 => '作用功能',
        4 => '作用部位'
    ];

    public static function getLabelList()
    {
        $labelList = Label::get()->toArray();
        $data = collect($labelList)->groupBy('label_category_id')->toArray();
        $list = [];
        foreach ($data as $key => $value){
            $list[$key]['id'] = $key;
            $list[$key]['name'] = self::$labelCategory[$key];
            $list[$key]['list'] = $value;
        }
        $list = array_values($list);
        return $list;
    }

    public static function getLabelSelectList($label_category_id)
    {
        if($label_category_id == 0){
            return Label::get()->toArray();
        }
        return Label::where('label_category_id',$label_category_id)->get()->toArray();
    }

    public static function addLabel($name,$label_category_id)
    {
        $result = new Label();
        $result->name = $name;
        $result->label_category_id = $label_category_id;
        return $result->save();
    }

    public static function editLabel($name,$label_category_id,$label_id)
    {
        $result = Label::find($label_id);
        $result->name = $name;
        $result->label_category_id = $label_category_id;
        return $result->save();
    }

    public static function delLabel($label_id)
    {
        return Label::destroy($label_id);
    }
}