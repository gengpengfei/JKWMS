<?php

namespace app\api\model;
use app\api\model\CommonModel;

class QualityCheckWaitModel extends CommonModel
{
    protected $table = 'wms_quality_check_wait';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [
        'disabled'
    ];

    //-- 属性获取器 是否支持预定
    public function getCheckWaitStatusAttr($apply_status)
    {
        switch ($apply_status) {
            case 1:
                return '部分质检';
            case 2:
                return '已质检';
            default:
                return '待质检';
        }
    }
}