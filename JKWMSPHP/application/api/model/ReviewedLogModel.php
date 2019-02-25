<?php

namespace app\api\model;
use app\api\model\CommonModel;

class ReviewedLogModel extends CommonModel
{
    protected $table = 'wms_reviewed_log';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [];
}