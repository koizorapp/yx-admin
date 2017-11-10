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
    const KEY_TOKEN_USER = 'u_t_%s';

    public static function login($email,$password)
    {
//        parent::validate($request,[
//            'phone'    => 'required|numeric|digits:11',
//            'password' => 'required',
//        ]);
        $user = User::where('email', $email)->first();
        if(is_null($user)){
            return self::currentReturnFalse([],'用户不存在.',100001);
        }

        if(password_verify($password, $user->password)){
            if(\Redis::exists(sprintf(self::KEY_TOKEN_USER, $user->token))){
                return $user->toArray();
            }
            $token = self::generationToken();
            $key   = sprintf(self::KEY_TOKEN_USER, $token);
            \Redis::set($key, $user);

            $user->token = $token;
            $user->save();
            return $user->toArray();
        }else{
            return self::currentReturnFalse([],'密码错误.',100002);
        }
    }

    public static function logout()
    {

    }

    public static function generationToken(){
        do{
            $token = str_random(20);
            $key = sprintf(self::KEY_TOKEN_USER, $token);
        }while(\Redis::exists($key));
        return $token;
    }
}