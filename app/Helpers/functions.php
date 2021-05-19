<?php

use Illuminate\Support\Collection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Str;

/**
 * Created by PhpStorm.
 * User: hrenj
 * Date: 2018/7/6
 * Time: 10:29
 */

//PHP stdClass Object转array
function object_array($array) {
	if(is_object($array)) {
		$array = (array)$array;
	} if(is_array($array)) {
	foreach($array as $key=>$value) {
		$array[$key] = object_array($value);
	}
		}
	return $array;
}

//计算字符串的长度(包括中英数字混合情况)
function countStringLen($str) {
    $name_len = strlen ( $str );
    $temp_len = 0;
    for($i = 0; $i < $name_len;) {
        if (strpos ( 'abcdefghijklmnopqrstvuwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', $str [$i] ) === false) {
            $i = $i + 3;
            $temp_len += 1;
        } else {
            $i = $i + 1;
            $temp_len += 1;
        }
    }
    return $temp_len;
}

//统计汉字字符长度
function getChineseLength($str=''){
    if(empty($str)){
        return 0;
    }
    $str2br = nl2br($str);
    //获取换行符出现的次数
    $br_num = substr_count($str2br,'
');
    //回车的长度
    $br_le = mb_strlen(nl2br('
'),'UTF-8');

    //去除回车的长度
    $l = mb_strlen($str2br, 'UTF-8') - $br_num*$br_le;

    //加上一行的默认字数
    $l =$l + $br_num*18;

    return $l;
}


/**
 * 获取被赋值的key value
 * @param array $variables 变量名数组
 * @param array $variableValues 变量和值数组
 * @return array
 * @author yanghuichao
 * @todo laravel 已经有了 helpers
 */
function getAssignedVariables($variables, $variableValues)
{
    $assigned = [];
    foreach ($variables as $key => $variable) {
        //todo 可以对赋值对象进行别名以及设置默认值处理，不会影响以前的传值方式  -- liyan
        //todo 示例： ['field1','field2','field3'=>['alias'=>'tmp_name','default'=>'']]
        if(is_array($variable)){
            if(isset($variable['alias'])){
                $assigned[$variable['alias']] = $variableValues[$key] ?? (isset($variable['default'])?$variable['default']:'');
            }
        }else{
            if (isset($variableValues[$variable])) {
                $assigned[$variable] = $variableValues[$variable];
            }
        }
    }
    return $assigned;
}

/**
 * 检验数组是否包含某些key，或某些key是否被赋值
 * @param array $array 要检验的数组
 * @param array $keys 要检验的key
 * @param bool $assigned key是否必须被赋值
 * @return bool
 * @author yanghuichao
 * @todo laravel 已经有了 helpers
 */
function arrayHas($array, $keys, $assigned = false)
{
    if (!$array) {
        return false;
    }
    foreach ($keys as $key) {
        if (!isset($array[$key])) {
            return false;
        }
        if ($assigned && (null === $array[$key])) {
            return false;
        }
    }
    return true;
}

/*
 * 计算两个日期相隔的时间
 * @param datetime $date1 起始时间
 * @param datetime $date2 结束时间
 * @return array
 * */
function diffDate($date1,$date2){
    $datestart= date('Y-m-d',strtotime($date1));
    if(strtotime($datestart)>strtotime($date2)){
        $tmp=$date2;
        $date2=$datestart;
        $datestart=$tmp;
    }
    list($Y1,$m1,$d1)=explode('-',$datestart);
    list($Y2,$m2,$d2)=explode('-',$date2);
    $Y=$Y2-$Y1; // 1
    $m=$m2-$m1; // 0
    $d=$d2-$d1; // -11
    if($d<0){
        $d+=(int)date('t',strtotime("-1 month $date2"));
        $m=--$m;
    }
    if($m<0){
        $m+=12;
        $Y=--$Y;
    }
    if($date1 == $date2){
        $d = 1;
    }
    return ['year' => $Y , 'month' => $m , 'day' => $d];
}

/**
 * 两个日期之间排出两个月份的时长
 * @params: array
 * @author: wangerxu
 * @time: 2020-09-08 14:39
 */
function dischargeDate($date1, $date2){
    $date1 = strtotime($date1);
    $date2 = strtotime($date2);
    $twoDays = 0;
    $eightDays = 0;

    while($date1 < $date2){
        if (intval(date("m",$date1)) == 2){
            $twoDays++;
        }
        if (intval(date("m",$date1)) == 8){
            $eightDays++;
        }
        $date1 = $date1 + 86400;
    }

    $twoMonthDays = date("Y") == '2024' || date("Y") == '2028' ? 29 : 28;
    $data = [
        'twoMonth' => [
            'month' => intval($twoDays / $twoMonthDays),
            'day' => $twoDays >= $twoMonthDays ? $twoDays % $twoMonthDays : $twoDays
        ],
        'eightMonth' => [
            'month' => intval($eightDays / 31),
            'day' => $eightDays >= 31 ? $eightDays % 31 : $eightDays
        ]
    ];
    return $data;
}

/**
 * 求两个日期之间相差的天数
 * (针对1970年1月1日之后，求之前可以采用泰勒公式)
 * @param string $date1
 * @param string $date2
 * @return number
 */
function diffDays($date1, $date2) {
    if($date1>$date2){
        $startTime = strtotime($date1);
        $endTime = strtotime($date2);
    }else{
        $startTime = strtotime($date2);
        $endTime = strtotime($date1);
    }
    $diff = $startTime-$endTime;
    $day = $diff/86400;

    return intval($day);
}

/**
 * 根据时间戳获取简短时间
 * @param $timestamp
 * @return false|string
 */
function showShortTime($timestamp)
{
    $s = time() - $timestamp;
    if ($s > 604800) {        //大于一周
        return date('m-d', $timestamp);
    } elseif ($s > 86400) {    //大于一天
        $week = array('', '1', '2', '3', '4', '5', '6', '7');
        return $week[intval($s / 86400)] . '天前';
    } elseif ($s > 3600) {    //大于一小时
        return intval($s / 3600) . '小时前';
    } elseif ($s > 1800) {
        return '30分钟前';
    } elseif ($s > 600) {
        return '10分钟前';
    } elseif ($s > 300) {
        return '5分钟前';
    } elseif ($s > 60) {
        return '1分钟前';
    } else {
        return '刚刚';
    }
}

/**
 * 动态发布时间格式化
 * @param $timestamp
 * @return string
 */
function showMomentTime($timestamp){
    $formatDate = '';
    $today = strtotime('today');            //今天
    $yesterday = strtotime('yesterday');    //昨天
    //$thisMonday = strtotime( "last monday" );    //周一,这里使用last monday会有问题，当前时间如果是周一会获取上周一的时间
    $thisMonday = strtotime(date('Y-m-d 00:00:00',time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600));
    if($timestamp > $today){
        //今天发布的
        $formatDate = date('H:i',$timestamp);
    } elseif($timestamp < $today && $timestamp > $yesterday && $timestamp > $thisMonday){
        //昨天发布的
        $formatDate = '昨天  '.date('H:i',$timestamp);
    } elseif($timestamp < $yesterday && $timestamp > $thisMonday){
        //昨天之前，本周一之后发布的
        $week = ['日','一','二','三','四','五','六'];
        $formatDate = '周'.$week[date('w',$timestamp)].date('H:i',$timestamp);
    } elseif($timestamp < $thisMonday){
        $formatDate = date('Y.m.d H:i',$timestamp);
    }
    return $formatDate;
}


/**
 * 根据时间戳
 * @param $time
 * @return int
 */
function getAge($time){
    $a = date('Y-m-d',$time);
    $b = date("Y-m-d");
    $date1 = date_create($a);
    $date2 = date_create($b);
    $diff = date_diff($date1,$date2);//获取年
    return $diff->y;
}

/**
 * 根据时间戳获取年龄到月
 * @param $time
 * @return int
 */
function getAgeM($time){
	$a = date('Y-m-d',$time);
	$b = date("Y-m-d");
	$date1 = date_create($a);
	$date2 = date_create($b);
	$diff = date_diff($date1,$date2);//获取年
	if ($diff->y > 0){
		return $diff->y . '岁' . $diff->m . '个月';
	} else {
		return $diff->m . '个月';
	}

}
/*
 * 获取日期对应的星期
* 参数$date为输入的日期数据，格式如：2018-6-22
*/
function get_week($date){
	//强制转换日期格式
	$date_str = date('Y-m-d', strtotime($date));
	//封装成数组
	$arr = explode("-", $date_str);
	//参数赋值
	//年
	$year = $arr[0];
	//月，输出2位整型，不够2位右对齐
	$month = sprintf('%02d', $arr[1]);
	//日，输出2位整型，不够2位右对齐
	$day = sprintf('%02d', $arr[2]);
	//时分秒默认赋值为0；
	$hour = $minute = $second = 0;
	//转换成时间戳
	$strap = mktime($hour, $minute, $second, $month, $day, $year);
	//获取数字型星期几
	$number_wk = date("w", $strap);
	//自定义星期数组
	$weekArr = array("星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六");
	//获取数字对应的星期
	return $weekArr[$number_wk];
}
//秒格式化
function changeTimeType($seconds){
	if ($seconds >3600){
		$hours =intval($seconds/3600);
		$minutes = $seconds % 3600;
		$time = $hours."小时".gmstrftime('%M',$minutes) . '分钟';
	}else{
		$time = gmstrftime('%M分钟',$seconds);
	}
	return$time;
}
/*
 * 返回图片服务器网络地址
 * @param string $imgPath 图片相对地址
 * @return string 图片绝对路径地址
 * */
function getImageWithHost($imgPath){
    if(!$imgPath)return '';
    if(strrpos($imgPath,'group') === false){
        $imgPath = env('HOST_IMG_URL').'/'.$imgPath;
    }else{
        $imgPath = env('HOST_IMG_FDFS').'/'.$imgPath;
    }
    return $imgPath;
}

/**
 * 下载远程文件到本地
 * @param string $imgUrl 远程文件地址
 * @param string $filePath 本地文件目录
 * @return string
 */
function catchImage($imgUrl, $filePath,$retrunContent=false) {
    //$client = new \GuzzleHttp\Client(['verify' => false]);
    try {
        $client = new \GuzzleHttp\Client(['verify' => false, 'timeout' => 30]);
        //$response = $client->get($imgUrl, ['save_to' => $localFile]);
        $imgUrl = \App\Services\BaseServices\BaseService::changeImageToIntranet($imgUrl);
        $response = $client->request('get', $imgUrl);
        //获取文件类型
        $fileType = $response->getHeader('Content-Type');
        $fileType = trim(strrchr($fileType[0], '/'), '/');
        $file = $filePath . '/' . uniqid() . '.' . $fileType;

        if($retrunContent){
            return $response->getBody()->getContents();
        }

        file_put_contents($file, $response->getBody()->getContents());

        return $file;
    } catch (\Exception $e) {
        $err['code'] = $e->getCode();
        $err['msg'] = $e->getMessage();
        \Illuminate\Support\Facades\Log::error('catchImage : ', $err);
        return false;
    }
}


function getArrayByKey($arrayName, $keys=[]):array{
    $newArray = [];
    foreach ($arrayName as $key => $value) {
        if(is_array($keys)){
            $newVal = [];
            foreach ($keys as $field) {
                $newVal[$field] = $value[$field];
            }

            $newArray[] = $newVal;
        }
    }

    return $newArray;
}

function viewCount(int $views):string{
    if($views<1000){
        return $views;
    }elseif($views<10000){
        return floor($views/1000) . "000+";
    }elseif($views<100000){
        return floor($views/10000) . "0000+";
    }elseif($views<1000000){
        return floor($views/100000) . "00000+";
    }else{
        return "1000000+";
    }
}

/*
 * 阿拉伯数字转为汉字
 */
function numToWord($num){
    $chiNum = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
    $chiUni = array('','十', '百', '千', '万', '亿', '十', '百', '千');

    $chiStr = '';

    $num_str = (string)$num;

    $count = strlen($num_str);
    $last_flag = true; //上一个 是否为0
    $zero_flag = true; //是否第一个
    $temp_num = null; //临时数字

    $chiStr = '';//拼接结果
    if ($count == 2) {//两位数
        $temp_num = $num_str[0];
        $chiStr = $temp_num == 1 ? $chiUni[1] : $chiNum[$temp_num].$chiUni[1];
        $temp_num = $num_str[1];
        $chiStr .= $temp_num == 0 ? '' : $chiNum[$temp_num];
    }else if($count > 2){
        $index = 0;
        for ($i=$count-1; $i >= 0 ; $i--) {
            $temp_num = $num_str[$i];
            if ($temp_num == 0) {
                if (!$zero_flag && !$last_flag ) {
                    $chiStr = $chiNum[$temp_num]. $chiStr;
                    $last_flag = true;
                }
            }else{
                $chiStr = $chiNum[$temp_num].$chiUni[$index%9] .$chiStr;

                $zero_flag = false;
                $last_flag = false;
            }
            $index ++;
        }
    }else{
        $chiStr = $chiNum[$num_str[0]];
    }

    return $chiStr;
}

/**
 * 截取字符串
 * @param type $str
 */
function str_cut($string, $length, $etc = '...')
{
    $result = '';
    $string = html_entity_decode(trim(strip_tags($string)), ENT_QUOTES, 'UTF-8');
    $strlen = strlen($string);
    for ($i = 0; (($i < $strlen) && ($length > 0)); $i++) {
        if ($number = strpos(str_pad(decbin(ord(substr($string, $i, 1))), 8, '0', STR_PAD_LEFT), '0')) {
            if ($length < 1.0) {
                break;
            }
            $result .= substr($string, $i, $number);
            $length -= 1.0;
            $i += $number - 1;
        } else {
            $result .= substr($string, $i, 1);
            $length -= 0.5; // Question Here.
        }
    }
    $result = htmlspecialchars($result, ENT_QUOTES, 'UTF-8');
    if ($i < $strlen) {
        $result .= $etc;
    }
    return $result;
}

//获取上一个月份或下一个月份
function NextMonth($datetime,$r = 1){
	$time = date('Ym01',  strtotime($datetime));
	if($r == 1){    //上个月
		$date = date('Y-m-d',strtotime("$time - 1 month"));
	}else if($r == 2 ){ //下个月
		$date = date('Y-m-d',strtotime("$time + 1 month"));
	}else{
		return $datetime;
	}
	return $date;
}


/**
 * 计算两个日期之间的所有日期
 * @param date $startTime 开始时间
 * @param date $endTime 结束时间
 * @return array  两个日期中间的日期
 */
function getDaysSection($startTime,$endTime){
    $startTime = strtotime($startTime);
    $endTime = strtotime($endTime);
    $leaveDays = [];
    while($startTime<=$endTime){
        $leaveDays[] = date('m月d日',$startTime);
        $startTime = strtotime('+1 day',$startTime);
    }
    return $leaveDays;
}

//微信错误码解析
function errorCodeMsg($code){
	$codes = [
		'48003' => '第一次使用群发，需要在微信后台群发功能菜单下点击同意按钮，同意腾讯的群发协议',
		'45028' => '本月发送次数超限',
        '45065' => '本条文章重复群发',
        '40007' => '不合法的媒体文件 id',
        '48001' => '公众号未授权，请核实小号是否过期。'
	];

	if (isset($codes[$code])) {
		return $codes[$code];
	} else {
		return '错误码：' . $code;
	}
}
//判断字符串是否是手机号码
function is_mobile( $text ) {
	$search = '/^0?1[3|4|5|6|7|8][0-9]\d{8}$/';
	if ( preg_match( $search, $text ) ) {
		return ( true );
	} else {
		return ( false );
	}
}

/**
 * 获取此刻至结束日期剩余的秒数，如果不传值，默认为今天结束日期
 * @param string $endDate
 * @return int
 */
function getTodayExpire($endDate = ''){
    if(!$endDate) {
        $endDate = strtotime(date('Y-m-d').'23:59:59');
    }
    $expire = intval(($endDate-time()));       //过期时间为今天，单位:秒
    return $expire;
}



/*
* 方法 isDate
* 功能 判断日期格式是否正确
* 参数 $str 日期字符串 $format 日期格式
* 返回 无
*/
function is_Date($str,$format='Y-m-d'){
    $unixTime_1=strtotime($str);
    if(!is_numeric($unixTime_1)) return false; //如果不是数字格式，则直接返回
    $checkDate=date($format,$unixTime_1);
    $unixTime_2=strtotime($checkDate);
    if($unixTime_1==$unixTime_2){
        return true;
    }else{
        return false;
    }
}


/*
 * 替换资源地址中的token
 * @param string $url --图片地址
 * @param string $token --token
 * @return string --新的图片地址
 * */
function replaceResourceToken($url,$token){
    $pattern = "/(?<=access_token=).*(?=&)/";
    $url = preg_replace($pattern,$token,$url);
    return $url;
}



function pack1($data){
    $sign = sign($data);
    array_unshift($data, $sign);
    $str = implode("@|@", $data);
    $stat_str = base64_encode($str);

    return $stat_str;
}

function unpack1($stat_str){
    try {
        $str = base64_decode($stat_str);
        $click = explode("@|@", $str);
        $sign = array_shift($click);
        if( $sign != sign($click) )
            return array();

        return $click;
    } catch( Exception $e ) {
        return array();
    }
}
function sign($data){
    return substr(md5(implode('', $data) . 'ZHSYoc_mYaTptxa560lhfLIE8p_EfJihu61M3WAV'), 0, 8);
}


//格式化金额显示，保留两位小数点
function moneyFormat($money){
    $money = sprintf("%.2f",$money);
    return $money;
}

function isMobile() {
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    {
        return true;
    }

    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
    {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }

    // 判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'MQQBrowser',
            'nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            //'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        );
        //判断飞华公司电视
        if (preg_match("/InettvBrowser/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return false;
        }

        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/pad/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            //排除pad
            return false;
        }

        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;

        }
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT']))
    {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;

        }
    }
    return false;
}


