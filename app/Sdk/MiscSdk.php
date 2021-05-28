<?php
/**
 * 微服务SDK.
 * User: wangerxu
 * Date: 2018/6/7
 * Time: 下午6:30
 */

namespace App\Sdk\MiscSdk;

use HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;


class MiscSapi {

    const METHOD_GET = 'get';
    const METHOD_POST = 'post';
    const METHOD_PUT = 'put';
    const METHOD_PATCH = 'patch';
    const METHOD_DELETE = 'delete';

    //请求头信息
    protected $headers;
    //期望返回数据类型
    private $expectResponseType = 'json';

    //设置期望返回数据类型
    protected function setExpectResponseType($type)
    {
        $this->expectResponseType = $type;
    }

    /**
     * get 请求
     * @param string $url URL
     * @param array $params 参数
     * @return array|mixed
     */
    protected function httpGet($url, $params = [])
    {
//         echo $url;exit;
        return $this->request(self::METHOD_GET, $url, $params);
    }

    /**
     * post 请求
     * @param string $url URL
     * @param array $params 参数
     * @param string $type 请求参数类型
     * @return array|mixed
     */
    protected function httpPost($url, $params = [], $type = "query")
    {
        return $this->request(self::METHOD_POST, $url, $params, $type);
    }


    /**
     * put 请求
     * @param string $url URL
     * @param array $params 参数
     * @param string $type 请求参数类型
     * @return array|mixed
     */
    protected function httpPut($url, $params = [], $type = "query")
    {
        return $this->request(self::METHOD_PUT, $url, $params, $type);
    }

    /**
     * delete 请求
     * @param string $url URL
     * @param array $params 参数
     * @return array|mixed
     */
    protected function httpDelete($url, $params = [])
    {
        return $this->request(self::METHOD_DELETE, $url, $params);
    }

    /**
     * 并发post请求
     * @param string $url url
     * @param array $params 参数
     * @param string $type  请求参数类型
     * @param int $asyncNum 并发请求数量
     * @return array
     */
    protected function httpPostAsync($url, $params = [], $type = "query" , $asyncNum = 5){
        return $this->requestAsync(self::METHOD_POST , $url , $params , $type , $asyncNum);
    }

    /**
     * 并发http请求
     * @param string $method 请求方法
     * @param string $url    请求url
     * @param array $params  请求参数
     * @param string $type   请求参数类型
     * @param int $asyncNum  并发请求数量
     * @return array
     */
    protected function requestAsync($method , $url , $params = [] , $type , $asyncNum = 5){
        $url = trim($url);
        $retData = ['code' => 0, 'msg' => '', 'data' => ''];
        $type = $type ?: 'query';
        $results = [];
        $promises = [];
        $requestId = [];
        try{
            $paramCycle = array_chunk($params,$asyncNum,true);
            foreach($paramCycle as $key => $item){
                foreach($item as $k => $val){
                    $api_request_id = 'PHP_API_' . uniqid(SERVER_HOST_NAME . '_');
                    Log::info('调API开始['.$url.']['.$api_request_id.']',$val);
                    $options[$k][$type] = $val;
                    //设置请求ID
                    $this->headers['MISC_SERVICE_REQUEST_ID'] = $api_request_id;
                    //设置请求时间头
                    $this->headers['MISC_SERVICE_REQUEST_TIME'] = sprintf('%d', LARAVEL_START * 1000);
                    if ($this->headers) {
                        $options[$k]['headers'] = $this->headers;
                    }
                    $request[$k] = new \GuzzleHttp\Psr7\Request($method,$url);
                    $promises[$k] = HttpClient::sendAsync($request[$k],$options[$k])->then(function($response) use($k,$val,&$results){
                        $results[$k] = $response->getBody()->getContents();
                    });
                    $requestId[$k] = $api_request_id;
                }
                Log::info('requestAsync cycle:'.$key);
                foreach($item as $k2 => $val){
                    $promises[$k2]->wait();
                }
            }
        }catch (GuzzleException $e) {
            $retData['api'] = $url;
            $retData['code'] = $e->getCode();
            $retData['msg'] = $e->getMessage();

            Log::error('MiscSapi::request error: ', $retData);
            if (env('APP_ENV') != 'local') {
                $retData['msg'] = '网络超时，请稍后重试';
            }
            //report($e);
            return $retData;
        } catch (\Exception $e) {
            $retData['api'] = $url;
            $retData['code'] = $e->getCode();
            $retData['msg'] = $e->getMessage();

            Log::error('MiscSapi::request error: ', $retData);
            if (env('APP_ENV') != 'local') {
                $retData['msg'] = '网络超时，请稍后重试';
            }
            //report($e);
            return $retData;
        }
        if(count($results) > 0){
            $retData = [];
            foreach($results as $key => $result){
                $arr = json_decode($result,true);
                $retData[$key]['code'] = $arr['code'] ?? 0;
                $retData[$key]['msg'] = $arr['msg'] ?? '';
                $retData[$key]['data'] = $arr['data'] ?? [];
                if ($arr['code'] != 0) {
                    if ($arr['code'] == -1 && env('APP_ENV') != 'local') {
                        $retData['msg'] = '服务繁忙，请稍后重试';
                    }
                    if ($arr['code'] == -1) {
                        Log::warning('调API结束['.$url.']['.$requestId[$key].'],错误码非0，错误:', $arr);
                    } else {
                        Log::warning('调API结束['.$url.']['.$requestId[$key].'],错误码非0，错误:', $arr);
                    }
                }
            }
            return $retData;
        }else{
            return $retData;
        }
    }


