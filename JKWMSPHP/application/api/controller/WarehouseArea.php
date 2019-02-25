<?php
/**
 * Created by PhpStorm.
 * User: guanyl
 * Date: 2018/11/13
 * Time: 16:19
 */

namespace app\Api\controller;
use app\api\model\RowShelfModel;
use app\api\model\WareaRowModel;
use app\api\model\WarehouseAreaModel;
use app\api\model\WarehouseModel;
use think\Request;

/**
 * @title 库区设置
 * @description 接口说明
 * @group 仓库管理
 * @header name:key require:1 default: desc:秘钥(区别设置)

 */

class WarehouseArea extends Common
{
    /**
     * @title 库区列表
     * @description 接口说明
     * @author gyl
     * @url /WarehouseArea
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:仓库编号、仓库名称、库区编号、库区名称
     * @param name:warea_type type:string require:0 default: other: desc:库区类型（0：收货区，1：存储区，2：次品区，3：拣货区）
     * @param name:warehouse_num type:string require:0 default: other: desc:仓库编号
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1other: desc:页数
     *
     * @return warehouseAreaList:库区列表@
     * @warehouseAreaList warehouse_num:仓库编号 warehouse_name:仓库名称 warea_num:库区编号 warea_name:库区名称 warea_type:库区类型（0：收货区，1：存储区，2：次品区，3：拣货区）  create_by:创建人 create_date:创建时间 modify_by:修改人 modify_date:修改时间
     * @return warehouseCount:库区数量
     */
    public function index(Request $request, WarehouseAreaModel $warehouseAreaModel){
        $param = $request->param();
        $where = "a.disabled = 1";
        $field = "a.*,w.warehouse_name";
        if (!empty($param['search_key'])) {
            $where .= " and (a.warea_num like '%" . $param['search_key'] . "%' or a.warea_name like '%" . $param['search_key'] . "%' or w.warehouse_num like '%" . $param['search_key'] . "%' or w.warehouse_name like '%" . $param['search_key'] . "%') ";
        }
        if (!empty($param['warehouse_num'])) {
            $where .= " and a.warehouse_num = '$param[warehouse_num]'";
        }
        if (isset($param['warea_type'])&&$param['warea_type']!==''&&$param['warea_type']!==false) {
            $where .= " and a.warea_type = '$param[warea_type]'";
        }
        $warehouseAreaList = $warehouseAreaModel
            ->field($field)
            ->alias('a')
            ->where($where)
            ->join('wms_warehouse w','w.warehouse_num = a.warehouse_num','left')
            ->limit($param["limit"]??10)
            ->page($param['page']??1)
            ->select();
        $warehouseCount = $warehouseAreaModel
            ->alias('a')
            ->where($where)
            ->join('wms_warehouse w','w.warehouse_num = a.warehouse_num','left')
            ->count();
        $data = array(
            'warehouseAreaList'=>$warehouseAreaList,
            'warehouseCount'=>$warehouseCount
        );
        $this->jkReturn('0000','库区列表',$data);
    }

    /**
     * @title 库区详情
     * @description 接口说明
     * @author gyl
     * @url /WarehouseArea/warehouseAreaDetail
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param id:id type:int require:1 default: other: desc:库区id
     * @return warehouse_num:仓库编号
     * @return warea_num:库区编号
     * @return warea_name:库区名称
     * @return warea_type:库区类型（0：收货区，1：存储区，2：次品区，3：拣货区）
     * @return remark:备注
     * @return create_by:创建人
     * @return create_date:创建时间
     * @return modify_by:修改人
     * @return modify_date:修改时间
     *
     */
    public function WarehouseAreaDetail(Request $request, WarehouseAreaModel $warehouseAreaModel){
        $param = $request->param();
        $warehouseAreaDetail = $warehouseAreaModel->where('id',$param['id'])->find();
        $this->jkReturn('0000','库区详情',$warehouseAreaDetail);
    }

