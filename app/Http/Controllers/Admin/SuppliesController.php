<?php

namespace App\Http\Controllers\Admin;

use App\Services\SuppliesService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SuppliesController extends Controller
{
    /*
     * 用品列表
     */
    protected function getSuppliesList(Request $request)
    {
        $current_page = $request->get('current_page');
        $list = SuppliesService::getSuppliesList($current_page);
        return $this->json($list);
    }

    /*
     * 获取中心用品列表
     */
    protected function getSuppliesListByCenterId(Request $request)
    {
        $center_id = $request->get('center_id');
        $list = SuppliesService::getSuppliesListByCenterId($center_id);
        return $this->json($list);
    }

    /*
     * 搜索用品列表
     */
    protected function getSuppliesListForSearch(Request $request)
    {
        $center_id = $request->get('center_id');
        $label_category_id = $request->get('label_category_id');
        $label_key_word = $request->get('label_key_word');
//        $current_page = $request->get('current_page');
        $result = SuppliesService::getSuppliesListForSearch($center_id,$label_category_id,$label_key_word);
        return $result ? $this->json($result) : $this->json(SuppliesService::getLastData(),SuppliesService::getLastMsg(),SuppliesService::getLastStatus());
    }

    /*
     * 用品详情
     */
    protected function getDetail(Request $request)
    {
        $supplies_id = $request->get('supplies_id');
        $result = SuppliesService::getDetail($supplies_id);
        return $result ? $this->json($result) : $this->json(SuppliesService::getLastData(),SuppliesService::getLastMsg(),SuppliesService::getLastStatus());
    }

    /*
     * 删除用品
     */
    protected function delSupplies(Request $request)
    {
        $supplies_id = $request->get('supplies_id');
        $result = SuppliesService::delSupplies($supplies_id);
        return $result ? $this->json() : $this->json(SuppliesService::getLastData(),SuppliesService::getLastMsg(),SuppliesService::getLastStatus());
    }

    /*
     * 编辑用品
     */
    protected function editSupplies(Request $request)
    {
        $data['code']                  = $request->get('code');//TODO 重复录入校验 //TODO 必填
        $data['name']                  = $request->get('name');//TODO 已经存在 自动加一 //TODO 必填
        $data['english_name']          = $request->get('english_name','');
        $data['storage_name']          = $request->get('storage_name','');
        $data['center_id']             = $request->get('center_id');//TODO 必填
        $data['brands']                = $request->get('brands','');
        $data['production_area']       = $request->get('production_area','');
        $data['specifications']        = $request->get('specifications','');
        $data['purchase_price']        = $request->get('purchase_price','');
        $data['market_price']          = $request->get('market_price','');
        $data['once_cost']             = $request->get('once_cost','');
        $data['unit']                  = $request->get('unit','');
        $data['min_age_limit']         = $request->get('min_age_limit');//TODO 必填
        $data['max_age_limit']         = $request->get('max_age_limit');//TODO 必填
        $data['gender_limit']          = $request->get('gender_limit');//TODO 必填
        $data['considerations']        = $request->get('considerations','');
        $data['adverse_reaction']      = $request->get('adverse_reaction','');
        $data['description']           = $request->get('description','');
        $data['remark']                = $request->get('remark','');
        $data['indications']           = $request->get('supplies_indications_labels') ? json_decode($request->get('supplies_indications_labels'),true) : [];//TODO JSON
        $data['contraindications']     = $request->get('supplies_contraindications_labels') ? json_decode($request->get('supplies_contraindications_labels'),true) : [];//TODO JSON
        $data['supplies_images']       = $request->get('supplies_images') ? json_decode($request->get('supplies_images'),true) : [];//TODO JSON
        $data['supplies_attachments']  = $request->get('supplies_attachments') ? json_decode($request->get('supplies_attachments'),true) : [];//TODO JSON
        $data['supplies_id']           = $request->get('supplies_id');
        $result = SuppliesService::addAndEditSupplies($data);
        return $result ? $this->json() : $this->json(SuppliesService::getLastData(),SuppliesService::getLastMsg(),SuppliesService::getLastStatus());
    }

    /*
     * 添加用品
     */
    protected function addSupplies(Request $request)
    {
        $data['code']                  = $request->get('code');//TODO 重复录入校验 //TODO 必填
        $data['name']                  = $request->get('name');//TODO 已经存在 自动加一 //TODO 必填
        $data['english_name']          = $request->get('english_name','');
        $data['storage_name']          = $request->get('storage_name','');
        $data['center_id']             = $request->get('center_id');//TODO 必填
        $data['brands']                = $request->get('brands','');
        $data['production_area']       = $request->get('production_area','');
        $data['specifications']        = $request->get('specifications','');
        $data['purchase_price']        = $request->get('purchase_price','');
        $data['market_price']          = $request->get('market_price','');
        $data['once_cost']             = $request->get('once_cost','');
        $data['unit']                  = $request->get('unit','');
        $data['min_age_limit']         = $request->get('min_age_limit');//TODO 必填
        $data['max_age_limit']         = $request->get('max_age_limit');//TODO 必填
        $data['gender_limit']          = $request->get('gender_limit');//TODO 必填
        $data['considerations']        = $request->get('considerations','');
        $data['adverse_reaction']      = $request->get('adverse_reaction','');
        $data['description']           = $request->get('description','');
        $data['remark']                = $request->get('remark','');
        $data['indications']           = $request->get('supplies_indications_labels') ? json_decode($request->get('supplies_indications_labels'),true) : [];//TODO JSON
        $data['contraindications']     = $request->get('supplies_contraindications_labels') ? json_decode($request->get('supplies_contraindications_labels'),true) : [];//TODO JSON
        $data['supplies_images']       = $request->get('supplies_images') ? json_decode($request->get('supplies_images'),true) : [];//TODO JSON
        $data['supplies_attachments']  = $request->get('supplies_attachments') ? json_decode($request->get('supplies_attachments'),true) : [];//TODO JSON
        $result = SuppliesService::addAndEditSupplies($data);
        return $result ? $this->json() : $this->json(SuppliesService::getLastData(),SuppliesService::getLastMsg(),SuppliesService::getLastStatus());
    }
}
