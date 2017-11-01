<?php
/**
 * Created by PhpStorm.
 * User: koizora
 * Date: 2017/10/27
 * Time: 16:49
 */

namespace App\Services;


class CoreService
{
    private static $msg = "";
    private static $status = 5000;
    private static $data = [];

    public static $limit = 15;

    public static $gender_data = ['0' => '不限', '1' => '限男', '2' => '限女'];

    public static function getLastMsg()
    {
        return self::$msg;
    }

    public static function getLastStatus()
    {
        return self::$status;
    }

    public static function getLastData()
    {
        return self::$data;
    }

    public static function currentReturnFalse(array $data = [], $msg = "", $status = 5000)
    {
        self::$msg    = $msg;
        self::$status = $status;
        self::$data   = $data;
        return false;
    }
}