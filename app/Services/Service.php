<?php

namespace App\Services;

use App\Common\Conf\CommonConf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;

class Service
{
    //需要子类定义
    private static $instance;
    private static $appId = '';

    protected function __construct() {}

    /**
     * @return \App\Services\Service
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    public static function formatResult($result = []){
        if(empty($result)){
            return ['code'=>10001,'msg'=>'格式化返回结果失败','data'=>[]];
        }
        if(isset($result['data']) && !$result['data']){
            $result['data'] = [];
        }
        if(!isset($result['code'])){
            //不是SDK返回的标准格式，部分业务不需要访问后端接口
            return ['code' => 0 , 'msg' => '执行成功' , 'data' => $result];
        }
        return ['code'=>$result['code'],'msg'=>$result['msg'],'data'=>$result['data']];
    }

    /**
     * 生成手机验证码，一天内保持不变
     * @param $mobile
     * @return int
     */
    public static function genSuperMobileCaptcha($mobile) {
        Log::info(__CLASS__ . '.genMobileCaptcha' . $mobile);
        $captcha = $mobile % date('Ymd');
        $captcha = substr($captcha, -4);
        Log::info(__CLASS__ . '.genMobileCaptcha ret ' . $captcha);

        return $captcha;
    }

    /**
     * 设置cookie
     * @param $key
     * @param $value
     * @param int $expire
     */
    public static function setCookie($key, $value, $expire = CommonConf::COOKIE_EXPIRE_FOREVER){
        Log::debug(__CLASS__ . '.setCookie', compact('key', 'value'));
        Cookie::queue($key, $value, $expire, null, null, false, false);
    }

    /**
     * 获取cookie
     * @param $key
     * @return string
     */
    public static function getCookie($key){
        Log::debug(__CLASS__ . '.getCookie-'.$key);
        return Cookie::get($key);
    }

    /**
     * 清除cookie
     * @param $key
     */
    public static function forgetCookie($key){
        Log::info(__CLASS__ . '.forgetCookie-'.$key);
        Cookie::queue(Cookie::forget($key));
    }

}
