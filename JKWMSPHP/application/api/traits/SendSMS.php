<?php
namespace app\api\traits;

trait SendSMS{

    /**
     *
     * @param string $mobile 手机号
     * @param string $content 短信内容
     * @return string
     */
    public function sendSMS($mobile,$content) {
        $http = 'http://114.55.176.84/msg/HttpSendSM'; // 短信接口
        $account = 'nx-shike';
        $pass = 'SHIke0825';
        $pro_id = '';
        $httpData = array (
            'account' => $account,
            'pswd' => $pass,
            'needstatus' => 'true',
            'mobile' => $mobile, // 号码
            'msg' => $content, // 内容
            'product' => $pro_id
        );
        $str = http_build_query ( $httpData );
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $http . '?' . $str );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        $content = curl_exec ( $ch );
        curl_close ( $ch );
        list ( $info, $msg_id ) = explode ( "\n", $content );
        list ( $rs_time, $status ) = explode ( ',', $info );
        return $status;
    }
}
