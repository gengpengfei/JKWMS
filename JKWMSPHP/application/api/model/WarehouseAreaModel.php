<?php

namespace app\api\model;
use app\api\model\CommonModel;

class WarehouseAreaModel extends CommonModel
{
    protected $table = 'wms_warehouse_area';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [
        'disabled'
    ];
}