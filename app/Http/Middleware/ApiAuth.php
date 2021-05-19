<?php

namespace App\Http\Middleware;
use JWTAuth;
use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
class ApiAuth
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
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {  //获取到用户数据，并赋值给$user

                return response()->json([
                    'errcode' => 1004,
                    'errmsg' => 'user not found'

                ], 404);
            }
            //如果想向控制器里传入用户信息，将数据添加到$request里面
            $userInfo = [
                'user_id' => $user->id,
                'user_name'    => $user->name
            ];
            $request->attributes->add($userInfo);//添加参数
            return $next($request);

        } catch (TokenExpiredException $e) {

            return response()->json([
                'errcode' => 1003,
                'errmsg' => 'token 过期' , //token已过期
            ]);

        } catch (TokenInvalidException $e) {

            return response()->json([
                'errcode' => 1002,
                'errmsg' => 'token 无效',  //token无效
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'errcode' => 1001,
                'errmsg' => '缺少token' , //token为空
            ]);

        }

        return $next($request);
    }
}
