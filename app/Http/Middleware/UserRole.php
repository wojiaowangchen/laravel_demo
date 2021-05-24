<?php

namespace App\Http\Middleware;

use Closure;

class UserRole
{
    /**
     * Handle an incoming request.
     * 角色校验(能进到这里证明token验证通过)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $from='')
    {
        $userId = $request->input('user_id');
        $userName = $request->input('user_name');
        return $next($request);
    }
}
