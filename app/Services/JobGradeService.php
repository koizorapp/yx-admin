<?php
/**
 * Created by PhpStorm.
 * User: koizora
 * Date: 2017/11/2
 * Time: 11:28
 */

namespace App\Services;


use App\Models\JobGrade;
use App\Models\Personnel;

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
}