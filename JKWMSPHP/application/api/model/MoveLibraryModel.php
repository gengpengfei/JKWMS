<?php

namespace app\api\model;
use app\api\model\CommonModel;

class MoveLibraryModel extends CommonModel
{
    protected $table = 'wms_move_library';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [
        'disabled'
    ];
    //-- 属性获取器 是否支持预定
    public function getMoveStatusAttr($apply_status)
    {
        switch ($apply_status) {
            case 1:
                return '已移库';
            default:
                return '待移库';
        }
    }
    //-- 属性获取器 是否支持预定
    public function getReviewedStatusAttr($apply_status)
    {
        switch ($apply_status) {
            case 1:
                return '审核通过';
            case 2:
                return '审核不通过';
            default:
                return '待审核';
        }
    }
}