<?php

namespace app\api\model;
use app\api\model\CommonModel;

class VendorModel extends CommonModel
{
    protected $table = 'wms_vendor';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [
        'disabled'
    ];
}