<?php
/**
 * Created by PhpStorm.
 * User: koizora
 * Date: 2017/10/30
 * Time: 14:29
 */

namespace App\Services;


use App\Models\Center;
use App\Models\Clinics;
use App\Models\Equipment;
use App\Models\EquipmentLabel;
use App\Models\Label;
use Illuminate\Support\Facades\DB;

class EquipmentService extends CoreService
{
    public static function getEquipmentList($current_page)
    {
        $columns = [
            'equipments.code',
            'name',
            'name_index',
            'brands',
            'center_id',
            'clinics_id'
        ];
        $result = Equipment::paginate(self::$limit,$columns,'page',$current_page)->toArray();

        foreach ($result['data'] as $key => $value)
        {
            $result['data'][$key]['center_name'] = Center::where('id',$value['center_id'])->value('name');
            $result['data'][$key]['clinics_name'] = Clinics::where('id',$value['clinics_id'])->value('name');
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

    public static function getEquipmentListForSearch($center_id,$label_category_id,$label_key_word)
    {
        if(empty($center_id) && empty($label_key_word) && empty($label_category_id)){
            return [];
        }
        $label = [];
        if($label_key_word){
            $label = Label::where('name','like','%'.$label_key_word.'%')->pluck('id')->toArray();
        }


        $equipment_id_list = new EquipmentLabel();
        if($center_id){
            $equipment_id_list = $equipment_id_list->where('center_id',$center_id);
        }
        if($label_category_id){
            $equipment_id_list = $equipment_id_list->where('label_category_id',$label_category_id);
        }
        if($label){
            $equipment_id_list = $equipment_id_list->whereIn('label_id',$label);
        }
        $equipment_id_list = $equipment_id_list->groupBy('equipment_id')->pluck('equipment_id')->toArray();

        if(empty($equipment_id_list)){
            return [];
        }

        $columns = [
            'equipments.code',
            'name',
            'name_index',
            'brands',
            'center_id',
            'clinics_id'
        ];
        $result = Equipment::whereIn('id',$equipment_id_list)->get($columns)->toArray();

        foreach ($result as $key => $value)
        {
            $result[$key]['center_name'] = Center::where('id',$value['center_id'])->value('name');
            $result[$key]['clinics_name'] = Clinics::where('id',$value['clinics_id'])->value('name');
            $result[$key]['name'] = $result[$key]['name_index'] == 0 ? $result[$key]['name'] : $result[$key]['name'] . '_' . $result[$key]['name_index'];
            unset($result[$key]['center_id']);
            unset($result[$key]['clinics_id']);
            unset($result[$key]['name_index']);
        }

        return $result;
    }

    public static function getDetail($equipment_id)
    {
        $equipment = Equipment::find($equipment_id);

        if(empty($equipment)){
            return self::currentReturnFalse([],'该设备不存在,请尝试刷新页面.');
        }
        $name_index = $equipment->name_index == 0 ? '' : '_' . $equipment->name_index;
        $equipment->name = $equipment->name . $name_index;

        $label_list = self::getEquipmentLabelList($equipment_id);
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
        $equipment->indications = $indications;
        $equipment->contraindications = $contraindications;

        //图片 附件
        $equipment->attachments = [];
        $equipment->images = [];

        //上一条
        $last_id = Equipment::where('id','<',$equipment_id)->max('id');
        $equipment->last_id = $last_id;
        //下一条
        $next_id = Equipment::where('id','>',$equipment_id)->min('id');
        $equipment->next_id = $next_id;

        return $equipment->toArray();
    }
    public static function addAndEditEquipment($data)
    {
        //数据校验
        $checkCode = Equipment::where('code',$data['code'])->exists();
        if($checkCode){
            return self::currentReturnFalse([],'设备代码重复,请核实.');
//            return false;//该设备编号已经存在 请核实  或者是前端输入完成ajax校验
        }

        //设备名字重复自动加一
        $checkName = Equipment::where('name',$data['name'])->where('center_id',$data['center_id'])->first(['name','name_index']);
        $name_index = 0;
        if($checkName){
            $name_index = $checkName->name_index + 1;
        }
        DB::beginTransaction();

        //更新设备
        if(isset($data['equipment_id'])){
            $equipment = Equipment::find($data['equipment_id']);
        }else{
            //添加设备表
            $equipment = new Equipment();
        }

        $equipment->code                  = $data['code'];
        $equipment->name                  = $data['name']; //TODO $checkName
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
        $equipment->clinics_id            = $data['clinics_id'];//TODO 数据库改字段存储为 诊室ID
        $equipment->min_age_limit         = $data['min_age_limit'];
        $equipment->max_age_limit         = $data['max_age_limit'];
        $equipment->gender_limit          = $data['gender_limit'];
        $equipment->considerations        = $data['considerations'];
        $equipment->adverse_reaction      = $data['adverse_reaction'];
        $equipment->description           = $data['description'];
        $equipment->remark                = $data['remark'];

        $saveEquipment = $equipment->save();
        if(!$saveEquipment){
            return self::currentReturnFalse([],'添加设备错误 EQUIPMENT-ERROR-6000' . __LINE__);
        }

        if(isset($data['equipment_id'])){
            EquipmentLabel::where('equipment_id',$data['equipment_id'])->delete();
            //TODO 图片 附件
        }
        //添加设备标签表
        $mergeLabels = array_merge($data['indications'],$data['contraindications']);
        if(!empty($mergeLabels)){
            foreach ($mergeLabels as $key => $value){
                $equipment_label_data = [
                    'label_id' => $value['id'],
                    'equipment_id' => $equipment->id,
                    'center_id' => $equipment->center_id,
                    'label_category_id' => $value['id'],
                ];
                $equipment_label = EquipmentLabel::firstOrCreate($equipment_label_data);
                if(!$equipment_label){
                    return self::currentReturnFalse([],'添加设备错误 EQUIPMENT-LABEL-ERROR-6000' . __LINE__);
                }
            }
        }
        DB::commit();
        return true;

        //添加设备附件表
        //TODO


        //添加设备图片表
        //TODO
//        $equipment->equipment_labels      = $data['equipment_labels']      = $request->get('equipment_labels') ? json_decode($request->get('equipment_labels')) : [];//TODO JSON
//        $equipment->equipment_images      = $data['equipment_images']      = $request->get('equipment_images') ? json_decode($request->get('equipment_images')) : [];//TODO JSON
//        $equipment->equipment_attachments = $data['equipment_attachments'] = $request->get('equipment_attachments') ? json_decode($request->get('equipment_attachments')) : [];//TODO JSON

    }

    public static function delEquipment($equipment_id)
    {
        Equipment::where('id',$equipment_id)->delete();
        EquipmentLabel::where('equipment_id',$equipment_id)->delete();
        return true;
    }

    public static function getEquipmentLabelList($equipment_id)
    {
        $equipmentLabel = EquipmentLabel::where('equipment_id',$equipment_id)
            ->leftJoin('labels','labels.id','=','equipment_labels.label_id')
            ->get()->toArray();
        $list = collect($equipmentLabel)->groupBy('label_category_id')->toArray();
        return $list;
    }
}