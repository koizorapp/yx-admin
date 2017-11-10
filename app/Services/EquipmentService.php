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
            'id',
            'code',
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
            unset($result['data'][$key]['center_id']);
            unset($result['data'][$key]['clinics_id']);
            unset($result['data'][$key]['name_index']);
        }

        $data['list'] = $result['data'];
        $data['total_page'] = $result['last_page'];
        $data['total_count'] = $result['total'];
        $data['current_page'] = $result['current_page'];
        return $data;
    }

    public static function getEquipmentListByCenterId($center_id)
    {
        $list = Equipment::where('center_id',$center_id)->get(['id','name'])->toArray();
        return $list;
    }

    public static function getEquipmentListForSearch($center_id,$label_category_id,$label_key_word)
    {
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
            'id',
            'code',
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
            $indications = $label_list[1];//collect($label_list[1])->implode('name',',');
        }else{
            $indications = '';
        }

        if(!empty($label_list) && isset($label_list[2])){
            $contraindications = $label_list[2];//collect($label_list[2])->implode('name',',');
        }else{
            $contraindications = '';
        }
        $equipment->equipment_indications_labels = $indications;
        $equipment->equipment_contraindications_labels = $contraindications;
        $equipment->center_name  = Center::where('id',$equipment->center_id)->value('name');
        $equipment->clinics_name = Clinics::where('id',$equipment->clinics)->value('name');
        $equipment->gender_limit_name = self::$gender_data[$equipment->gender_limit];
        $equipment->age_limit = ModuleService::ageLimit($equipment->min_age_limit,$equipment->max_age_limit);

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
        $check_code = Equipment::where('code',$data['code'])->exists();
        if($check_code && !isset($data['equipment_id'])){
            return self::currentReturnFalse([],'设备代码重复,请核实.');
        }

        //设备名字重复自动加一
        $check_name = Equipment::where('name',$data['name'])->where('center_id',$data['center_id'])->max('name_index');
        $name_index = 0;
        if($check_name){
            $name_index = $check_name + 1;
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
        $equipment->english_name          = empty($data['english_name']) ? '' : $data['english_name'];
        $equipment->storage_name          = empty($data['storage_name']) ? '' : $data['storage_name'];
        $equipment->center_id             = $data['center_id'];
        $equipment->brands                = empty($data['brands']) ? '' : $data['brands'];
        $equipment->production_area       = empty($data['production_area']) ? '' : $data['production_area'];
        $equipment->specifications        = empty($data['specifications']) ? '' : $data['specifications'];
        $equipment->purchase_price        = empty($data['purchase_price']) ? 0.00 : $data['purchase_price'];
        $equipment->market_price          = empty($data['market_price']) ? 0.00 : $data['market_price'];
        $equipment->once_cost             = empty($data['once_cost']) ? 0.00 : $data['once_cost'];
        $equipment->clinics_id            = empty($data['clinics_id']) ? 0 : $data['clinics_id'];
        $equipment->min_age_limit         = empty($data['min_age_limit']) ? 0 : $data['min_age_limit'];
        $equipment->max_age_limit         = empty($data['max_age_limit']) ? 151 : $data['max_age_limit'];
        $equipment->gender_limit          = $data['gender_limit'];
        $equipment->considerations        = empty($data['considerations']) ? '' : $data['considerations'];
        $equipment->adverse_reaction      = empty($data['adverse_reaction']) ? '' : $data['adverse_reaction'];
        $equipment->description           = empty($data['description']) ? '' : $data['description'];
        $equipment->remark                = empty($data['remark']) ? '' : $data['remark'];

        $save_equipment = $equipment->save();
        if(!$save_equipment){
            return self::currentReturnFalse([],'添加设备错误 EQUIPMENT-ERROR-6000' . __LINE__);
        }

        if(isset($data['equipment_id'])){
            EquipmentLabel::where('equipment_id',$data['equipment_id'])->delete();
            //TODO 图片 附件
        }
        //添加设备标签表
        if(!empty($data['indications']) && !empty($data['contraindications'])){
            $mergeLabels = array_merge($data['indications'],$data['contraindications']);
        }else if(empty($data['indications']) && !empty($data['contraindications'])){
            $mergeLabels = $data['contraindications'];
        }else if(!empty($data['indications']) && empty($data['contraindications'])){
            $mergeLabels = $data['indications'];
        }else{
            $mergeLabels = [];
        }
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