/**
 * 转换时间格式
 * TODO 因为手机端上传格式要求为Y年m月d日，后台格式为Y-m-d，所以增加一个通用方法进行转换
 * @param string $date 日期
 * @param string $format 时间格式
 * @return false|string
 */
function transDateFormat($date,$format = 'Y-m-d H:i:s'){
    $stamp = strtotime($date);
    if(!$stamp){
        $arr = date_parse_from_format('Y年m月d日',$date);
        $stamp = mktime(0,0,0,$arr['month'],$arr['day'],$arr['year']);
    }
    $dateFormat = date($format,$stamp);
    return $dateFormat;
}

/**
 * 对二维数组进行排序[key需要唯一] #废弃，用multisortArrayByField函数
 * todo 规则：根据指定键名进行排序，后返回排序后的数组，数组的下标从0开始
 * todo 注意：此函数作用不等同于 array_multisort, key如果存在重复的情况，会覆盖数据，所以需要排序的key最好是id或者不重复的值
 * @param array $ary 需要排序的数组
 * @param string $key  按照指定的key进行排序
 * @param string $sort 排序规则  sort-升序，  desc-降序
 */
function multisortArray($ary, $key, $sort='asc'){
    $ary = array_column($ary,null,$key);
    if($sort == 'asc'){
        ksort($ary);
    }else{
        krsort($ary);
    }
    $ary = array_values($ary);
    return $ary;
}

