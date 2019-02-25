<?php
namespace app\api\Model;

class AdminUsersModel extends CommonModel{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'admin_user';
    protected $hidden = ['is_admin','action_list','token_time'];
}