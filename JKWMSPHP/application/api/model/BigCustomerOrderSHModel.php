<?php

namespace app\api\model;
use app\api\model\CommonModel;

class BigCustomerOrderSHModel extends CommonModel
{
    protected $table = 'wms_big_cust_order_sh';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [];
}