/**
 * 过滤字符串中表情及换行
 * @param $str
 * @return mixed|null|string|string[]
 */
function filter_str($str){
    $str = filter_emoji($str);
    $str = str_replace(PHP_EOL, '', $str);
    return $str;
}

/**
 * 过滤emoji表情
 * @param $str
 * @return null|string|string[]
 */
function filter_emoji($str)
{
    $str = preg_replace_callback(    //执行一个正则表达式搜索并且使用一个回调进行替换
        '/./u',
        function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        },
        $str);

    return $str;
}

//阿里oss图片webp转jpg
function ossImgWebp2Jpg($imgUrl) {
    //http://file.1d1d100.com/2018/12/21/d7d216a1545547fbb6c933c9d1967991.webp?x-oss-process=image/interlace,1/format,jpg
    $arr = explode('?', $imgUrl);
    if (strripos($arr[0], '.webp')) {
        if (isset($arr[1]) && $arr[1]) {
            if (stripos($arr[1], 'x-oss-process=') !== false) {
                $arr[1] = str_replace('x-oss-process=image/','x-oss-process=image/interlace,1/format,jpg/', $arr[1]);
            } else {
                $arr[1] .= '&x-oss-process=image/interlace,1/format,jpg';
            }
        } else {
            $arr[1] = 'x-oss-process=image/interlace,1/format,jpg';
        }
        return $arr[0] . '?' . $arr[1];
    }
    return $imgUrl;
}

