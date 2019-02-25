<?php

namespace app\api\model;
use app\api\model\CommonModel;

class PurOrderModel extends CommonModel
{
    protected $table = 'wms_pur_order';
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
                return '待收货验收入库';
            case 2:
                return '待质检入库';
            case 3:
                return '部分质检、待移库';
            case 4:
                return '已质检、待移库';
            case 5:
                return '已质检、部分移库';
            case 6:
                return '已质检、已移库7';
            case 7:
                return '已入库';
            default:
                return '待审核';
        }
    }
}