<?php
namespace app\Api\controller;
use app\api\Model\ActionModel;
use app\Api\Model\AdminUsersModel;
use think\Request;
use think\Session;

/**
 * @title 管理员管理
 * @description 接口说明
 * @group 系统设置
 * @header name:key require:1 default: desc:秘钥(区别设置)
 */
class AdminUser extends Common
{
    /**
     * @title 管理员登录
     * @description 接口说明
     * @author 房育材
     * @url /api/AdminUser/adminLogin
     * @method POST
     *
     * @param name:user_name type:string require:1 default:空 other: desc:登录账号或手机号
     * @param name:password type:string require:1 default:孔 other: desc:登录密码
     * @return admin_id:用户id
     * @return shop_id:商户id
     * @return branch_id:机构id
     * @return user_name:用户登录名
     * @return nickname:用户昵称
     * @return mobile:手机号
     * @return email:邮箱
     * @return login_count:登陆次数
     * @return last_login_time:上次登陆时间
     * @return last_ip:上次登录ip
     * @return create_time:创建时间
     * @return update_time:修改时间
     * @return user_token 用户验证token
     * @return tiken_time 上次生成token时间
     */
    public function adminLogin(AdminUsersModel $usersModel,Request $request)
    {
        $param = $request->param();
        $where = " disabled=1 ";
        //用户user_name
        if(!empty($param['user_name'])){
            $user_name = $param['user_name'];
            $where .= " and (user_name = '$user_name' or mobile = '$user_name')";
        }else{
            $this->jkReturn(-1002, '缺少参数user_name', array(), '0');
        }
        $usersInfo = $usersModel->where($where)->find();
        if(empty($usersInfo)){
            $this->jkReturn(-1005, '查找失败、用户不存在', array(), '0');
        }else{
            $usersInfoArray = $usersInfo->toArray();
            if(!empty($param['password'])){
                if($usersInfo['password'] == md5(md5( $param['password']))){
                    //更新用户token
                    $usersInfoArray['user_token'] = md5(md5($usersInfo['admin_id'].time()));
                    $usersInfoArray['tiken_time'] = date("Y-m-d H:i:s",time());
                    $usersInfoArray['last_ip'] = $request->ip();
                    $usersInfoArray['login_count'] = $usersInfo['login_count'] + 1 ;
                    $usersInfoArray['last_login_time'] = date("Y-m-d H:i:s",time()) ;
                    $upWhere['admin_id'] = $usersInfo['admin_id'];
                    $usersInfo->allowField(true)->save($usersInfoArray,$upWhere);
                    $this->jkReturn('0000','会员信息',$usersInfo);
                }else{
                    $this->jkReturn(-1006, '验证失败、密码错误', array(), '0');
                }
            }else{
                $this->jkReturn(-1002, '缺少参数password', array(), '0');
            }

        }

    }

    /**
     * @title 获取管理员列表
     * @description 接口说明
     * @author 房育材
     * @url /api/AdminUser/getAdminUser
     * @method POST
     *
     * @param name:user_token type:string require:空 default:1 other: desc:当前登录用户token
     * @param name:admin_id type:int require:0 default:空 other: desc:用户id
     * @param name:keywords type:string require:0 default:空 other: desc:搜索关键词：手机、账号、昵称
     * @param name:orderBy type:string require:0 default:admin_id other: desc:排序字段
     * @param name:orderByUpOrDown type:string require:0 default:Desc other: desc:ASC或DESC
     * @param name:list_rows type:int require:0 default:1 other: desc:每页条数
     * @param name:page type:int require:0 default:10 other: desc:页码数
     * @return total:总数据条数
     * @return per_page:每页显示条数
     * @return current_page:当前页
     * @return last_page:上一页
     * @return data:用户列表@
     * @data admin_id:用户id shop_id:商户id branch_id:机构id user_name:用户登录名 nickname:用户昵称 mobile:手机号 email:邮箱 login_count:登陆次数 last_login_time:上次登陆时间
     * last_ip:上次登录ip create_time:创建时间 update_time:修改时间@
     * @list_follow user_id:用户id name:名称
     */
    public function getAdminUser(AdminUsersModel $usersModel,Request $request)
    {
        $param = $request->param();
        //每页展示数
        if (!empty($param['list_rows'])){
            $list_rows = $param['list_rows'];
        }else{
            $list_rows = 10;
        }

        $where = " disabled=1 ";
        if(!empty($param['keywords'])){
            $keywords = $param['keywords'];
            $where .= " and (admin_id like '%" . $keywords . "%' or user_name like '%" . $keywords . "%' or nickname like '%". $keywords . "%' or mobile like '%". $keywords . "%')";
        }
        //用户id
        if(!empty($param['admin_id'])){
            $admin_id = $param['admin_id'];
            $where .= " and admin_id= '$admin_id'";
        }

        //排序条件
        if(!empty($param['orderBy'])){
            $orderBy = $param['orderBy'];
        }else{
            $orderBy = 'admin_id';
        }
        if(!empty($param['orderByUpOrDown'])){
            $orderByUpOrDown = $param['orderByUpOrDown'];
        }else{
            $orderByUpOrDown = 'Desc';
        }

        $usersList = $usersModel->where($where)->order($orderBy.' '.$orderByUpOrDown)->paginate($list_rows);
        $this->jkReturn('0000','会员列表',$usersList);
    }

