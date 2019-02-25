<?php

namespace app\api\model;
use app\api\model\CommonModel;

class ReceiveInfoModel extends CommonModel
{
    protected $table = 'wms_get_pur_pro';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [];
}