<?php

namespace app\api\model;
use app\api\model\CommonModel;

class OrderInModel extends CommonModel
{
    protected $table = 'wms_order_in_copy';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [];
}