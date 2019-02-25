<?php


namespace app\api\model;
use app\api\model\CommonModel;

class WarehousingInfoModel extends CommonModel
{
    protected $table = 'wms_warehousing_order_info';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [
        'disabled'
    ];
}