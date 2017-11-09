<?php

namespace App\Http\Controllers\Admin;

use App\Services\UserService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Session\Middleware\StartSession;

class UserController extends Controller
{
    /*
     * 登录
     */
    protected function login(Request $request)
    {
        $email = $request->get('email');
        $password = $request->get('password');
        $result = UserService::login($email,$password,$request);
    }

    /*
     * 退出
     */
    protected function logout(Request $request)
    {

    }
}
