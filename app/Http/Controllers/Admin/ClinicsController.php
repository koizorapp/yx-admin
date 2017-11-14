<?php

namespace App\Http\Controllers\Admin;

use App\Models\Clinics;
use App\Services\ClinicsService;
use App\Services\CoreService;
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
        CoreService::validate($request,[
            'name' => 'required',
            'center_id' => 'required | numeric | min:1'
        ]);
        $name = $request->get('name');
        $center_id = $request->get('center_id');
        $result = ClinicsService::addClinics($name,$center_id);
        return $result ? $this->json() : $this->json([], 'error', 5000);
    }

    /*
     * 添加平行诊室
     */
    protected function addParallelClinics(Request $request)
    {
        CoreService::validate($request,[
            'name' => 'required',
            'center_id' => 'required | numeric | min:1',
            'clinics_list' => 'required | json'
        ]);
        $center_id = $request->get('center_id');
        $name = $request->get('name');
        $clinics_list = json_decode($request->get('clinics_list'),true);
        $result = ClinicsService::addParallelClinics($center_id,$name,$clinics_list);
        return $result ? $this->json() : $this->json([], 'error', 5000);
    }

    /*
     * 编辑诊室
     */
    protected function editClinics(Request $request)
    {
        CoreService::validate($request,[
            'name' => 'required',
            'center_id' => 'required | numeric | min:1',
            'clinics_id' => 'required'
        ]);
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
