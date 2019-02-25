<?php

namespace app\api\model;
use app\api\model\CommonModel;

class ReceiveModel extends CommonModel
{
    protected $table = 'wms_get_order';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [];
}