    /**
     * @title 添加管理员
     * @description 接口说明
     * @author 房育材
     * @url /api/AdminUser/addAdminUser
     * @method POST
     *
     * @param name:user_token type:string require:空 default:1 other: desc:当前登录用户token
     * @param name:keywords type:string require:0 default:空 other: desc:搜索关键词：手机、账号、昵称
     * @param name:orderBy type:string require:0 default:admin_id other: desc:排序字段
     * @param name:orderByUpOrDown type:string require:0 default:Desc other: desc:ASC或DESC
     * @param name:list_rows type:int require:0 default:1 other: desc:每页条数
     * @param name:page type:int require:0 default:10 other: desc:页码数
     * @return total:总数据条数
     * @return per_page:每页显示条数
     * @return current_page:当前页
     * @return last_page:上一页
     * @return data:用户列表@
     * @data admin_id:用户id shop_id:商户id branch_id:机构id user_name:用户登录名 nickname:用户昵称 mobile:手机号 email:邮箱 login_count:登陆次数 last_login_time:上次登陆时间
     * last_ip:上次登录ip create_time:创建时间 update_time:修改时间@
     * @list_follow user_id:用户id name:名称
     */
    public function addAdminUser(AdminUsersModel $usersModel,Request $request)
    {
        $param = $request->param();
        //每页展示数
        if (!empty($param['list_rows'])){
            $list_rows = $param['list_rows'];
        }else{
            $list_rows = 10;
        }

        $where = " disabled=1 ";
        if(!empty($param['keywords'])){
            $keywords = $param['keywords'];
            $where .= " and (admin_id like '%" . $keywords . "%' or user_name like '%" . $keywords . "%' or nickname like '%". $keywords . "%' or mobile like '%". $keywords . "%')";
        }
        //用户id
        if(!empty($param['admin_id'])){
            $admin_id = $param['admin_id'];
            $where .= " and admin_id= '$admin_id'";
        }

        //排序条件
        if(!empty($param['orderBy'])){
            $orderBy = $param['orderBy'];
        }else{
            $orderBy = 'admin_id';
        }
        if(!empty($param['orderByUpOrDown'])){
            $orderByUpOrDown = $param['orderByUpOrDown'];
        }else{
            $orderByUpOrDown = 'Desc';
        }

        $usersList = $usersModel->where($where)->order($orderBy.' '.$orderByUpOrDown)->paginate($list_rows);
        $this->jkReturn('0000','会员列表',$usersList);
    }
    /**
     * @title 获取管理员权限列表
     * @description 接口说明
     * @author 房育材
     * @url /api/AdminUser/getUserActionList
     * @method POST
     *
     * @param name:admin_id:int require:空 default:0 other: desc:用户id
     * @return data:权限列表@
     * @data action_code:方法名列表
     * @list_follow user_id:用户id name:名称
     */
    public function getUserActionList(AdminUsersModel $usersModel,Request $request,ActionModel $actionModel)
    {
        $param = $request->param();
        $where = " disabled=1 ";
        //用户id
        if(!empty($param['admin_id'])){
            $admin_id = $param['admin_id'];
            $where .= " and admin_id= '$admin_id'";
        }
        $usersInfo = $usersModel->where($where)->find();
        if(!empty($usersInfo)){
            $actionList = $usersInfo['action_list'];
            if(empty($actionList)){
                $this->jkReturn(-1001, '查找失败、用户无权限', array(), '0');
            }elseif ($actionList == 'all'){
                $actionList = 'all';
            }else{
                $actionList = unserialize($actionList);
            }
            $actionList = $this->getActionList($actionList,$actionModel);

            if(!empty($actionList)){
                $this->jkReturn('0000', '查找成功', $actionList, '0');
            }else{
                $this->jkReturn(-1001, '查找失败、用户无权限', array(), '0');
            }
        }else{
            $this->jkReturn(-1005, '查找失败、用户不存在', array(), '0');
        }
    }
    //获取等级权限列表
    public function getActionList($actionList,$actionModel){
        $where = " disabled=1 ";
        if($actionList !== 'all'){
            $where .= " and action_id in ('$actionList')";
        }
        $actionList = $actionModel->where($where)->order('sort asc')->select();
        $actionList1 = array();
        if(!empty($actionList)){
            foreach ($actionList as $item){
                array_push($actionList1,$item['action_path'].$item['action_code']);
            }
        }else{
            $actionList1 = [];
        }
        return $actionList1;
    }
}
