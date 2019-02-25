<?php

namespace app\api\model;
use app\api\model\CommonModel;

class ApprovalModel extends CommonModel
{
    protected $table = 'wms_approval';
    //-- 设置主键
    protected $pk = 'id';
    //--隐藏属性
    protected $hidden = [];

    //-- 属性获取器 是否支持预定
     public function getApplyStatusAttr($apply_status)
     {
         switch ($apply_status) {
             case 0:
                 return '未审核';
             case 1:
                 return '初审中';
             case 2:
                 return '初审通过';
             case 3:
                 return '二审通过';
             case 4:
                 return '终审通过';
             case 5:
                 return '审核不通过';
             default:
                 return '已取消';
         }
     }
}