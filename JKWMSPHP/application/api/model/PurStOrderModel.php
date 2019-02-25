<?php

namespace app\api\model;
use app\api\model\CommonModel;

class PurStOrderModel extends CommonModel
{
    protected $table = 'wms_pur_storder';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [];
}