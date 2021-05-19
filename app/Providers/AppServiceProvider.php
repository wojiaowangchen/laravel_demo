<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 单例，只在解析时会触发一次
        error_reporting('E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED');

        //增加phone验证规则
        Validator::extend('phone', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^\d{11}$/', $value);
        });

        // 加入日志唯一ID和客户端IP
        $this->app->resolving('log', function($log, $app) {

            $log->pushProcessor(function($record) use ($app) {
                $microtime = microtime(true);
                $arr = explode('.', $microtime);
                $record['datetime'] = date('Y-m-d H:i:s', $arr[0])  . '.' . sprintf('%-03.3s', $arr[1] ?? '');
                $record['log_id']   = REQUEST_ID;
                $record['use_time'] = round($microtime * 1000 - LARAVEL_START * 1000, 3);
                // 不需要截断
                $notTruncate = (substr(REQUEST_ID, 0, 10) != 'PHP_SCRIPT') && ($record['message'] == '请求开始');

                // 拼接上下文信息
                if (!empty($record['context'])) {
                    $record['message'] .= ' ' . stripslashes(json_encode($record['context'], JSON_UNESCAPED_UNICODE));
                    $record['context']  = [];
                }
                $record['short_message'] = $record['message'];

                if ($notTruncate) {
                    $record['short_message'] .= sprintf(', uri: %s, ip: %s', $app->request->getRequestUri(), $app->request->getClientIp());
                } else {
                    // 日志长度不超过4096个字符
                    if (mb_strlen($record['short_message'], 'utf-8') > 4096) {
                        $record['short_message'] = mb_substr($record['short_message'], 0, 4096, 'utf-8') . '... (truncate too long)';
                    }
                }

                return $record;
            });
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
