<?php

namespace App\Exceptions;

use App\Common\Conf\CommonConf;
use App\Common\Conf\Error;
use App\Services\Service;
use App\Services\WechatServices\OpenPlatformService;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
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
        $error = ['code' => $exception->getCode(), 'msg' => $exception->getMessage()];
        $logErr = $error;
        $logErr['referer'] = $request->header('referer');
        $logErr['uri'] = $request->getUri();
        $logErr['file'] = $exception->getFile();
        $logErr['line'] = $exception->getLine();
        if ($error['code'] > 0 || $exception instanceof NotFoundHttpException) {
            Log::warning(__CLASS__ . '.Exception', $logErr);
        } else {
            Log::error(__CLASS__ . '.Exception', $logErr);
        }

        if ($exception instanceof MiscServiceException) {
            $error['desc'] = '微服务接口异常';
            $error['msg'] = $error['msg'] ?: $error['desc'];
        } elseif ($exception instanceof BusinessException) {
            $error['desc'] = Error::getErrMsg($exception->getCode());
            $error['msg'] = $error['msg'] ?: $error['desc'];
        } else {
            if (stripos($error['msg'], 'component_verify_ticket') !== false) {
                OpenPlatformService::getInstance()->setComponentVerifyTicket();
            }
        }

        if (isset($error['desc'])) {
            if ($request->expectsJson()) {
                return response()->json($error);
            } else {
                //@todo 需要根据当前环境（微信、wap、pc等），判断跳哪个页面
                //@todo 要用重定向，不能直接渲染一个页面
                //@todo 有些code需要跳转到登陆页/注册页
                return response()->view('errors.notice', $error);
            }
        }
        try {
            //检查是否有错误缓存文件
            $monitorFiles = ['Container.php', 'services.php', 'ProviderRepository.php'];
            if (in_array(basename($logErr['file']), $monitorFiles)) {
                @rename(base_path() . '/bootstrap/cache/services.php', base_path() . '/bootstrap/cache/services.php.error');
            }
        } catch (\Exception $e) {
            Log::error('检查是否有错误缓存文件失败: ' . $e->getMessage());
        }
        return parent::render($request, $exception);
    }
}
