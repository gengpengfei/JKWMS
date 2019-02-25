<?php
/**
 * Created by PhpStorm.
 * User: guanyl
 * Date: 2018/11/26
 * Time: 17:24
 */

namespace app\Api\controller;


use app\api\model\OrderInDetailModel;
use app\api\model\OrderOutDetailModel;
use app\api\model\OrderOutModel;
use app\api\model\ReviewedLogModel;
use think\Db;
use think\Request;


/**
 * @title 出库单管理
 * @description 接口说明
 * @group 采购管理
 * @header name:key require:1 default: desc:秘钥(区别设置)

 */
class Inventory extends Common
{
    /**
     * @title 出库单列表
     * @description 接口说明
     * @author gyl
     * @url /Inventory/orderOutList
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:订单编号
     * @param name:wh_id type:string require:0 default: other: desc:仓库id
     * @param name:start_date type:int require:0 default: 1 other: desc:搜索开始日期
     * @param name:end_date type:int require:0 default: 1 other: desc:搜索结束日期
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1other: desc:页数
     *
     * @return orderOutList:出库单列表@
     * @orderOutList order_code:订单编号 order_type:订单类型 wh_id:仓库id warehouse_name:出库仓库 入库仓库:供应商 remark:备注 create_by:创建人 create_date：创建时间 out_flag:是否出库(0：否，1：是)
     * @return orderOutCount:出库单数量
     */
    public function orderOutList(Request $request, OrderOutModel $orderOutModel){
        $param = $request->param();
        $where = "o.disabled = 1 and o.order_type = 10";
        $field = "o.id,o.order_code,o.order_type,o.wh_id,w.warehouse_name,o.remark,o.create_by,o.create_date,o.out_flag";
        if (!empty($param['search_key'])) {
            $where .= " and o.order_code like '%" . $param['search_key'] . "%' ";
        }
        if (!empty($param['start_date']) && !empty($param['end_date'])) {
            $where .= " and date_format(o.create_date,'%Y-%m-%d') >= date_format('" . $param['start_date'] . "','%Y-%m-%d')";
            $where .= " and date_format(o.create_date,'%Y-%m-%d') <= date_format('" . $param['end_date'] . "','%Y-%m-%d')";
        }
        $orderOutList = $orderOutModel
            ->field($field)
            ->alias('o')
            ->where($where)
            ->join('wms_warehouse w','w.id = o.wh_id','left')
            ->limit($param["limit"]??10)
            ->page($param['page']??1)
            ->select();
        $orderOutCount = $orderOutModel
            ->field($field)
            ->alias('o')
            ->where($where)
            ->join('wms_warehouse w','w.id = o.wh_id','left')
            ->count();
        $data = array(
            'orderOutList'=>$orderOutList,
            'orderOutCount'=>$orderOutCount
        );
        $this->jkReturn('0000','出库单列表',$data);
    }

    /**
     * @title 出库订单详情
     * @description 接口说明
     * @author gyl
     * @url /Inventory/orderOutDetail
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     * @param name:pur_order_num type:int require:1 default: other: desc:采购订单编号
     *
     * @return order_code:出库单编号
     * @return create_date:出库单日期
     * @return order_type:出库单类型
     * @return wh_id:出库仓库
     * @return postage:邮费
     * @return remark:备注
     * @return product_info:商品信息@
     * @product_info product_num:商品编号 product_name:商品名称 product_date:生产日期 pro_out_amount:出库数量
     *
     */
    public function orderOutDetail(Request $request, OrderOutModel $orderOutModel, OrderOutDetailModel $outDetailModel) {
        $param = $request->param();
        $where = "o.id = $param[id]";
        $field = "o.id,o.order_code,o.create_date,o.order_type,o.wh_id,w.warehouse_name,o.postage,o.remark";
        $orderOutDetail = $orderOutModel
            ->field($field)
            ->alias('o')
            ->where($where)
            ->join('wms_warehouse w','w.id = o.wh_id','left')
            ->find();
        $orderOutDetail['order_type_name'] = "退货供应商";
        if (count($orderOutDetail) >= 1) {
            $orderOutDetail['product_info'] = $outDetailModel
                ->field('id,product_num,product_name,pro_out_amount')
                ->where('order_code',$orderOutDetail['order_code'])
                ->select();
        }
        $this->jkReturn('0000','出库订单详情',$orderOutDetail);
    }

