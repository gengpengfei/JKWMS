<?php
namespace app\api\model;

use think\Model;

class CommonModel extends Model {

    // 定义时间戳字段名
    protected $createTime = 'create_date';
    protected $updateTime = 'modify_date';
    //use \app\api\traits\GetConfig;


}