<?php
namespace app\api\Model;

class AdminUserActionModel extends CommonModel{
    // 设置当前模型对应的完整数据表名称
    public $table = 'admin_user_action';
    protected $hidden = ['create_time','update_time'];


}