/**
 * 获取http协议
 * @param string $host
 * @return string http|https
 */
function getHttpScheme($host = '') {

    $arr = parse_url($host ?: env('HOST_DOMAIN'));
    return $arr['scheme'];
}

/**
 * Generate the URL to a named route.
 *
 * @param  array|string  $name
 * @param  mixed  $parameters
 * @param  bool  $absolute
 * @return string
 */
function ydRoute($name, $parameters = [], $absolute = true)
{

    $url = route($name, $parameters, $absolute);
    if (env('APP_ENV') == 'develop') {
        return $url;
    }
    $url = str_replace('http://', 'https://', $url);
    return $url;
}

/**
 * 对二维数组进行排序,可传多个排序字段
 * 示例：$sortKey = ['id'=>SORT_ASC , 'age' => SORT_DESC , 'time' => SORT_ASC];
 * @param array $ary       需要排序的数组
 * @param array $sortKey   排序规则： 键为排序的字段， 值为排序方式[升序、降序]
 * @return array|bool
 */
function multisortArrayByField($ary, $sortKey){
    $args = [];
    if(empty($ary) || empty($sortKey)){
        return $ary;
    }
    foreach($sortKey as $key => $sort){
        if(!in_array($sort,[SORT_ASC,SORT_DESC])){
            return $ary;
        }
        $sortAry = array_column($ary,$key);
        if(!is_array($sortAry)){
            return $ary;
        }
        $args[] = $sortAry;
        $args[] = $sort;
    }
    $args[] = &$ary;
    call_user_func_array('array_multisort',$args);
    return $ary;
}


