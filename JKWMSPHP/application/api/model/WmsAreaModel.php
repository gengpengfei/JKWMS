<?php
namespace app\api\Model;

class WmsAreaModel extends CommonModel{
    // 设置当前模型对应的完整数据表名称
    public $table = 'wms_area';
    protected $hidden = ['create_time','update_time'];
}