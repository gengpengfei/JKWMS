<?php

namespace app\api\model;
use app\api\model\CommonModel;

class QualityCheckInfoModel extends CommonModel
{
    protected $table = 'wms_quality_check_info';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [];
}