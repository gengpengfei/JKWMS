<?php

namespace app\api\model;
use app\api\model\CommonModel;

class RowShelfModel extends CommonModel
{
    protected $table = 'wms_row_shelf';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [
        'disabled'
    ];
}