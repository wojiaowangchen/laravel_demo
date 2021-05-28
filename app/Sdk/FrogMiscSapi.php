<?php
/**
 * Created by PhpStorm.
 * User: test
 * Date: 2020/5/28
 * Time: 1:28 PM
 */

namespace App\Sdk;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FrogMiscSapi extends MiscSapi{
    private $host;

    public function __construct($host = '')
    {
        if (!$host) {
            $this->host = config('microservice.host.frog_api');
        } else {
            $this->host = $host;
        }
    }


    /**
     * 发送模板消息
     * @param $params
     * @return array|mixed
     */
    public function test($params)
    {
        Log::info('sendTemplateMsg SDK params: ', $params);
        $action = '';
        return $this->httpGet($this->host . $action , $params , 'json');
    }

}
