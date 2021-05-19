<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use JWTAuth;
use phpDocumentor\Reflection\Types\Object_;


class PassportController extends Controller
{
    /**
     * 用户注册
     */
    public function register(Request $request)
    {
        // jwt token
        $credentials = [
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'email' => 'wangerxu@1d1d100.com'
        ];
        $user = User::create($credentials);
        if($user){
            $token = JWTAuth::fromUser($user);
            return $this->responseWithToken($token);
        }
    }

    /**
     * 用户登录
     */
    public function login(Request $request)
    {


        // todo 用户登录逻辑
        // jwt token
        $credentials = $request->only('name', 'password');
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['result'=>'failed']);
        }

        return $this->responseWithToken($token);
    }

    /**
     * 刷新token
     */
    public function refresh()
    {
        return $this->responseWithToken(auth()->refresh());
    }

    /**
     * 退出登录
     */
    public function logout(Request $request)
    {
        auth()->logout();
    }

    /**
     * 响应
     */
    private function responseWithToken(string $token)
    {
        $response = [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 60
        ];
        return $response;
        return response()->json($response);
    }

    public function demo(Request $request){
        $user_name = $request->get('user_name');
        Log::info('username',$request->all());
        return ['name'=>$user_name];
    }
}
