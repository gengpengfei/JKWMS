<?php

namespace app\api\model;
use app\api\model\CommonModel;

class QualityCheckModel extends CommonModel
{
    protected $table = 'wms_quality_check';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [
        'disabled'
    ];

    //-- 属性获取器 是否支持预定
    public function getApplyStatusAttr($apply_status)
    {
        switch ($apply_status) {
            case 1:
                return '已质检';
            default:
                return '待质检';
        }
    }
}