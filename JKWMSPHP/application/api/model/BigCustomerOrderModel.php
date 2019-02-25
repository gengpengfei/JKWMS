<?php

namespace app\api\model;
use app\api\model\CommonModel;

class BigCustomerOrderModel extends CommonModel
{
    protected $table = 'wms_big_cust_order';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [];

    //-- 属性获取器 是否支持预定
    public function getApplyStatusAttr($apply_status)
    {
        switch ($apply_status) {
            case 0:
                return '未提交';
            case 1:
                return '提交方案';
            case 2:
                return '方案提交初审';
            case 3:
                return '初审通过';
            case 4:
                return '方案未通过';
            case 5:
                return '方案已确认';
            case 6:
                return '渠道审核通过';
            case 7:
                return '提交到总裁';
            case 8:
                return '终审通过';
            default:
                return '已生成订单';
        }
    }
}