if (!function_exists('base64_urlSafeEncode')) {
	/**
	 * 对提供的数据进行urlsafe的base64编码。
	 *
	 * @param string $data 待编码的数据，一般为字符串
	 *
	 * @return string 编码后的字符串
	 * @link http://developer.qiniu.com/docs/v6/api/overview/appendix.html#urlsafe-base64
	 */
	function base64_urlSafeEncode($data)
	{
		$find = array('+', '/');
		$replace = array('-', '_');
		return str_replace($find, $replace, base64_encode($data));
	}
}

if (!function_exists('base64_urlSafeDecode')) {

	/**
	 * 对提供的urlsafe的base64编码的数据进行解码
	 *
	 * @param string $str 待解码的数据，一般为字符串
	 *
	 * @return string 解码后的字符串
	 */
	function base64_urlSafeDecode($str)
	{
		$find = array('-', '_');
		$replace = array('+', '/');
		return base64_decode(str_replace($find, $replace, $str));
	}
}
//判断0点到5点不允许群发  1/允许   2、不允许
function is_allow_send(){
	$is_allow_send = 1;
	if (intval(date("H")) >= 0 && intval(date("H")) < 5) {
		$is_allow_send = 2;
	}
	return $is_allow_send;

}

//获取连接携带的参数
function getUrlParam($url=''){
    if(empty($url)) return [];
    $arr = parse_url($url);
    if(empty($arr['query'])) return [];
    $queryParts = explode('&', $arr['query']);
    $params = array();
    foreach ($queryParts as $param) {
        $item = explode('=', $param);
        $params[$item[0]] = $item[1];
    }
    return $params;
}

