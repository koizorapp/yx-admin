<?php
/**
 * Created by PhpStorm.
 * User: koizora
 * Date: 2017/11/9
 * Time: 14:05
 */

namespace App\Services;



use App\Models\User;

class UserService extends CoreService
{
    public static function login($email,$password,$request)
    {
        \Redis::set('a','b');
        echo 1;die;
    }

    public static function logout()
    {

    }
}