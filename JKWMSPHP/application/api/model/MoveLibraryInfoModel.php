<?php

namespace app\api\model;
use app\api\model\CommonModel;

class MoveLibraryInfoModel extends CommonModel
{
    protected $table = 'wms_move_library_info';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [
    ];
}