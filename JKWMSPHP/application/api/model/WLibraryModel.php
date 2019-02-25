<?php

namespace app\api\model;
use app\api\model\CommonModel;

class WLibraryModel extends CommonModel
{
    protected $table = 'wms_wlibrary';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [
        'disabled'
    ];
}