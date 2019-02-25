<?php

namespace app\api\model;
use app\api\model\CommonModel;

class ProOfflineModel extends CommonModel
{
    protected $table = 'wms_product_offline';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [];
    /*protected $append = [
        'applyStatus'
    ];*/

    //-- 属性获取器 是否支持预定
   /* public function getApplyStatusAttr($value,$data)
    {
        switch ($data['apply_status']) {
            case 1:
                return '终审通过';
            case 2:
                return '二审通过，终审中';
            case 3:
                return '初审通过，二审中';
            case 4:
                return '已申请，初审中';
            case 5:
                return '已拒绝';
            default:
                return '待申请';
        }
    }*/
}