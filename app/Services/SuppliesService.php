<?php
/**
 * Created by PhpStorm.
 * User: koizora
 * Date: 2017/11/1
 * Time: 11:54
 */

namespace App\Services;


use App\Models\Supplies;

class SuppliesService extends CoreService
{
    public static function getSuppliesList($current_page)
    {
        $columns = [
            'equipments.code',
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

    public static function getSuppliesListForSearch()
    {

    }

    public static function getDetail()
    {

    }

    public static function delSupplies()
    {

    }

    public static function editSupplies()
    {

    }

    public static function addSupplies($data)
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
}