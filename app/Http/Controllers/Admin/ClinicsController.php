<?php

namespace App\Http\Controllers\Admin;

use App\Services\ClinicsService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ClinicsController extends Controller
{
    /*
     * 诊室列表
     */
    protected function getClinicsList(Request $request)
    {
        $list = ClinicsService::getClinicsList();
        return $this->json($list);
    }

    /*
     * 获取中心诊室列表
     */
    protected function getClinicsListByCenterId(Request $request)
    {
        $center_id = $request->get('center_id');
        $result = ClinicsService::getClinicsListByCenterId($center_id);
        return $this->json($result);
    }

    /*
     * 添加诊室
     */
    protected function addClinics(Request $request)
    {
        $name = $request->get('name');
        $center_id = $request->get('center_id');
        $result = ClinicsService::addClinics($name,$center_id);
        return $result ? $this->json() : $this->json([], 'error', 5000);
    }

    /*
     * 编辑诊室
     */
    protected function editClinics(Request $request)
    {
        $name = $request->get('name');
        $center_id = $request->get('center_id');
        $clinics_id = $request->get('clinics_id');
        $result = ClinicsService::editClinics($name,$center_id,$clinics_id);
        return $result ? $this->json() : $this->json([], 'error', 5000);
    }

    /*
     * 删除诊室
     */
    protected function delClinics(Request $request)
    {
        $clinics_id = $request->get('clinics_id');
        $result = ClinicsService::delClinics($clinics_id);
        return $result ? $this->json() : $this->json([], 'error', 5000);
    }
}
