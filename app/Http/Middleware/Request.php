<?php
/**
 * 公众号请求中间件.
 */
namespace App\Http\Middleware;

use App\Common\Conf\CommonConf;
use App\Services\Service;
use App\Services\UserServices\UserService;
use Closure;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class Request
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //@todo 检验登陆用户身份，是否拥有当前请求相应的身份
        $param = $request->all();

//        $headers = $request->header();
//        $param['headers']['referer'] = $headers['referer'] ?? '';
//        $param['headers']['user-agent'] = $headers['user-agent'] ?? '';
        $param['headers']['method'] = $request->method();
//        $param['headers']['host'] = $headers['host'] ?? '';
        Log::info('请求开始', $param);
        if ($request->method() == 'OPTIONS') {
            exit;
        }

        return $next($request);
    }
}
