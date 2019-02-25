<?php

namespace app\api\model;
use app\api\model\CommonModel;

class QualityCheckWaitInfoModel extends CommonModel
{
    protected $table = 'wms_quality_check_wait_info';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [];
}