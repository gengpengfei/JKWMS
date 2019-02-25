<?php

namespace app\api\model;
use app\api\model\CommonModel;

class OrderOutDetailModel extends CommonModel
{
    protected $table = 'wms_order_out_copy_detail';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [];
}