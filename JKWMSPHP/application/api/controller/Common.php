<?php
namespace app\Api\controller;

use app\api\Model\ActionModel;
use app\api\Model\AdminUserActionModel;
use app\Api\Model\AdminUsersModel;
use app\api\Model\AdminLogModel;

use app\api\model\PurOrderModel;
use app\api\model\QualityCheckModel;
use app\api\model\QualityCheckWaitModel;
use app\api\model\ReviewedLogModel;
use app\api\service\ValidateService;
use think\Controller;
use think\Request;
use think\Session;

class Common extends Controller {

    public function __construct(ValidateService $validateService, Request $request, AdminUserActionModel $userActionModel, ActionModel $actionModel, AdminUsersModel $usersModel){
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods: PUT,POST,GET,DELETE,OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Content-Type, Accept');
        $this->checkStatus();
        //验证签名
        //$this->checkSign($_POST);
        //验证权限
        //$this->checkPower($request,$userActionModel,$actionModel,$usersModel);
        //验证参数
        //$validateService->validate($_POST);
    }

    /*
     * explain:验证签名
     * authors:fyc
     * addTime:2018/11/03 9:43
     */
    function checkSign($data=array()){
        if(!empty($data) && is_array($data)){
            $sign = $data['sign'];
            if(empty($sign)){
                $this->jkReturn(-1,'缺少参数sign',array(),'0');
            }else{
                unset($data['sign']);
                //排序
                ksort($data);
                $str = '';
                foreach ($data as $item){
                    $str .= $item;
                }
                $str .= '87749CECEA24B1C314CC27CF7952EBC3';
                $firstSign = strtoupper(md5($str));
                //截取第3-18位，共16位
                $str2 = substr($firstSign,2,16);
                $sign2 = strtoupper(md5($str2));
                if($sign == $sign2){
                    return true;
                }else{
                    $this->jkReturn(-1,'签名错误',array(),'0');
                }
            }
        }else{
            $this->jkReturn(-1,'数据格式不正确',array(),'0');
        }
    }

