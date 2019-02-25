<?php

namespace app\api\model;
use app\api\model\CommonModel;

class ReceiveReviewModel extends CommonModel
{
    protected $table = 'wms_get_review';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [];
}