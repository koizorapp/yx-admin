<?php
/**
 * Created by PhpStorm.
 * User: koizora
 * Date: 2017/11/1
 * Time: 11:54
 */

namespace App\Services;


use App\Models\Center;
use App\Models\Label;
use App\Models\Supplies;
use App\Models\SuppliesLabel;
use Illuminate\Support\Facades\DB;

class SuppliesService extends CoreService
{
    public static function getSuppliesList($current_page)
    {
        $columns = [
            'id',
            'code',
            'name',
            'name_index',
            'brands',
            'center_id',
            'specifications'
        ];
        $result = Supplies::paginate(self::$limit,$columns,'page',$current_page)->toArray();

        foreach ($result['data'] as $key => $value)
        {
            $result['data'][$key]['center_name'] = Center::where('id',$value['center_id'])->value('name');
            $result['data'][$key]['name'] = $result['data'][$key]['name_index'] == 0 ? $result['data'][$key]['name'] : $result['data'][$key]['name'] . '_' . $result['data'][$key]['name_index'];
            unset($result['data']['center_id']);
            unset($result['data']['clinics_id']);
            unset($result['data']['name_index']);
        }

        $data['list'] = $result['data'];
        $data['total_page'] = $result['last_page'];
        $data['total_count'] = $result['total'];
        $data['current_page'] = $result['current_page'];
        return $data;
    }

    public static function getSuppliesListForSearch($center_id,$label_category_id,$label_key_word)
    {
        if(empty($center_id) && empty($label_key_word) && empty($label_category_id)){
            return [];
        }
        $label = [];
        if($label_key_word){
            $label = Label::where('name','like','%'.$label_key_word.'%')->pluck('id')->toArray();
        }


        $supplies_id_list = new SuppliesLabel();
        if($center_id){
            $supplies_id_list = $supplies_id_list->where('center_id',$center_id);
        }
        if($label_category_id){
            $supplies_id_list = $supplies_id_list->where('label_category_id',$label_category_id);
        }
        if($label){
            $supplies_id_list = $supplies_id_list->whereIn('label_id',$label);
        }
        $supplies_id_list = $supplies_id_list->groupBy('supplies_id')->pluck('supplies_id')->toArray();

        if(empty($supplies_id_list)){
            return [];
        }

        $columns = [
            'id',
            'code',
            'name',
            'name_index',
            'brands',
            'center_id',
            'specifications'
        ];
        $result = Supplies::whereIn('id',$supplies_id_list)->get($columns)->toArray();

        foreach ($result as $key => $value)
        {
            $result[$key]['center_name'] = Center::where('id',$value['center_id'])->value('name');
            $result[$key]['name'] = $result[$key]['name_index'] == 0 ? $result[$key]['name'] : $result[$key]['name'] . '_' . $result[$key]['name_index'];
            unset($result[$key]['center_id']);
            unset($result[$key]['name_index']);
        }

        return $result;
    }

    public static function getDetail($supplies_id)
    {
        $supplies = Supplies::find($supplies_id);

        if(empty($supplies)){
            return self::currentReturnFalse([],'该设备不存在,请尝试刷新页面.');
        }
        $name_index = $supplies->name_index == 0 ? '' : '_' . $supplies->name_index;
        $supplies->name = $supplies->name . $name_index;

        $label_list = self::getSuppliesLabelList($supplies_id);
        if(!empty($label_list) && isset($label_list[1])){
            $indications = collect($label_list[1])->implode('name',',');
        }else{
            $indications = '';
        }

        if(!empty($label_list) && isset($label_list[2])){
            $contraindications = collect($label_list[2])->implode('name',',');
        }else{
            $contraindications = '';
        }
        $supplies->indications = $indications;
        $supplies->contraindications = $contraindications;
        $supplies->center_name  = Center::where('id',$supplies->center_id)->value('name');
        $supplies->gender_limit_name = self::$gender_data[$supplies->gender_limit];

        //图片 附件
        $supplies->attachments = [];
        $supplies->images = [];

        //上一条
        $last_id = Supplies::where('id','<',$supplies_id)->max('id');
        $supplies->last_id = $last_id;
        //下一条
        $next_id = Supplies::where('id','>',$supplies_id)->min('id');
        $supplies->next_id = $next_id;

        return $supplies->toArray();
    }

