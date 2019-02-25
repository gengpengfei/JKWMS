<?php
namespace app\Api\controller;
use app\api\Model\WmsAreaModel;
use think\Request;

/**
 * @title 仓库设置
 * @description 接口说明
 * @group 地址管理
 * @header name:key require:1 default: desc:秘钥(区别设置)
 */
class WmsArea extends Common
{
    /**
     * @title 获取下级地址列表
     * @description 接口说明
     * @author 耿鹏飞
     * @url /api/WmsArea/areaList
     * @method POST
     *
     * @param area_name:area_name type:string require:0 default:空 other: desc:地址名称（为空默认获取顶层地址）
     * @param area_level:area_level type:int require:1 default:空 other: desc:地址层级
     * @return id:地址id
     * @return area_name:地址名称
     * @return zip_code:机构id
     * @return area_level:层级
     * @return parent_id:父级id
     */
    public function areaList(WmsAreaModel $wmsAreaModel,Request $request)
    {
        $param = $request->param();
        $list = [];
        if(!empty($param['area_name'])){
            $info = $wmsAreaModel->where("area_name='".$param['area_name']."' and area_level=".$param['area_level'])->find();
            if($info){
                $list = $wmsAreaModel->where("parent_id=".$info['id'])->select();
            }
        }else{
            $list = $wmsAreaModel->where("area_level=1")->select();
        }
        $this->jkReturn('0000','下级地址列表',$list);     
    }
}