    /*
     * explain:验证权限
     * authors:fyc
     * addTime:2018/11/03 10:43
     */
    function checkPower($request,$userActionModel,$actionModel,$usersModel){
        $user_token = $request->param('user_token');
        if(!empty($user_token)) {
            //验证token是否有效
            $admin_user_info = $usersModel->where("user_token",$user_token)->find();
            \session('shop_id',$admin_user_info['shop_id']);
            \session('branch_id',$admin_user_info['branch_id']);
            //token两小时内有效
            if(!empty($admin_user_info) && date("Y-m-d H:i:s",time()) - $admin_user_info['token_time'] <= 2*60*60){
                //验证action权限
                if (!empty($request->param('action'))) {
                    //不验证是否登陆的控制器
                    $not_login = ['Login', 'Common', 'Messagereport'];
                    //不验证权限的控制器
                    $not_rule = ['Login', 'Common', 'Index', 'Upload', 'Messagereport'];
                    //如果需要验证权限
                    if (!in_array($request->controller(), $not_rule)) {
                        //获取用户权限，序列或者all
                        if (!empty($admin_user_info['action_list'])) {
                            if ($admin_user_info['action_list'] != 'all') {
                                $action_id_list = unserialize($admin_user_info['action_list']);
                                $action_list = $actionModel->where('action_id', 'in', $action_id_list)->where('disabled=1')->select();
                            } else {
                                $action_list = $actionModel->where('disabled=1')->select();
                            }
                            $action_list = empty($action_list) ? array() : $action_list->toArray();
                            $action_list = array_column($action_list,'action_id');
                            $controller = $request->controller();
                            $action = $request->action(true);

                            //根据code查找控制器id
                            $where['action_code'] = $controller;
                            $where['parent_id'] = 0;
                            $where['disabled'] = 1;
                            $controller_id = $actionModel->field('action_id')->where($where)->find()->action_id;
                            //查询方法id
                            $where['action_code'] = $action;
                            $where['parent_id'] = $controller_id;
                            $where['disabled'] = 1;
                            $action_id = $actionModel->field('action_id')->where($where)->find()->action_id;
                            if (!in_array($action_id, $action_list)) {
                                $this->jkReturn(-1001, '您暂时没有此权限', array(), '0');
                            }else{
                                return true;
                            }
                        } else {
                            $this->jkReturn(-1001, '您暂时没有此权限', array(), '0');
                        }
                    }
                } else {
                    $this->jkReturn(-1002, 'action', array(), '0');
                }
            }else{
                $this->jkReturn(-1003, 'token不存在或已过期', array(), '0');
            }
        }else{
            $this->jkReturn(-1002, '缺少参数', array(), '0');
        }
    }

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
                exit(json_encode(['status' => $code, 'msg' => $msg, 'data' => $data], $json_option));
            case 'JSONP':
                //----------- 返回JSON数据格式到客户端
                header('Content-Type:application/json; charset=utf-8');
                exit('(' . json_encode(['status' => $code, 'msg' => $msg, 'data' => $data], $json_option) . ');');
            default     :
                //----------- 用于扩展其他返回格式数据
        }
    }

    /**
     * 记录操作日志
     *
     * @param string $type
     * @param string $cont
     * @param string $table_name
     * @param string $table_id
     * @Author: guanyl
     * @Date: ${DATE} ${TIME}
     */
    public function setAdminUserLog($type='',$cont='',$table_name='',$table_id='')
    {
        $userLogModel = new AdminLogModel();
        $userLogModel->log_type = $type;
        $userLogModel->content = $cont;
        $userLogModel->table_name = $table_name;
        $userLogModel->table_id = $table_id;
        $userLogModel->admin_user_id = Session::get('admin_id');
        $userLogModel->admin_nickname = Session::get('user_name');
        $browser = $this->getBrowser();
        $userLogModel->browser = $browser['browser'];
        $userLogModel->version = $browser['version'];
        $userLogModel->ip = request()->ip();
        $userLogModel->allowField(true)->save();
    }

    /**
     * 记录审核日志
     *
     * @param string $type_info 审核日志
     * @param string $order_num 订单编号
     * @param string $sh_info 审核信息
     * @Author: guanyl
     * @Date: ${DATE} ${TIME}
     */
    public function setReviewedLog($type_info='',$order_num='',$sh_info='')
    {
        $reviewedLogModel = new ReviewedLogModel();
        $reviewedLogModel->type_info = $type_info;
        $reviewedLogModel->order_num = $order_num;
        $reviewedLogModel->sh_info = $sh_info;
        $reviewedLogModel->createby = 'admin';
        $reviewedLogModel->allowField(true)->save();
    }
    public function number($checkWait){
        $number = '0';
        if (strlen($checkWait) == 1) {
            $number = '000' . ($checkWait + 1);
        }
        if (strlen($checkWait) == 2) {
            $number = '00' . ($checkWait + 1);
        }
        if (strlen($checkWait) == 3) {
            $number = '0' . ($checkWait + 1);
        }
        if (strlen($checkWait) == 4) {
            $number = $checkWait + 1;
        }
        return $number;
    }

    //获取浏览器以及版本号
    protected function getBrowser() {
        global $_SERVER;
        $agent  = $_SERVER['HTTP_USER_AGENT'];
        $browser  = '';
        $browser_ver  = '';

        if (preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $agent, $regs)) {
            $browser  = 'OmniWeb';
            $browser_ver   = $regs[2];
        }

        if (preg_match('/Netscape([\d]*)\/([^\s]+)/i', $agent, $regs)) {
            $browser  = 'Netscape';
            $browser_ver   = $regs[2];
        }

        if (preg_match('/safari\/([^\s]+)/i', $agent, $regs)) {
            $browser  = 'Safari';
            $browser_ver   = $regs[1];
        }

        if (preg_match('/MSIE\s([^\s|;]+)/i', $agent, $regs)) {
            $browser  = 'Internet Explorer';
            $browser_ver   = $regs[1];
        }

        if (preg_match('/Opera[\s|\/]([^\s]+)/i', $agent, $regs)) {
            $browser  = 'Opera';
            $browser_ver   = $regs[1];
        }

        if (preg_match('/NetCaptor\s([^\s|;]+)/i', $agent, $regs)) {
            $browser  = '(Internet Explorer ' .$browser_ver. ') NetCaptor';
            $browser_ver   = $regs[1];
        }

        if (preg_match('/Maxthon/i', $agent, $regs)) {
            $browser  = '(Internet Explorer ' .$browser_ver. ') Maxthon';
            $browser_ver   = '';
        }
        if (preg_match('/360SE/i', $agent, $regs)) {
            $browser       = '(Internet Explorer ' .$browser_ver. ') 360SE';
            $browser_ver   = '';
        }
        if (preg_match('/SE 2.x/i', $agent, $regs)) {
            $browser       = '(Internet Explorer ' .$browser_ver. ') 搜狗';
            $browser_ver   = '';
        }

        if (preg_match('/FireFox\/([^\s]+)/i', $agent, $regs)) {
            $browser  = 'FireFox';
            $browser_ver   = $regs[1];
        }

        if (preg_match('/Lynx\/([^\s]+)/i', $agent, $regs)) {
            $browser  = 'Lynx';
            $browser_ver   = $regs[1];
        }

        if(preg_match('/Chrome\/([^\s]+)/i', $agent, $regs)){
            $browser  = 'Chrome';
            $browser_ver   = $regs[1];

        }

        if ($browser != '') {
            return ['browser'=>$browser,'version'=>$browser_ver];
        } else {
            return ['browser'=>'unknow browser','version'=>'unknow browser version'];
        }
    }

    public function checkStatus(){
        $checkModel = new QualityCheckModel();
        $checkWaitModel = new QualityCheckWaitModel();
        $purOrder = new PurOrderModel();
        //所有待质检订单
        $qualityCheckWait = $checkWaitModel->select();
        foreach ($qualityCheckWait as $checkWait) {
            //质检单数量
            $qualityCheck = $checkModel->where('check_wait_num',$checkWait['check_wait_num'])->count();
            //已质检数量
            $status = $checkModel->where(['check_wait_num'=>$checkWait['check_wait_num'],'check_status'=>1])->count();
            if ($qualityCheck == $status) {
                $check_status = 4;
                $check_wait_status = 2;
                $msg = '已质检,待移库';
                $check_wait_msg = '已质检';
            }else{
                $check_status = 3;
                $check_wait_status = 1;
                $msg = '部分质检,待移库';
                $check_wait_msg = '部分质检';
            }
            //采购订单改变
            $purOrder->allowField(true)->save(['pur_order_status'=>$check_status],['pur_order_num'=>$checkWait['pur_order_num']]);
            $this->setReviewedLog("采购单","$checkWait[pur_order_num]",$msg);
            //待质检单改变
            $checkWaitModel->allowField(true)->save(['check_wait_status'=>$check_wait_status],['pur_order_num'=>$checkWait['pur_order_num']]);
            $this->setReviewedLog("待质检单","$checkWait[pur_order_num]",$check_wait_msg);
        }
    }
}
