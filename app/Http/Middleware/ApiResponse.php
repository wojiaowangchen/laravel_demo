<?php
/**
 * Created by PhpStorm.
 * User: wangerxu
 * Date: 2021-04-15
 * Time: 13:23
 */

namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
class ApiResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $response = $next($request);


        if (method_exists($response, 'header')) {

            // 设置响应头
            $response->header('Request-LogId', REQUEST_ID);

            // 格式化JSON响应
            if (isset($response->exception)) {
                $data = [
                    'code' => $response->exception->getCode() ?: 500,
                    'msg'  => $response->exception->getMessage(),
                    'data' => []
                ];

            } else {
                $data = $response->original;
                //原始格式返回
                if (!isset($data['code'])) {
                    $data = ['code' => 0, 'msg' => 'success', 'data' => (is_string($data) || is_null($data))? new \stdClass():$data];
                }
            }
            if (is_array($data)){
            	$data = json_encode($data);
            }
            $response->setContent($data);
        }
        $microtime = microtime(true);
        $used = round($microtime * 1000 - LARAVEL_START * 1000);
        $slowLevel = '';
        if ($used >= 1000) {
            $slowLevel = '!!!';
        } elseif ($used > 500) {
            $slowLevel = '!!';
        } elseif ($used > 200) {
            $slowLevel = '!';
        }
        Log::info("请求结束$slowLevel", array_merge(['action' => Route::currentRouteAction()], $request->all()));
        return $response;

    }

}