    public static function delSupplies($supplies_id)
    {
        Supplies::where('id',$supplies_id)->delete();
        SuppliesLabel::where('supplies_id',$supplies_id)->delete();
        return true;
    }

    public static function addAndEditSupplies($data)
    {
        //数据校验
        $checkCode = Supplies::where('code',$data['code'])->exists();
        if($checkCode){
            return self::currentReturnFalse([],'设备代码重复,请核实.');
//            return false;//该设备编号已经存在 请核实  或者是前端输入完成ajax校验
        }

        //设备名字重复自动加一
        $checkName = Supplies::where('name',$data['name'])->where('center_id',$data['center_id'])->first(['name','name_index']);
        $name_index = 0;
        if($checkName){
            $name_index = $checkName->name_index + 1;
        }
        DB::beginTransaction();

        //更新设备
        if(isset($data['supplies_id'])){
            $equipment = Supplies::find($data['supplies_id']);
        }else{
            //添加设备表
            $equipment = new Supplies();
        }

        $equipment->code                  = $data['code'];
        $equipment->name                  = $data['name'];
        $equipment->name_index            = $name_index;
        $equipment->english_name          = $data['english_name'];
        $equipment->storage_name          = $data['storage_name'];
        $equipment->center_id             = $data['center_id'];
        $equipment->brands                = $data['brands'];
        $equipment->production_area       = $data['production_area'];
        $equipment->specifications        = $data['specifications'];
        $equipment->purchase_price        = $data['purchase_price'];
        $equipment->market_price          = $data['market_price'];
        $equipment->once_cost             = $data['once_cost'];
        $equipment->unit                  = $data['unit'];
        $equipment->min_age_limit         = $data['min_age_limit'];
        $equipment->max_age_limit         = $data['max_age_limit'];
        $equipment->gender_limit          = $data['gender_limit'];
        $equipment->considerations        = $data['considerations'];
        $equipment->adverse_reaction      = $data['adverse_reaction'];
        $equipment->description           = $data['description'];
        $equipment->remark                = $data['remark'];

        $saveEquipment = $equipment->save();
        if(!$saveEquipment){
            return self::currentReturnFalse([],'添加用品错误 SUPPLIES-ERROR-6000' . __LINE__);
        }

        if(isset($data['supplies_id'])){
            SuppliesLabel::where('supplies_id',$data['supplies_id'])->delete();
            //TODO 图片 附件
        }
        //添加设备标签表
        $mergeLabels = array_merge($data['indications'],$data['contraindications']);
        if(!empty($mergeLabels)){
            foreach ($mergeLabels as $key => $value){
                $equipment_label_data = [
                    'label_id' => $value['id'],
                    'supplies_id' => $equipment->id,
                    'center_id' => $equipment->center_id,
                    'label_category_id' => $value['id'],
                ];
                $equipment_label = SuppliesLabel::firstOrCreate($equipment_label_data);
                if(!$equipment_label){
                    return self::currentReturnFalse([],'添加用品错误 SUPPLIES-LABEL-ERROR-6000' . __LINE__);
                }
            }
        }
        DB::commit();
        return true;

        //添加用品附件表
        //TODO


        //添加用品图片表
        //TODO
//        $equipment->equipment_labels      = $data['equipment_labels']      = $request->get('equipment_labels') ? json_decode($request->get('equipment_labels')) : [];//TODO JSON
//        $equipment->equipment_images      = $data['equipment_images']      = $request->get('equipment_images') ? json_decode($request->get('equipment_images')) : [];//TODO JSON
//        $equipment->equipment_attachments = $data['equipment_attachments'] = $request->get('equipment_attachments') ? json_decode($request->get('equipment_attachments')) : [];//TODO JSON
    }

    public static function getSuppliesLabelList($supplies_id)
    {
        $suppliesLabel = SuppliesLabel::where('supplies_id',$supplies_id)
            ->leftJoin('labels','labels.id','=','supplies_labels.label_id')
            ->get()->toArray();
        $list = collect($suppliesLabel)->groupBy('label_category_id')->toArray();
        return $list;
    }
}