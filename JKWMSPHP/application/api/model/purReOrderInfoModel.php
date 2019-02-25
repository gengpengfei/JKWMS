<?php

namespace app\api\model;
use app\api\model\CommonModel;

class purReOrderInfoModel extends CommonModel
{
    protected $table = 'wms_pur_re_order_info';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [
        'disabled'
    ];
}