/**
 * @param $arr
 * @param $key_name
 * @return array
 * 将数据库中查出的列表以指定的 id 作为数组的键名
 */
function convert_arr_key($arr,$key_name)
{
    if(empty($arr)) return [];
    $arr2 = array();
    foreach ($arr as $key => $val) {
        $arr2[$val[$key_name]] = $val;
    }
    return $arr2;
}

//将描述转为分钟数
function second_to_minute($s=0){
    //计算分钟
    //算法：将秒数除以60，然后下舍入，既得到分钟数
    $h    =    floor($s/60);
    //计算秒
    //算法：取得秒%60的余数，既得到秒数
    $s    =    $s%60;
    //如果只有一位数，前面增加一个0
    $h = sprintf("%02d" , $h);
    $s = sprintf("%02d" , $s);
    return $h.':'.$s;
}

if (! function_exists('insensitive')) {
    /**
     * 脱敏操作
     *
     * @param $str
     * @param int $start
     * @param int $end
     * @return mixed
     */
    function insensitive($str, $start = 0, $end = 0)
    {
        if (empty($str)) return null;
        return preg_replace('/^(.{' . $start . '}).*(.{' . $end . '})$/u', '\1' . str_repeat('*', mb_strlen($str) - $start - $end) . '\2', $str);
    }
}


