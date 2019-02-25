<?php
namespace app\api\traits;

trait BuildParam{

    /*
     * explain:获取当前时间
     * params :
     * authors:Mr.Geng
     * addTime:2018/4/4 13:49
     */
    function getTime()
    {
        return date("Y-m-d H:i:s",time());
    }

    /*
     * explain:获取当天零点时间
     * params :
     * authors:Mr.Geng
     * addTime:2018/4/10 16:13
     */
    function getTimeToday($time = '')
    {
        $time = !empty($time) ? $time : time();
        return date("Y-m-d H:i:s",strtotime(date("Y-m-d",$time)));
    }

    /*
     * explain:获取一周前的时间
     * params :
     * authors:Mr.Geng
     * addTime:2018/4/10 16:29
     */
    function getTimeWeek()
    {
        return date("Y-m-d H:i:s",strtotime(date('Y-m-d', strtotime("-7 day"))));
    }

    /*
     * explain:根据时间间隔拆分时间段
     * params : $time  单位秒
     * authors:Mr.Geng
     * addTime:2018/5/15 11:56
     */
    public function splitTime($startTime,$endTime,$time='60',$format='H:i')
    {
        $_time = range(strtotime($startTime), strtotime($endTime),$time);
        $_time = array_map(create_function('$v', 'return date("'.$format.'", $v);'), $_time);
        return $_time;
    }


    /*
     * explain:获取x天前后的时间
     * params :
     * authors:Mr.Geng
     * addTime:2018/4/11 16:22
     */
    function getTimeX($day,$newDate=null)
    {
        $time = $newDate ? strtotime($newDate) : time();
        return date('Y-m-d H:i:s', strtotime("$day day",$time));
    }

    /*
     * explain:获取x分钟前后的时间
     * params :
     * authors:Mr.Geng
     * addTime:2018/4/11 16:22
     */
    function getTimeMinuteX($minute,$newDate=null)
    {
        $time = $newDate ? strtotime($newDate) : time();
        return date('Y-m-d H:i:s', strtotime("$minute minute",$time));
    }
    /*
     * explain:创建订单sn编码
     * params :
     * authors:Mr.Geng
     * addTime:2018/3/26 15:46
     */
    function getOrderSn()
    {
        return date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    /*
     * explain:创建券号sn编码
     * params :
     * authors:Mr.Geng
     * addTime:2018/3/26 15:47
     */
    function getVoucherSn()
    {
        return date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    /*
     * explain:积分商城订单退款编号
     * params :
     * authors:Mr.Geng
     * addTime:2018/4/25 15:06
     */
    public function getOrderRefundSn()
    {
        return date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    /*
     * explain:支付编号
     * params :
     * authors:Mr.Geng
     * addTime:2018/4/25 15:06
     */
    function paySn()
    {
        return date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }
    /*
     * explain:店铺订单退款编号
     * params :
     * authors:Mr.Geng
     * addTime:2018/4/25 15:06
     */
    function refundSn()
    {
        return date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }
    /**
     * 随机生成指定长度的数字
     * @param number $length
     * @return number
     */
    function randNumber($length = 6) {
        if ($length < 1) {
            $length = 6;
        }
        $min = 1;
        for($i = 0; $i < $length - 1; $i ++) {
            $min = $min * 10;
        }
        $max = $min * 10 - 1;
        return rand ( $min, $max );
    }

    /*
     * explain:生成图片名称
     * params :
     * authors:Mr.Geng
     * addTime:2018/4/9 17:39
     */
    function imgName(){
        return time().rand(1,100);
    }
    
    /*
     * explain:获取两个时间相差的天时分
     * params :
     * authors:Mr.Geng
     * addTime:2018/4/27 11:34
     */
    function buffTime($startTime,$endTime)
    {
        $date=floor((strtotime($endTime)-strtotime($startTime))/86400);
        $hour=floor((strtotime($endTime)-strtotime($startTime))%86400/3600);
        $minute=floor((strtotime($endTime)-strtotime($startTime))%86400%3600/60);
        $second=floor((strtotime($endTime)-strtotime($startTime))%86400%60);
        return $date."天".$hour."小时".$minute."分钟";
    }
    /*
     * explain:加密解密函数
     * params :$string：字符串，明文或密文；$operation：DECODE表示解密，其它表示加密；$key：密匙；$expiry：密文有效期。
     * authors:Mr.Geng
     * addTime:2018/5/10 11:09
     */
    function authCode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
        $ckey_length = 4;
        // 密匙
        $key = md5($key ? $key : $GLOBALS['discuz_auth_key']);
        // 密匙a会参与加解密
        $keya = md5(substr($key, 0, 16));
        // 密匙b会用来做数据完整性验证
        $keyb = md5(substr($key, 16, 16));
        // 密匙c用于变化生成的密文
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length):
            substr(md5(microtime()), -$ckey_length)) : '';
        // 参与运算的密匙
        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :
        sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        // 产生密匙簿
        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分
        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if($operation == 'DECODE') {
            // 验证数据有效性，请看未加密明文的格式
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&
                substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
            return $keyc.str_replace('=', '', base64_encode($result));
        }
    }
}