    /**
     * 封装http请求
     * @param string $method 请求类型
     * @param string $url URL
     * @param array $params 参数
     * @param string $type 请求参数类型
     * @return array|mixed
     */
    protected function request($method, $url, $params = [], $type = "query")
    {
        $url = trim($url);
        $retData = ['code' => 0, 'msg' => '', 'data' => []];

        $type = $type ?: 'query';
        $options = [];
        $options[$type] = $params;
        $api_request_id = 'PHP_API_' . uniqid(SERVER_HOST_NAME . '_');
        //设置请求ID
        $this->headers['MISC_SERVICE_REQUEST_ID'] = $api_request_id;
        //设置请求时间头
        $this->headers['MISC_SERVICE_REQUEST_TIME'] = sprintf('%d', LARAVEL_START * 1000);

        if ($this->headers) {
            $options['headers'] = $this->headers;
        }
        try {
            $requestParam = in_array($method, ['get', 'delete']) ? (array) $params : [];
            $response = HttpClient::request($method, $url, $options);
        } catch (GuzzleException $e) {
            $retData['code'] = $e->getCode();
            $retData['msg'] = $e->getMessage();

            Log::error('MiscSapi::request error: ', $retData);
            if (env('APP_ENV') != 'local') {
                $retData['msg'] = '网络超时，请稍后重试';
            }
            //report($e);
            return $retData;
        } catch (\Exception $e) {
            $retData['code'] = $e->getCode();
            $retData['msg'] = $e->getMessage();

            Log::error('MiscSapi::request error: ', $retData);
            if (env('APP_ENV') != 'local') {
                $retData['msg'] = '网络超时，请稍后重试';
            }
            //report($e);
            return $retData;
        }
        $body = $response->getBody();
        $ret = $body->getContents();
        if (!$ret) {
            return $retData;
        }
        if ($this->expectResponseType != 'json') {
            return $ret;
        }

        $arr = json_decode($ret, 1);
        if (!is_array($arr)) {
            return $ret;
        }
        $retData['code'] = $arr['code'];
        $retData['msg'] = isset($arr['msg']) ? $arr['msg'] : '';
        $retData['data'] = (isset($arr['data']) && $arr['data']) ? $arr['data'] : [];
        if ($arr['code'] != 0) {
            if ($arr['code'] == -1 && env('APP_ENV') != 'local') {
                $retData['msg'] = '服务繁忙，请稍后重试';
            }
            if ($arr['code'] == -1) {
                Log::warning('调API结束['.$url.']['.$api_request_id.'],错误码非0，错误:', $arr);
            } else {
                $arr['code'] = 1000 . $arr['code'];
                Log::warning('调API结束['.$url.']['.$api_request_id.'],错误码非0，错误:', $arr);
            }
        }

        return $retData;
    }

    /**
     * 设置请求头
     * @param $headers
     */
    protected function setHeaders($headers) {
        $this->headers = $headers;
    }

    /**
     * 添加请求头
     * @param string $name
     * @param string $val
     */
    protected function addHeader($name, $val) {
        $this->headers[$name] = $val;
    }
}
