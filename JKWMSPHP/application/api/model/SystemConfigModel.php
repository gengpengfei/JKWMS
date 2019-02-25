<?php

namespace app\api\model;
use app\api\model\CommonModel;

class SystemConfigModel extends CommonModel
{
    protected $table = 'wms_system_config';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [

    ];
}