    /**
     * @title 出库单添加
     * @description 接口说明
     * @author gyl
     * @url /Inventory/orderOutAdd
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:wh_id type:string require:1 default: other: desc:(必选)出库仓库
     * @param name:wh type:string require:0 default:供应商 other: desc:入库仓库
     * @param name:remark type:string require:0 default: other: desc:备注
     * @param name:product_info type:array require:0 default: other: desc:商品信息
     *
     * @param name:product_num type:string require:1 default: other: desc:商品编号
     * @param name:product_name type:string require:1 default: other: desc:商品名称
     * @param name:pro_out_amount type:string require:1 default: other: desc:出库数量
     * @param name:product_date type:string require:1 default: other: desc:生产日期
     * @param name:supplier type:string require:1 default: other: desc:供应商编号
     * @param name:supplier_name type:string require:1 default: other: desc:供应商名称
     * @param name:price type:string require:1 default: other: desc:进价
     *
     */
    public function orderOutAdd(Request $request, OrderOutModel $orderOutModel, OrderOutDetailModel $outDetailModel){
        $param = $request->param();
        $date = date('Y-m-d');
        $where = "date_format(create_date,'%Y-%m-%d') = date_format('" . $date . "','%Y-%m-%d')";
        $orderOut = $orderOutModel->where($where)->count();
        $number = $this->number($orderOut);
        $param['order_code'] = 'OUT-' . date('Ymd') . '-' . $number;//出库单编号
        $param['create_date'] = date('Y-m-d H:i:s');//出库单日期
        $param['order_type'] = 10;//出库单类型
        $result = $orderOutModel->allowField(true)->save($param);
        if($result){
            $orderOutId = $orderOutModel->getLastInsID();
            $this->setAdminUserLog("新增","出库单新建","wms_order_out_copy",$orderOutId);
            if (!empty($param['product_info'])) {
                foreach ($param['product_info'] as &$product_info) {
                    $product_info['order_code'] = $param['order_code'];//出库单编号
                }
            }
            $outDetailModel->allowField(true)->saveAll($param['product_info']);
            $this->jkReturn('0000','添加成功');
        }else{
            $this->jkReturn('-1004','添加失败');
        }
    }

    /**
     * @title 出库单商品列表
     * @description 接口说明
     * @author gyl
     * @url /Inventory/productList
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:varchar require:0 default: other: desc:查询值
     * @param name:wh_id type:varchar require:1 default: other: desc:仓库id
     *
     * @return product_num:商品编号
     * @return product_name:商品名称
     * @return vendor_num:供应商编号
     * @return vendor_name:供应商名称
     * @return price:进价
     * @return specifications_num:规格
     * @return unit:计量单位
     * @return current_pur_start_time:生产日期
     * @return shelf_month:保质期
     * @return carton:箱规
     * @return rate:库存
     */
    public function productList(Request $request, OrderInDetailModel $orderDetailModel){
        $param = $request->param();
        $param['wh_id'] = 1;
        if (empty($param['wh_id'])) {
            $this->jkReturn('-1004','请选择出库仓库！');
        }
        $where = "i.disabled = 1 and i.wh_id = '$param[wh_id]' and s.stock_amount <> 0";
        $field = "p.product_num,p.product_name,v.vendor_num,v.vendor_name,p.price,p.specifications_num,p.unit,p.current_pur_start_time,p.shelf_month,p.carton,s.stock_amount as rate";
        if (!empty($param['search_key'])) {
            $where .= " and (p.product_num like '%" . $param['search_key'] . "%' or p.product_name like '%" . $param['search_key'] . "%') ";
        }
        $productList = $orderDetailModel
            ->field($field)
            ->alias('d')
            ->where($where)
            ->join('wms_order_in_copy i','d.order_code = i.order_num','left')
            ->join('wms_stock s','d.product_num = s.product_num and i.wh_id = s.wh_id','left')
            ->join('wms_product p','p.product_num = d.product_num','left')
            ->join('wms_vendor v','p.vendor_num = v.vendor_num','left')
            ->select();
        $this->jkReturn('0000','出库单商品列表',$productList);
    }

