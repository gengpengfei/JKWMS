<?php


namespace app\api\model;
use app\api\model\CommonModel;

class WarehousingModel extends CommonModel
{
    protected $table = 'wms_warehousing_order';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [
        'disabled'
    ];
    //-- 属性获取器 是否支持预定
    public function getInFlagAttr($apply_status)
    {
        switch ($apply_status) {
            case 1:
                return '已入库';
            default:
                return '待入库';
        }
    }
    //-- 属性获取器 是否支持预定
    public function getReviewedStatusAttr($apply_status)
    {
        switch ($apply_status) {
            case 1:
                return '待审核';
            case 2:
                return '审核通过';
            case 3:
                return '审核不通过';
            default:
                return '新建';
        }
    }
    //-- 属性获取器 是否支持预定
    public function getOrderTypeAttr($OrderType)
    {
        switch ($OrderType) {
            case 1:
                return '采购入库';
            case 2:
                return '调拨入库';
            case 3:
                return '其他入库';
            case 4:
                return '盘盈入库';
            case 5:
                return '大客户退回入库';
            case 6:
                return '换货入库';
            default:
                return '待收货';
        }
    }
}