    /**
     * @title 仓库列表
     * @description 接口说明
     * @author gyl
     * @url /WarehouseArea/warehouse
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     *
     * @return warehouse_num:仓库编号
     * @return warehouse_name:仓库名称
     */
    public function Warehouse(WarehouseModel $warehouseModel){
        $where = "disabled = 1";
        $warehouseList = $warehouseModel->field("id,warehouse_num,warehouse_name")->where($where)->select();
        $this->jkReturn('0000','仓库列表',$warehouseList);
    }
    /**
     * @title 库区添加
     * @description 接口说明
     * @author gyl
     * @url /WarehouseArea/warehouseAreaAdd
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:warehouse_num type:string require:1 default: other: desc:仓库编号
     * @param name:warea_num type:string require:1 default: other: desc:库区编号
     * @param name:warea_name type:string require:1 default: other: desc:库区名称
     * @param name:warea_type type:string require:1 default: other: desc:库区类型（0：收货区，1：存储区，2：次品区，3：拣货区）
     * @param name:remark type:string require:0 default: other: desc:备注
     * @param name:create_by type:string require:0 default: other: desc:创建人
     * @param name:create_date type:string require:0 default: other: desc:创建时间
     * @param name:modify_by type:string require:0 default: other: desc:修改人
     * @param name:modify_date type:string require:0 default: other: desc:修改时间
     *
     */
    public function warehouseAreaAdd(Request $request, WarehouseAreaModel $warehouseAreaModel){
        $param = $request->param();
        if (empty($param['warehouse_num'])) {
            $this->jkReturn('-1004','请填写仓库编号');
        }
        $result = $warehouseAreaModel->allowField(true)->save($param);
        if($result){
            $warehouseArea_id = $warehouseAreaModel->getLastInsID();
            $this->setAdminUserLog("新增","添加库区","wms_warehouse_area",$warehouseArea_id);
            $this->jkReturn('0000','添加成功');
        }else{
            $this->jkReturn('-1004','添加失败');
        }
    }
    /**
     * @title 库区编辑
     * @description 接口说明
     * @author gyl
     * @url /WarehouseArea/warehouseAreaEdit
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:warehouse_num type:string require:1 default: other: desc:仓库编号
     * @param name:warea_num type:string require:1 default: other: desc:库区编号
     * @param name:warea_name type:string require:1 default: other: desc:库区名称
     * @param name:warea_type type:string require:1 default: other: desc:库区类型（0：收货区，1：存储区，2：次品区，3：拣货区）
     * @param name:remark type:string require:0 default: other: desc:备注
     * @param name:create_by type:string require:0 default: other: desc:创建人
     * @param name:create_date type:string require:0 default: other: desc:创建时间
     * @param name:modify_by type:string require:0 default: other: desc:修改人
     * @param name:modify_date type:string require:0 default: other: desc:修改时间
     *
     */
    public function warehouseAreaEdit(Request $request, WarehouseAreaModel $warehouseAreaModel){
        $param = $request->param();
        $warehouse = $warehouseAreaModel->where('id',$param['id'])->select();
        if (count($warehouse) <= 0) {
            $this->jkReturn('-1004','库区不存在');
        }
        if (empty($param['warehouse_num'])) {
            $this->jkReturn('-1004','请填写仓库编号');
        }
        $where = ['id'=>$param['id']];
        $result = $warehouseAreaModel->allowField(true)->save($param,$where);
        if($result){
            $this->setAdminUserLog("更新","更新库区信息","wms_warehouse_area",$param['id']);
            $this->jkReturn('0000','更新成功');
        }else{
            $this->jkReturn('-1004','更新失败');
        }
    }

    /**
     * @title 库区删除
     * @description 接口说明
     * @author gyl
     * @url /WarehouseArea/warehouseAreaDel
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:array require:0 default: other: desc:库区id(批量删除)
     *
     */
    public function warehouseAreaDel(Request $request, RowShelfModel $rowShelfModel, WarehouseAreaModel $warehouseAreaModel){
        $param = $request->param();
        //多个删除
        foreach ($param['id'] as $v){
            /**
             * 查看库区下是否有库区排，否则不允许删除
             */
            $warehouseArea = $warehouseAreaModel->where(['id'=>$v])->find();
            $rowShelf = $rowShelfModel->where(['warea_num'=>$warehouseArea->warea_num])->select();
            if (count($rowShelf) >= 1) {
                $this->jkReturn('-1004',"此库区下还有货架，不允许删除,id为'$v'");
            }
            if(!$warehouseAreaModel->where(['id'=>$v])->delete()){
                $this->jkReturn('-1004',"删除失败,id为'$v'");
            }
            $this->setAdminUserLog("删除","删除库区","wms_warehouse",$v);
        }
        $this->jkReturn('0000','删除成功');
    }
}