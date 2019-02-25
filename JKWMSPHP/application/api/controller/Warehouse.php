<?php
/**
 * Created by PhpStorm.
 * User: guanyl
 * Date: 2018/11/13
 * Time: 14:12
 */

namespace app\Api\controller;
use app\api\model\WarehouseAreaModel;
use app\api\model\WarehouseModel;
use think\Request;

/**
 * @title 仓库设置
 * @description 接口说明
 * @group 仓库管理
 * @header name:key require:1 default: desc:秘钥(区别设置)

 */

class Warehouse extends Common
{
    
    /**
     * @title 仓库列表
     * @description 接口说明
     * @author gyl
     * @url /Warehouse/index
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:仓库编号、仓库名称
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1other: desc:页数
     *
     * @return warehouseList:仓库列表@
     * @warehouseList warehouse_num:仓库编号 warehouse_name:仓库名称 pic_name:负责人 pic_tel:负责人电话 province:仓库所在省 city:仓库所在市 area:仓库所在区 address:具体地址 remark:备注  create_by:创建人 create_date:创建时间 modify_by:修改人 modify_date:修改时间
     * @return warehouseCount:仓库数量
     */
    public function index(Request $request, WarehouseModel $warehouseModel){
        $param = $request->param();
        $where = "disabled = 1";
        if (!empty($param['search_key'])) {
            $where .= " and (warehouse_num like  '%" . $param['search_key'] . "%' or warehouse_name like '%" . $param['search_key'] . "%') ";
        }
        $warehouseList = $warehouseModel
            ->where($where)->limit($param["limit"]??10)->page($param['page']??1)->select();
        $warehouseCount = $warehouseModel->where($where)->count();
        $data = array(
            'warehouseList'=>$warehouseList,
            'warehouseCount'=>$warehouseCount
        );
        $this->jkReturn('0000','仓库列表',$data);
    }

    /**
     * @title 仓库详情
     * @description 接口说明
     * @author gyl
     * @url /Warehouse/warehouseDetail
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:int require:1 default: other: desc:仓库id
     *
     * @return id:仓库id
     * @return warehouse_num:仓库编号
     * @return warehouse_name:仓库名称
     * @return pic_name:负责人
     * @return pic_tel:负责人电话
     * @return pic_mobile:负责人手机
     * @return pic_fax:负责人传真
     * @return pic_qq:负责人qq
     * @return pic_wechat:负责人微信
     * @return pic_email:负责人email
     * @return province:仓库所在省
     * @return city:仓库所在市
     * @return area:仓库所在区
     * @return address:具体地址
     * @return remark:备注
     * @return receiving_date:接收日期
     * @return create_by:创建人
     * @return create_date:创建时间
     * @return modify_by:修改人
     * @return modify_date:修改时间
     *
     */
    public function warehouseDetail(Request $request, WarehouseModel $warehouseModel){
        $param = $request->param();
        $warehouseDetail = $warehouseModel->where('id',$param['id'])->find();
        $this->jkReturn('0000','仓库详情',$warehouseDetail);
    }

