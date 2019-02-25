<?php
/**
 * Created by PhpStorm.
 * User: jk
 * Date: 2018/11/3
 * Time: 18:00
 */

namespace app\api\service;
header("Content-type:text/html; charset=UTF-8");
class CommonService
{

    /**
     * 统一接口返回模板
     *
     * @param array  $data        返回数据
     * @param string $msg         返回信息
     * @param int    $code        编码
     * @param string $type        类型
     * @param int    $json_option
     */
    public function jkReturn($code,$msg,$data='',$type='',$json_option=0)
    {
        if (empty($type)) {
            $type = "JSON";
        }
        switch (strtoupper($type)) {
            case 'JSON' :
                //---------- 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode(['code' => $code, 'msg' => $msg, 'data' => $data], $json_option));
            case 'XML'  :
                //----------- 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode(['code' => $code, 'msg' => $msg, 'data' => $data]));
            case 'JSONP':
                //----------- 返回JSON数据格式到客户端
                header('Content-Type:application/json; charset=utf-8');
                exit('(' . json_encode(['code' => $code, 'msg' => $msg, 'data' => $data], $json_option) . ');');
            default     :
                //----------- 用于扩展其他返回格式数据
        }
    }



    public function sendSMS ($mobile, $content, $time = '', $mid = '')
    {
        //http://114.55.176.84/msg/HttpSendSM?account=nx-shike&pswd=SHIke0825&mobile=18637703726&msg=test&needstatus=true&product=2332
        $http = 'http://114.55.176.84/msg/HttpSendSM'; // 短信接口
        $account = 'nx-shikeyx';
        $pass = 'NXshikeyx0915';
        //$pro_id = '2332';
        $pro_id = '';
        $data = array(
            'account' => $account,
            'pswd' => $pass,
            'needstatus' => 'true',
            'mobile' => $mobile, // 号码
            'msg' => $content, // 内容
            'product' => $pro_id
        );

        $str = http_build_query($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $http.'?'.$str);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        curl_close($ch);

        list($info, $msg_id) = explode("\n", $content);
        list($rs_time, $status) = explode(',', $info);

        if($status == 0) {
            return "发送成功!";
        }else{
            return "发送失败! 状态：".$status;
        }
    }

    /**
     * 发送邮件
     *
     * @param $to       email地址
     * @param $subject  email主题
     * @param $message  email信息
     * @param $headers  email开头
     * @param $parameters
     * @Author: guanyl
     * @Date: ${DATE} ${TIME}
     */
    public function mail($to,$subject,$message,$headers,$parameters){
        $to = "someone@example.com";
        $subject = "Test mail";
        $message = "Hello! This is a simple email message.";
        $from = "someonelse@example.com";
        $headers = "From: $from";
        mail($to,$subject,$message,$headers);
        echo "Mail Sent.";
    }


}