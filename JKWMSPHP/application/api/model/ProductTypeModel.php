<?php

namespace app\api\model;
use app\api\model\CommonModel;

class ProductTypeModel extends CommonModel
{
    protected $table = 'wms_product_type';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [

    ];
}