    /**
     * @title 仓库添加
     * @description 接口说明
     * @author gyl
     * @url /Warehouse/warehouseAdd
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:warehouse_num type:string require:1 default: other: desc:仓库编号
     * @param name:warehouse_name type:string require:1 default: other: desc:仓库名称
     * @param name:pic_name type:string require:1 default: other: desc:负责人
     * @param name:pic_tel type:string require:1 default: other: desc:负责人电话
     * @param name:pic_mobile type:string require:1 default: other: desc:负责人手机
     * @param name:pic_fax type:string require:0 default: other: desc:负责人传真
     * @param name:pic_qq type:string require:0 default: other: desc:负责人qq
     * @param name:pic_wechat type:string require:0 default: other: desc:负责人微信
     * @param name:pic_email type:string require:0 default: other: desc:负责人email
     * @param name:province type:string require:1 default: other: desc:仓库所在省
     * @param name:city type:string require:1 default: other: desc:仓库所在市
     * @param name:area type:string require:1 default: other: desc:仓库所在区
     * @param name:address type:string require:1 default: other: desc:具体地址
     * @param name:remark type:string require:0 default: other: desc:备注
     * @param name:receiving_date type:string require:0 default: other: desc:接收日期
     * @param name:create_by type:string require:0 default: other: desc:创建人
     * @param name:create_date type:string require:0 default: other: desc:创建时间
     * @param name:modify_by type:string require:0 default: other: desc:修改人
     * @param name:modify_date type:string require:0 default: other: desc:修改时间
     *
     */
    public function warehouseAdd(Request $request, WarehouseModel $warehouseModel){
        $param = $request->param();
        $result = $warehouseModel->allowField(true)->save($param);
        if($result){
            $warehouse_id = $warehouseModel->getLastInsID();
            $this->setAdminUserLog("新增","添加仓库","wms_warehouse",$warehouse_id);
            $this->jkReturn('0000','添加成功');
        }else{
            $this->jkReturn('-1004','添加失败');
        }
    }
    /**
     * @title 仓库编辑
     * @description 接口说明
     * @author gyl
     * @url /Warehouse/warehouseEdit
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:string require:1 default: other: desc:仓库id
     * @param name:warehouse_num type:string require:1 default: other: desc:仓库编号
     * @param name:warehouse_name type:string require:1 default: other: desc:仓库名称
     * @param name:pic_name type:string require:1 default: other: desc:负责人
     * @param name:pic_tel type:string require:1 default: other: desc:负责人电话
     * @param name:pic_mobile type:string require:1 default: other: desc:负责人手机
     * @param name:pic_fax type:string require:0 default: other: desc:负责人传真
     * @param name:pic_qq type:string require:0 default: other: desc:负责人qq
     * @param name:pic_wechat type:string require:0 default: other: desc:负责人微信
     * @param name:pic_email type:string require:0 default: other: desc:负责人email
     * @param name:province type:string require:1 default: other: desc:仓库所在省
     * @param name:city type:string require:1 default: other: desc:仓库所在市
     * @param name:area type:string require:1 default: other: desc:仓库所在区
     * @param name:address type:string require:1 default: other: desc:具体地址
     * @param name:remark type:string require:0 default: other: desc:备注
     * @param name:receiving_date type:string require:0 default: other: desc:接收日期
     * @param name:create_by type:string require:0 default: other: desc:创建人
     * @param name:create_date type:string require:0 default: other: desc:创建时间
     * @param name:modify_by type:string require:0 default: other: desc:修改人
     * @param name:modify_date type:string require:0 default: other: desc:修改时间
     *
     */
    public function warehouseEdit(Request $request, WarehouseModel $warehouseModel){
        $param = $request->param();
        $warehouse = $warehouseModel->where('id',$param['id'])->select();
        if (count($warehouse) <= 0) {
            $this->jkReturn('-1004','仓库不存在');
        }
        $where = ['id'=>$param['id']];
        $result = $warehouseModel->allowField(true)->save($param,$where);
        if($result){
            $this->setAdminUserLog("更新","更新仓库信息","wms_warehouse",$param['id']);
            $this->jkReturn('0000','更新成功');
        }else{
            $this->jkReturn('-1004','更新失败');
        }
    }

    /**
     * @title 仓库删除
     * @description 接口说明
     * @author gyl
     * @url /Warehouse/warehouseDel
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:array require:0 default: other: desc:仓库id(批量删除)
     *
     */
    public function warehouseDel(Request $request, WarehouseModel $warehouseModel, WarehouseAreaModel $warehouseAreaModel){
        $param = $request->param();
        //多个删除
        foreach ($param['id'] as $v){
            /**
             * 查看仓库下是否有库区，否则不允许删除
             */
            $warehouse = $warehouseModel->where(['id'=>$v])->find();
            $warehouseArea = $warehouseAreaModel->where(['warehouse_num'=>$warehouse->warehouse_num])->select();
            if (count($warehouseArea) >= 1) {
                $this->jkReturn('-1004',"此仓库下还有库区，不允许删除,id为'$v'");
            }
            if(!$warehouseModel->where(['id'=>$v])->delete()){
                $this->jkReturn('-1004',"删除失败,id为'$v'");
            }
            $this->setAdminUserLog("删除","删除仓库","wms_warehouse",$v);
        }
        $this->jkReturn('0000','删除成功');
    }
}