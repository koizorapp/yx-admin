<?php
/**
 * Created by PhpStorm.
 * User: koizora
 * Date: 2017/10/30
 * Time: 13:46
 */

namespace App\Services;


use App\Models\Label;

class LabelService extends CoreService
{
    protected static $labelCategory = [
        1 => '适应症',
        2 => '禁忌症',
        3 => '作用功能',
        4 => '作用部位'
    ];

    public static function getLabelList()
    {
        $list = [];
        foreach (self::$labelCategory as $key => $value){
            $list[$key]['id'] = $key;
            $list[$key]['name'] = $value;
            $list[$key]['list'] = $labelList = Label::where('label_category_id',$key)->get(['id','name'])->toArray();
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
        $exists = Label::where('name',$name)->where('label_category_id',$label_category_id)->exists();
        if($exists){
            return self::currentReturnFalse([],'该数据已经存在,请勿重复添加.');
        }
        $label = Label::firstOrCreate([
            'name' => $name,
            'label_category_id' => $label_category_id,
        ]);
        return $label;
    }

    public static function editLabel($name,$label_category_id,$label_id)
    {
        $exists = Label::where('name',$name)->where('label_category_id',$label_category_id)->value('id');
        if($exists && ($exists != $label_id)){
            return self::currentReturnFalse([],'该数据已经存在,请勿重复添加.');
        }
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