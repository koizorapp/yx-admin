<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Mail;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
//        InvalidRequestException::class
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if(!$this->shouldReport($exception)){
            return parent::report($exception);
        }
        if('local' == env('APP_ENV') ){
            //邮件发送失败，过滤
//            if(strpos($exception->getTraceAsString(), 'swiftmailer')){
//                return parent::report($exception);
//            }


            $hostname = \App\Tools\C::$hostnames[env('APP_ENV')];
            $body = '';
            if(isset($_SERVER['REQUEST_URI'])){
                $body .= 'url: ' . $_SERVER['HTTP_HOST'] . ''. $_SERVER['REQUEST_URI'] . PHP_EOL;
            }

//            if(defined('USER_ID')){
//                $body .= 'userId: ' . USER_ID . PHP_EOL;
//            }else{
//                try{
//                    $user = \App\Services\CoreService::currentUser();
//                    $body .= 'userId: ' . $user->id . PHP_EOL;
//                }catch(InvalidTokenException $invalidToken){
//                }
//            }
            $body .= 'parameters: ' . print_r($_REQUEST, 1). PHP_EOL . PHP_EOL;
            $body .= 'useragent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? ''). PHP_EOL . PHP_EOL;
            $body .= 'Exception: ' . get_class($exception) . PHP_EOL . PHP_EOL;
            $body .= $exception->getMessage() . PHP_EOL . PHP_EOL;
            $body .= $exception->getTraceAsString();

            Mail::raw($body, function ($m) use($hostname) {
                $m->from(env('MAIL_USERNAME'), $hostname)
                    ->subject($hostname.'报错')
                    ->to('liulei@lavionlife.com', '测试邮件')
                ;
            });

            //相同的内容每5分钟发一次
//            $key = sprintf("MAIL_error_%s", md5($body));
//            if(!\Cache::add($key, $body, 5)){
//                parent::report($e);
//                \Log::info(__LINE__);
//                return;
//            }

//            try{
//                \Log::info(__LINE__);
//                Mail::raw($body, function ($m) use($hostname) {
//                    $m->from(env('MAIL_USERNAME'), $hostname)
//                        ->to('php_team@chupinxiu.com', '服务端邮件组')
//                        ->to('gaoxuedong@chupinxiu.com')
//                        ->to('chenghongzhi@chupinxiu.com')
//                        ->to('zhaojinping@chupinxiu.com')
//                        ->subject($hostname.'报错')
//                        ->to('tongjishi@chupinxiu.com', '服务端邮件组')
//                    ;
//                });
//            }catch(\Exception $m){
//                \Log::error($body);
//                \Log::error($m->getMessage());
//            }
        }
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if($exception instanceof InvalidRequestException){
            if(count($exception->data) > 0){
                $msg = array_shift($exception->data)[0];
            }else{
                $msg = '请求参数错误';
            }
            return \Response::json([
                'status' => 5000,
                'msg'    => $msg,
                'data'   => $exception->data,
            ]);
        }

        return parent::render($request, $exception);
    }
}