    /**
     * @title 出库单编辑
     * @description 接口说明
     * @author gyl
     * @url /Inventory/orderOutEdit
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:string require:1 default: other: desc:id
     * @param name:postage type:string require:1 default: other: desc:邮费
     * @param name:remark type:string require:0 default: other: desc:备注
     * @param name:product_info type:array require:0 default: other: desc:商品信息
     *
     * @param name:order_code type:string require:1 default: other: desc:出库单编号
     * @param name:product_num type:string require:1 default: other: desc:商品编号
     * @param name:product_name type:string require:1 default: other: desc:商品名称
     * @param name:pro_out_amount type:string require:1 default: other: desc:出库数量
     * @param name:product_date type:string require:1 default: other: desc:生产日期
     * @param name:supplier type:string require:1 default: other: desc:供应商编号
     * @param name:supplier_name type:string require:1 default: other: desc:供应商名称
     * @param name:price type:string require:1 default: other: desc:进价
     *
     */
    public function orderOutEdit(Request $request, OrderOutModel $orderOutModel, OrderOutDetailModel $outDetailModel){
        $param = $request->param();
        //-- 开启事物
        $outDetailModel->startTrans();
        $where = array('id'=>$param['id']);
        $result = $orderOutModel->allowField(true)->save($param,$where);
        if($result){
            $this->setAdminUserLog("更新","出库单更新","wms_order_out_copy",$param['id']);
            //-- 删除出库单商品
            $result = $outDetailModel->where(['order_code'=>$param['product_info'][0]['order_code']])->delete();
            if($result<0){
                $outDetailModel->rollback();
                $this->jkReturn('-1004','删除出库单商品失败');
            }
            if(!$outDetailModel->allowField(true)->saveAll($param['product_info'])){
                $outDetailModel->rollback();
                $this->jkReturn('-1004','出库单商品保存失败');
            }
            $outDetailModel->commit();
            $this->jkReturn('0000','更新成功');
        }else{
            $this->jkReturn('-1004','更新失败');
        }
    }

    /**
     * @title 出库单删除
     * @description 接口说明
     * @author gyl
     * @url /Inventory/orderOutDel
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:order_code type:string require:1 default: other: desc:出库单编号
     *
     */
    public function orderOutDel(Request $request, OrderOutModel $orderOutModel, OrderOutDetailModel $outDetailModel){
        $param = $request->param();
        $outDetailModel->startTrans();
        //-- 删除绑定商品
        if(!$outDetailModel->where(['order_code'=>$param['order_code']])->delete()){
            $outDetailModel->rollback();
            $this->jkReturn('-1004',"出库单商品删除失败,出库单编号为'$param[order_code]'");
        }
        $this->setAdminUserLog("删除","删除出库单商品,出库单编号为'$param[order_code]'","wms_order_out_copy_detail");
        if(!$orderOutModel->where(['order_code'=>$param['order_code']])->delete()){
            $this->jkReturn('-1004',"删除失败,出库单编号为'$param[order_code]'");
        }
        $this->setAdminUserLog("删除","删除出库单,出库单编号为'$param[order_code]'","wms_order_out_copy");
        $outDetailModel->commit();
        $this->jkReturn('0000','删除成功');
    }

    /**
     * @title 审核日志列表
     * @description 接口说明
     * @author gyl
     * @url /Inventory/reviewedLog
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:订单编号
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1other: desc:页数
     *
     * @return reviewedLog:审核日志列表@
     * @reviewedLog order_code:订单编号 order_type:审核日志 sh_info:审核信息 create_by:创建人 create_date：创建时间
     * @return reviewedLogCount:审核日志数量
     */
    public function reviewedLog(Request $request, ReviewedLogModel $reviewedLogModel){
        $param = $request->param();
        $where = "1 = 1";
        if (!empty($param['search_key'])) {
            $where .= " and order_num like '%" . $param['search_key'] . "%' ";
        }
        $reviewedLog = $reviewedLogModel
            ->where($where)
            ->limit($param["limit"]??10)
            ->page($param['page']??1)
            ->select();
        $reviewedLogCount = $reviewedLogModel
            ->where($where)
            ->count();
        $data = array(
            'reviewedLog'=>$reviewedLog,
            'reviewedLogCount'=>$reviewedLogCount
        );
        $this->jkReturn('0000','出库单列表',$data);
    }
}