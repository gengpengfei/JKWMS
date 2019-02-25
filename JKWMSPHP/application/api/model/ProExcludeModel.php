<?php

namespace app\api\model;
use app\api\model\CommonModel;

class ProExcludeModel extends CommonModel
{
    protected $table = 'wms_product_exclude';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [

    ];
}