<?php
/**
 * Created by PhpStorm.
 * User: koizora
 * Date: 2017/10/27
 * Time: 18:17
 */

namespace App\Services;


use App\Models\JobGrade;
use App\Models\Personnel;
use Illuminate\Support\Facades\DB;

class PersonnelService extends CoreService
{
    public static function getPersonnelList()
    {
        return Personnel::get()->toArray();

    }

    public static function addPersonnel($name,$center_id,$job_grade_name,$hourly_wage)
    {
        DB::beginTransaction();
        //查询是否有相同的执行人等级
        $job_grade = JobGrade::where('center_id',$center_id)->where('name',$job_grade_name)->first();
        if(empty($job_grade)){
            //添加人员等级表
            $job_grade = new JobGrade();
            $job_grade->name = $job_grade_name;
            $job_grade->hourly_wage = $hourly_wage;
            $job_grade->center_id = $center_id;
            $job_grade->save();
        }
        $personnel = new Personnel();
        $personnel->name = $name;
        $personnel->center_id = $center_id;
        $personnel->job_grade_name = $job_grade_name;
        $personnel->hourly_wage = $hourly_wage;
        $personnel->job_grade_id = $job_grade->id;
        $personnel->save();

        DB::commit();

        return true;
    }

    public static function editPersonnel($name,$center_id,$job_grade_name,$hourly_wage,$personnel_id)
    {
        DB::beginTransaction();
        //查询是否有相同的执行人等级
        $job_grade = JobGrade::where('center_id',$center_id)->where('name',$job_grade_name)->first();
        if(empty($job_grade)){
            //添加人员等级表
            $job_grade = new JobGrade();
            $job_grade->name = $job_grade_name;
            $job_grade->hourly_wage = $hourly_wage;
            $job_grade->center_id = $center_id;
            $job_grade->save();
        }
        $personnel = Personnel::find($personnel_id);
        $personnel->name = $name;
        $personnel->center_id = $center_id;
        $personnel->job_grade_name = $job_grade_name;
        $personnel->hourly_wage = $hourly_wage;
        $personnel->job_grade_id = $job_grade->id;
        $personnel->save();

        DB::commit();

        return true;
    }

    public static function delPersonnel($personnel_id)
    {
        return Personnel::destroy($personnel_id);
    }

}