<?php
/**
 * Created by PhpStorm.
 * User: koizora
 * Date: 2017/11/2
 * Time: 11:28
 */

namespace App\Services;


use App\Models\JobGrade;
use App\Models\ModuleJobGrade;
use App\Models\Personnel;
use Illuminate\Support\Facades\DB;

class JobGradeService extends CoreService
{
    public static function getJobGrade($center_id)
    {
        $list = JobGrade::where('center_id',$center_id)->get(['id','name'])->toArray();
        if(!empty($list)){
            foreach ($list as $key => &$value){
                $value['personnel_list'] = Personnel::where('job_grade_id',$value['id'])->get(['id','name'])->toArray();
                if(empty($value['personnel_list'])){
                    unset($list[$key]);
                }
            }
        }else{
            $list = [];
        }
        return array_values($list);
    }

    public static function getJobGradeByModuleId($module_id)
    {
        $list = ModuleJobGrade::leftJoin('job_grade','module_job_grades.job_grade_id','=','job_grade.id')
            ->where('module_job_grades.module_id',$module_id)
            ->select(DB::raw('yx_job_grade.id,yx_job_grade.name'))
            ->get()->toArray();
        return $list;
    }
}