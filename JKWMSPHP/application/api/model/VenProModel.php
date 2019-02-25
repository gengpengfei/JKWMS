<?php

namespace app\api\model;
use app\api\model\CommonModel;

class VenProModel extends CommonModel
{
    protected $table = 'wms_ven_pro';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [

    ];
}