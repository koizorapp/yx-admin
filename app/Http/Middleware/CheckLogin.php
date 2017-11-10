<?php
/**
 * Created by PhpStorm.
 * User: koizora
 * Date: 2017/11/10
 * Time: 10:43
 */

namespace App\Http\Middleware;


use App\Services\UserService;

class CheckLogin
{
    public function __construct()
    {
    }

    public function handle($request,\Closure $next)
    {
        if('dev' == env('APP_ENV') || 'local' == env('APP_ENV')){
            return $next($request);
        }
        $request_data = $request->all();
        if(!isset($request_data['token'])){
            return \Illuminate\Support\Facades\Response::json(['status' => 20000 , 'msg' => '非法请求' , 'data' => []]);
        }else{
            $user = \Redis::get(sprintf(UserService::KEY_TOKEN_USER, $request_data['token']));
            if(empty($user)){
                return \Illuminate\Support\Facades\Response::json(['status' => 20001 , 'msg' => '登录已过期,请重新登录' , 'data' => []]);
            }
        }

        return $next($request);
    }
}