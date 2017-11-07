<?php

namespace App\Http\Controllers\Admin;

use App\Services\CoreService;
use App\Services\EquipmentService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EquipmentController extends Controller
{
    /*
     * 设备列表
     */
    protected function getEquipmentList(Request $request)
    {
        CoreService::validate($request,[
            'current_page' => 'required | numeric | min:1'
        ]);
        $current_page = $request->get('current_page');
        $list         = EquipmentService::getEquipmentList($current_page);
        return $this->json($list);
    }

    /*
     * 中心设备列表
     */
    protected function getEquipmentListByCenterId(Request $request)
    {
        CoreService::validate($request,[
            'center_id' => 'required | numeric | min:1'
        ]);
        $center_id = $request->get('center_id');
        $list      = EquipmentService::getEquipmentListByCenterId($center_id);
        return $this->json($list);
    }

    /*
     * 搜索列表
     */
    protected function getEquipmentListForSearch(Request $request)
    {
        CoreService::validate($request,[
            'center_id'         => 'required | numeric',
            'label_category_id' => 'required | numeric',
        ]);

        $center_id         = $request->get('center_id');
        $label_category_id = $request->get('label_category_id');
        $label_key_word    = $request->get('label_key_word');
        $result            = EquipmentService::getEquipmentListForSearch($center_id,$label_category_id,$label_key_word);
//        return $result ? $this->json($result) : $this->json(EquipmentService::getLastData(),EquipmentService::getLastMsg(),EquipmentService::getLastStatus());
        return $this->json($result);
    }

    /*
     * 设备详情
     */
    protected function getDetail(Request $request)
    {
        CoreService::validate($request,[
            'equipment_id' => 'required | numeric | min:1',
        ]);
        $equipment_id = $request->get('equipment_id');
        $result       = EquipmentService::getDetail($equipment_id);
        return $result ? $this->json($result) : $this->json(EquipmentService::getLastData(),EquipmentService::getLastMsg(),EquipmentService::getLastStatus());
    }

    /*
     * 添加设备
     */
    protected function addEquipment(Request $request)
    {
        CoreService::validate($request,[
            'name'          => 'required',
            'center_id'     => 'required | numeric | min:1',
//            'min_age_limit' => 'required | numeric',
//            'max_age_limit' => 'required | numeric',
            'gender_limit'  => 'required | in:0,1,2'
        ]);
        $data['code']                  = $request->get('code');
        $data['name']                  = $request->get('name');
        $data['english_name']          = $request->get('english_name','');
        $data['storage_name']          = $request->get('storage_name','');
        $data['center_id']             = $request->get('center_id');
        $data['brands']                = $request->get('brands','');
        $data['production_area']       = $request->get('production_area','');
        $data['specifications']        = $request->get('specifications','');
        $data['purchase_price']        = $request->get('purchase_price','');
        $data['market_price']          = $request->get('market_price','');
        $data['once_cost']             = $request->get('once_cost','');
        $data['clinics_id']            = $request->get('clinics_id','');
        $data['min_age_limit']         = $request->get('min_age_limit');
        $data['max_age_limit']         = $request->get('max_age_limit');
        $data['gender_limit']          = $request->get('gender_limit');
        $data['considerations']        = $request->get('considerations','');
        $data['adverse_reaction']      = $request->get('adverse_reaction','');
        $data['description']           = $request->get('description','');
        $data['remark']                = $request->get('remark','');
        $data['indications']           = $request->get('equipment_indications_labels') ? json_decode($request->get('equipment_indications_labels'),true) : [];//TODO JSON
        $data['contraindications']     = $request->get('equipment_contraindications_labels') ? json_decode($request->get('equipment_contraindications_labels'),true) : [];//TODO JSON
        $data['equipment_images']      = $request->get('equipment_images') ? json_decode($request->get('equipment_images'),true) : [];//TODO JSON
        $data['equipment_attachments'] = $request->get('equipment_attachments') ? json_decode($request->get('equipment_attachments'),true) : [];//TODO JSON
        $result = EquipmentService::addAndEditEquipment($data);
        return $result ? $this->json() : $this->json(EquipmentService::getLastData(),EquipmentService::getLastMsg(),EquipmentService::getLastStatus());
    }

    /*
     * 编辑设备
     */
    protected function editEquipment(Request $request)
    {
        CoreService::validate($request,[
            'name'          => 'required',
            'center_id'     => 'required | numeric | min:1',
//            'min_age_limit' => 'required | numeric',
//            'max_age_limit' => 'required | numeric',
            'gender_limit'  => 'required | in:0,1,2'
        ]);
        $data['equipment_id']          = $request->get('equipment_id');
        $data['code']                  = $request->get('code');
        $data['name']                  = $request->get('name');
        $data['english_name']          = $request->get('english_name','');
        $data['storage_name']          = $request->get('storage_name','');
        $data['center_id']             = $request->get('center_id');
        $data['brands']                = $request->get('brands','');
        $data['production_area']       = $request->get('production_area','');
        $data['specifications']        = $request->get('specifications','');
        $data['purchase_price']        = $request->get('purchase_price','');
        $data['market_price']          = $request->get('market_price','');
        $data['once_cost']             = $request->get('once_cost','');
        $data['clinics_id']            = $request->get('clinics_id','');
        $data['min_age_limit']         = $request->get('min_age_limit');
        $data['max_age_limit']         = $request->get('max_age_limit');
        $data['gender_limit']          = $request->get('gender_limit');
        $data['considerations']        = $request->get('considerations','');
        $data['adverse_reaction']      = $request->get('adverse_reaction','');
        $data['description']           = $request->get('description','');
        $data['remark']                = $request->get('remark','');
        $data['indications']           = $request->get('equipment_indications_labels') ? json_decode($request->get('equipment_indications_labels'),true) : [];//TODO JSON
        $data['contraindications']     = $request->get('equipment_contraindications_labels') ? json_decode($request->get('equipment_contraindications_labels'),true) : [];//TODO JSON
        $data['equipment_images']      = $request->get('equipment_images') ? json_decode($request->get('equipment_images'),true) : [];//TODO JSON
        $data['equipment_attachments'] = $request->get('equipment_attachments') ? json_decode($request->get('equipment_attachments'),true) : [];//TODO JSON
        $result = EquipmentService::addAndEditEquipment($data);
        return $result ? $this->json() : $this->json(EquipmentService::getLastData(),EquipmentService::getLastMsg(),EquipmentService::getLastStatus());
    }

    /*
     * 删除设备
     */
    protected function delEquipment(Request $request)
    {
        CoreService::validate($request,[
            'equipment_id'  => 'required | numeric | min:1'
        ]);
        $equipment_id = $request->get('equipment_id');
        $result = EquipmentService::delEquipment($equipment_id);
        return $result ? $this->json() : $this->json(EquipmentService::getLastData(),EquipmentService::getLastMsg(),EquipmentService::getLastStatus());
    }

}