if (! function_exists('success')) {
    /**
     * @param array $data
     * @param string $msg
     * @param int $code
     * @return array
     */
    function success($data = [], $msg = '操作成功', $code = 0) {

        $return = ['msg' => $msg, 'code' => $code, 'data' => []];

        if ($data instanceof AbstractPaginator) {
            $return['data'] = [
                'list' => $data->items(),
                'total' => (int)$data->total(),
                'current' => (int)$data->currentPage(),
                'isFirstPage' => $data->onFirstPage(),
                'isLastPage' => $data->lastPage() == $data->currentPage(),
                'pageSize' => (int)$data->perPage(),
                'navigatepageNums' => (function($s, $e){
                    $n = [$s];
                    while ($s < $e) {
                        $s++;
                        $n[] = $s;
                    }
                    return $n;
                })(1, $data->lastPage())
            ];
            if($data->sumTotal){
                $return['data']['sumTotal'] = $data->sumTotal;
            }
        }

        elseif (is_array($data)) {
            $return['data'] = $data;
        }

        elseif ($data instanceof Collection) {
            $return['data'] = $data->values();
        }

        return $return;
    }
}

if (! function_exists('fail')) {
    /**
     * @param string $msg
     * @param int $code
     * @param null $data
     * @return array
     */
    function fail($msg = '操作失败.', $code = -1, $data = null) {
        return [
            'code' => $code, 'msg' => $msg, 'data' => $data
        ];
    }
}

/**
 * 校验密码强度：大于8位，且必须包含字母和数字
 * @param $password
 * @return bool
 */
function checkPassword($password){
    if(preg_match('/^(?![^a-zA-Z]+$)(?!\D+$).{8,18}$/',$password)){
        return true;
    }else{
        return false;
    }
}

/**
 * 根据起始日期，得出上一周对于起始日期来说是第几周
 * TODO 比如firstWeek=2020-03-09，
 *      当天是2020-03-16,那么lastWeek=1， 当天是2020-03-26，lastWeek=2
 *      如果当天日期小于2020-03-16日，lastWeek默认为1
 * @param string $firstWeek  起始日期
 * @return int
 */
function getLastWeekOffset($firstWeek){
    $day = date('Y-m-d');
    $thisWeek = date('w', strtotime($day));
    $startDiff = $thisWeek ? $thisWeek-1 : 6;
    $weekStart = date('Y-m-d', strtotime("$day - $startDiff days"));
    $lastWeek = intval(((strtotime($weekStart) - strtotime($firstWeek)) / 86400) / 7) ?: 1;
    return $lastWeek;
}

/**
 * 数组转换为驼峰命名
 * @param $data
 * @return mixed
 */
function arrayKeyToCamel($data){
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            unset($data[$key]);
            $value = is_array($value) ? arrayKeyToCamel($value) : $value;
            $data[camel_case($key)] = $value;
        }
    }
    return $data;
}

/**
 * 数组转换为蛇式命名
 * @param $data
 * @return mixed
 */
function arrayKeyToSnake($data){
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            unset($data[$key]);
            $value = is_array($value) ? arrayKeyToSnake($value) : $value;
            $data[Str::snake($key)] = $value;
        }
    }
    return $data;
}

/**
 * 通用加密规则，校验秘钥
 * @param $data
 * @return bool
 */
function checkSign($data){
    if (!is_array($data) || !$data['sign']) {
        return false;
    }

    $sign = $data['sign'];
    unset($data['sign']);
    ksort($data);
    $data['key'] = "DDweilai@.2020";

    $checkSign = md5(implode('#', $data));
    if ($sign === $checkSign) {
        return true;
    } else {
        return false;
    }
}

/**
 * 拼接文件远程地址
 * @param string $path
 * @return string
 */
function joinFileDomain($path = ''){
    if (empty($path)) return '';
    if (substr($path, 0, 4) == 'http') return $path;
    $domain = 'https://file.1d1d100.com/';
    return $domain . $path;
}

/**
 * 生成随机数
 *
 * @param int $size
 * @return string
 */
function makeRandomNumber($size = 4)
{
    $code = '';

    for ($i = 0; $i < $size; $i ++)
        $code .= chr(mt_rand(48, 57));

    return $code;
}

/**
 * 字符串位运算加密解密
 * @params: array
 * @author: wangerxu
 * @time: 2020-12-22 11:58
 */
function StrCode($string, $action = 'ENCODE') {
    $action != 'ENCODE' && $string = base64_decode($string);

    $code = '';
    $kstr = 'beijingdiandianweilaijiaoyukejiyouxiangongsi';
    $key = substr(md5($kstr), 8, 18);
    $keyLen = strlen($key);
    $strLen = strlen($string);
    for ($i = 0; $i < $strLen; $i++) {
        $k = $i % $keyLen;
        $code .= $string[$i] ^ $key[$k];
    }
    return ($action != 'DECODE' ? base64_encode($code) : $code);
}
