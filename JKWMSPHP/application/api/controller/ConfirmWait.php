<?php
/**
 * Created by PhpStorm.
 * User: guanyl
 * Date: 2018/11/30
 * Time: 15:29
 */

namespace app\Api\controller;
use app\api\model\PurOrderModel;
use app\api\model\PurProductModel;
use app\api\model\PurStOrderInfoModel;
use app\api\model\PurStOrderModel;
use app\api\model\QualityCheckWaitInfoModel;
use app\api\model\QualityCheckWaitModel;
use think\Request;


/**
 * @title 待收货入库管理
 * @description 接口说明
 * @group 单据管理
 * @header name:key require:1 default: desc:秘钥(区别设置)

 */
class ConfirmWait extends Common
{
    /**
     * @title 待收货入库申请单列表
     * @description 接口说明
     * @author gyl
     * @url /ConfirmWait/wareApplyList
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:采购单编号、申请单编号
     * @param name:wh_id type:string require:0 default: other: desc:仓库id
     * @param name:start_date type:int require:0 default: 1 other: desc:搜索开始日期
     * @param name:end_date type:int require:0 default: 1 other: desc:搜索结束日期
     * @param name:delivery_status type:int require:0 default: 1 other: desc:收货状态(0:待确认,1:已确认)
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1other: desc:页数
     *
     * @return wareApplyList:待收货入库申请单列表@
     * @wareApplyList pur_order_num:采购单编号 pur_storder_num:申请单编号 warehouse_name:收货仓库 warea_name:收货库区 purst_name:采购人姓名 supplier:供应商编码 vendor_name:供应商 remark:备注 create_by:创建人 create_date：创建时间 delivery_status:收货状态
     * @return wareApplyCount:待收货入库申请单数量
     */
    public function wareApplyList(Request $request, PurStOrderModel $stOrderModel){
        $param = $request->param();
        $where = "o.disabled = 1";
        $field = "o.id,o.pur_order_num,o.pur_storder_num,o.wh_id,w.warehouse_name,o.waerea_id,a.warea_name,o.purst_name,o.vendor,v.vendor_name,o.remark,o.create_by,o.create_date,o.delivery_status";
        if (!empty($param['search_key'])) {
            $where .= " and (o.pur_order_num like '%" . $param['search_key'] . "%' or o.pur_storder_num like '%" . $param['search_key'] . "%') ";
        }
        if (!empty($param['start_date']) && !empty($param['end_date'])) {
            $where .= " and date_format(o.create_date,'%Y-%m-%d') >= date_format('" . $param['start_date'] . "','%Y-%m-%d')";
            $where .= " and date_format(o.create_date,'%Y-%m-%d') <= date_format('" . $param['end_date'] . "','%Y-%m-%d')";
        }
        if (!empty($param['delivery_status'])) {
            $where .= " and o.delivery_status = $param[delivery_status]";
        }
        if (!empty($param['wh_id'])) {
            $where .= " and o.wh_id = $param[wh_id]";
        }
        $wareApplyList = $stOrderModel
            ->field($field)
            ->alias('o')
            ->where($where)
            ->join('wms_warehouse w','w.id = o.wh_id','left')
            ->join('wms_warehouse_area a','a.id = o.waerea_id','left')
            ->join('wms_vendor v','v.vendor_num = o.vendor','left')
            ->limit($param["limit"]??10)
            ->page($param['page']??1)
            ->select();
        $wareApplyCount = $stOrderModel
            ->alias('o')
            ->where($where)
            ->join('wms_warehouse w','w.id = o.wh_id','left')
            ->join('wms_warehouse_area a','a.id = o.waerea_id','left')
            ->join('wms_vendor v','v.vendor_num = o.vendor','left')
            ->count();
        $data = array(
            'wareApplyList'=>$wareApplyList,
            'wareApplyCount'=>$wareApplyCount
        );
        $this->jkReturn('0000','待收货入库申请单列表',$data);
    }

    /**
     * @title 待收货入库申请单详情
     * @description 接口说明
     * @author gyl
     * @url /ConfirmWait/wareApplyDetail
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     * @param name:id type:int require:1 default: other: desc:申请单id
     *
     * @return pur_storder_num:申请单编号
     * @return pur_order_num:采购订单编号
     * @return warehouse_name:收货仓库
     * @return warea_name:收货库区
     * @return wh_id:仓库id
     * @return waerea_id:库区id
     * @return vendor:供应商编码
     * @return vendor_name:供应商
     * @return product_num:采购总数量
     * @return real_num:实际收货数量
     * @return create_by:创建人
     * @return create_date:创建时间
     * @return remark:备注
     * @return delivery_status:收货状态
     * @return product_info:采购商品信息@
     * @product_info product_num:商品编号 product_name:商品名称 bar_code:商品条形码 vendor_name:供应商名称 price:进价 unit:单位 pro_create_date:生产日期 shelf_month:保质期 specifications:规格 carton:箱规 input_tax:税率 batch_code:批次号 product_amount:采购数量 real_arrival_num:订单到货数量 gift_num:赠品数量 wait_storage_num:实际到货数量
     *
     */
    public function wareApplyDetail(Request $request, PurStOrderModel $stOrderModel, PurStOrderInfoModel $stOrderInfoModel){
        $param = $request->param();
        $field = "o.id,o.pur_order_num,o.pur_storder_num,v.vendor_name,o.wh_id,w.warehouse_name,o.create_by,o.create_date,o.delivery_status,o.remark";
        $stOrder = $stOrderModel
            ->field($field)
            ->alias('o')
            ->where('o.id',$param['id'])
            ->join('wms_pur_order p','p.pur_order_num = o.pur_order_num','left')
            ->join('wms_warehouse w','w.id = o.wh_id','left')
            ->join('wms_warehouse_area a','a.id = o.waerea_id','left')
            ->join('wms_vendor v','v.vendor_num = p.supplier','left')
            ->find();
        $allPrice = 0;//总价格
        $product_num = 0;//采购总数量
        $real_num = 0;//实际收货数量
        if (count($stOrder) >= 1) {
            $stOrder['product_info'] = $stOrderInfoModel
                ->field('pp.*,p.bar_code')
                ->alias('pp')
                ->where('pur_storder_num',$stOrder['pur_storder_num'])
                ->join('wms_product_info p','p.product_num = pp.product_num','left')
                ->select();
            if (count($stOrder['product_info']) >= 1) {
                foreach ($stOrder['product_info'] as $product_info) {
                    $allAmount = $product_info['price'] * $product_info['product_amount'];
                    $allPrice += $allAmount;
                    $product_num += $product_info['product_amount'];
                    $real_num += $product_info['wait_storage_num'];
                }
            }
        }
        $stOrder['allPrice'] = $allPrice;
        $stOrder['product_num'] = $product_num;
        $stOrder['real_num'] = $real_num;
        $this->jkReturn('0000','待收货入库申请单详情',$stOrder);
    }

    /**
     * @title 待入库申请单新建
     * @description 接口说明
     * @author gyl
     * @url /ConfirmWait/wareApplyAdd
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:pur_order_num type:string require:0 default: 1 1other: desc:采购单编号
     * @param name:warehouse_name type:string require:1 default: other: desc:收货仓库
     * @param name:warea_name type:string require:1 default: other: desc:收货库区
     * @param name:wh_id type:string require:1 default: other: desc:仓库id
     * @param name:waerea_id type:string require:1 default: other: desc:库区id
     * @param name:create_by type:string require:1 default: other: desc:创建人
     * @param name:create_date type:string require:1 default: other: desc:创建时间
     * @param name:remark type:string require:1 default: other: desc:备注
     * @param name:pur_product type:array require:1 default: other: desc:采购商品信息
     * @param name:id type:string require:1 default: other: desc:id
     * @param name:gift_num type:string require:1 default: other: desc:赠品数量
     * @param name:real_arrival_num type:string require:1 default: other: desc:订单到货数量
     * @param name:wait_storage_num type:string require:1 default: other: desc:待检入库数量
     *
     */
    public function wareApplyAdd(Request $request,PurOrderModel $purOrderModel, PurProductModel $productModel, PurStOrderModel $stOrderModel, PurStOrderInfoModel $stOrderInfoModel){
        $param = $request->param();
        /*$param = array(
            'pur_order_num'=>'OI-20170703-0002',
            'wh_id'=>1,
            'create_by'=>'admin',
            'remark'=>'创建待入库申请单',
            'pur_product'=>array(
                array(
                    'id'=>132,
                    'real_arrival_num'=>'100',
                    'gift_num'=>'20',
                    'wait_storage_num'=>'120'
                ),
                array(
                    'id'=>133,
                    'real_arrival_num'=>'100',
                    'gift_num'=>'20',
                    'wait_storage_num'=>'120'
                ),
                array(
                    'id'=>134,
                    'real_arrival_num'=>'100',
                    'gift_num'=>'20',
                    'wait_storage_num'=>'120'
                ),
            )
        );*/
        if (empty($param['pur_order_num'])) {
            $this->jkReturn('-1004','请填写采购单编号');
        }
        $stOrderInfo = $stOrderModel->where(['pur_order_num'=>$param['pur_order_num']])->find();
        if (!empty($stOrderInfo)) {
            $this->jkReturn('-1004','采购单已存在');
        }
        //处理采购商品信息
        $pur_product = array();
        if (!empty($param['pur_product'])) {
            foreach ($param['pur_product'] as $product) {
                $pur_product[$product['id']]['real_arrival_num'] = $product['real_arrival_num'];
                $pur_product[$product['id']]['gift_num'] = $product['gift_num'];
                $pur_product[$product['id']]['wait_storage_num'] = $product['wait_storage_num'];
            }
        }
        //查找采购订单的供应商
        $purOrder = $purOrderModel->where(['pur_order_num'=>$param['pur_order_num']])->find();
        $param['vendor'] = $purOrder['supplier'];
        //生成入库申请编号
        $date = date('Y-m-d');
        $where = "date_format(create_date,'%Y-%m-%d') = date_format('" . $date . "','%Y-%m-%d')";
        $stOrder = $stOrderModel->where($where)->count();
        $number = $this->number($stOrder);
        $param['pur_storder_num'] = 'SH-' . date('Ymd') . '-' . $number;//申请单编号
        //录入申请单信息
        $result = $stOrderModel->allowField(true)->save($param);
        if($result){
            $stOrderId = $stOrderModel->getLastInsID();
            //操作日志
            $this->setAdminUserLog("新增","待入库申请单新建","wms_pur_storder",$stOrderId);
            //查询采购订单商品信息
            $product = $productModel->where(['pur_order_num'=>$param['pur_order_num']])->select()->toArray();
            if (count($product) > 0) {
                foreach ($product as &$v) {
                    $v['wh_id'] = $param['wh_id'];
                    if(!empty($param['waerea_id'])) {
                        $v['waerea_id'] = $param['waerea_id'];
                    }
                    $v['pur_storder_num'] = $param['pur_storder_num'];
                    $v['vendor'] = $param['vendor'];
                    if (!empty($pur_product)) {
                        if (empty($pur_product[$v['id']])) {
                            continue;
                        }
                        $v['real_arrival_num'] = $pur_product[$v['id']]['real_arrival_num'];
                        $v['gift_num'] = $pur_product[$v['id']]['gift_num'];
                        $v['wait_storage_num'] = $pur_product[$v['id']]['wait_storage_num'];
                    }
                }
                foreach ($product as &$k) {
                    unset($k['id']);
                }
                $stOrderInfoModel->allowField(true)->saveAll($product);
            }
            //采购订单状态改变
            $purOrderModel->allowField(true)->save(['pur_order_status'=>1],['pur_order_num'=>$param['pur_order_num']]);
            $this->setReviewedLog("采购单","$param[pur_order_num]",'待收货验收入库');
            $this->jkReturn('0000','添加成功');
        }else{
            $this->jkReturn('-1004','添加失败');
        }
    }

    /**
     * @title 待入库申请单更新
     * @description 接口说明
     * @author gyl
     * @url /ConfirmWait/wareApplyEdit
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:string require:0 default: 1 1other: desc:待入库申请单id
     * @param name:pur_storder_num type:string require:0 default: 1 1other: desc:待入库申请单编号
     * @param name:warehouse_name type:string require:1 default: other: desc:收货仓库
     * @param name:warea_name type:string require:1 default: other: desc:收货库区
     * @param name:wh_id type:string require:1 default: other: desc:仓库id
     * @param name:waerea_id type:string require:1 default: other: desc:库区id
     * @param name:remark type:string require:1 default: other: desc:备注
     * @param name:pur_product type:array require:1 default: other: desc:采购商品信息
     * @param name:id type:int require:1 default: other: desc:待入库申请单商品id
     * @param name:real_arrival_num type:string require:1 default: other: desc:订单到货数量
     * @param name:gift_num type:string require:1 default: other: desc:赠品数量
     * @param name:wait_storage_num type:string require:1 default: other: desc:实际到货数量
     *
     */
    public function wareApplyEdit(Request $request, PurStOrderModel $stOrderModel, PurStOrderInfoModel $stOrderInfoModel){
        $param = $request->param();
        /*$param = array(
            'id'=>'132',
            'pur_order_num'=>'OI-20170703-0002',
            'wh_id'=>1,
            'create_by'=>'admin',
            'remark'=>'更新待入库申请单',
            'pur_product'=>array(
                array(
                    'id'=>5,
                    'real_arrival_num'=>'50',
                    'gift_num'=>'20',
                    'wait_storage_num'=>'52'
                ),
                array(
                    'id'=>6,
                    'real_arrival_num'=>'80',
                    'gift_num'=>'20',
                    'wait_storage_num'=>'82'
                ),
                array(
                    'id'=>7,
                    'real_arrival_num'=>'63',
                    'gift_num'=>'20',
                    'wait_storage_num'=>'83'
                ),
            )
        );*/
        if (empty($param['id'])) {
            $this->jkReturn('-1004','请填写待入库申请单id');
        }
        $where['id'] = $param['id'];
        $result = $stOrderModel->allowField(true)->save($param, $where);
        if($result){
            $this->setAdminUserLog("更新","待入库申请单更新","wms_pur_storder",$param['id']);
            if (!empty($param['pur_product'])) {
                foreach ($param['pur_product'] as &$purProduct) {
                    $purProduct['wh_id'] = $param['wh_id'];
                    if(!empty($param['waerea_id'])) {
                        $purProduct['waerea_id'] = $param['waerea_id'];
                    }
                }
                $stOrderInfoModel->allowField(true)->saveAll($param['pur_product']);
            }

            $this->jkReturn('0000','更新成功');
        }else{
            $this->jkReturn('-1004','更新失败');
        }
    }

    /**
     * @title 待入库申请单删除
     * @description 接口说明
     * @author gyl
     * @url /ConfirmWait/wareApplyDel
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:int require:0 default: other: desc:待入库申请单id
     *
     */
    public function wareApplyDel(Request $request, PurStOrderModel $stOrderModel, PurStOrderInfoModel $stOrderInfoModel){
        $param = $request->param();
        $stOrderInfoModel->startTrans();
        if (empty($param['id'])) {
            $this->jkReturn('-1004','请填写待入库申请单id');
        }
        $stOrder = $stOrderModel->where(['id'=>$param['id']])->find();

        //-- 删除绑定商品
        if(!$stOrderInfoModel->where(['pur_storder_num'=>$stOrder['pur_storder_num']])->delete()){
            $stOrderInfoModel->rollback();
            $this->jkReturn('-1004',"入库申请单商品删除失败,编号为'$stOrder[pur_storder_num]'");
        }
        $this->setAdminUserLog("删除","删除入库申请单商品,编号为'$stOrder[pur_storder_num]'","wms_pur_storder_info");

        //--待入库申请单删除
        if(!$stOrderModel->where(['id'=>$param['id']])->delete()){
            $this->jkReturn('-1004',"删除失败,入库申请单编号为'$stOrder[pur_storder_num]'");
        }

        $this->setAdminUserLog("删除","删除入库申请单,编号为'$stOrder[pur_storder_num]'","wms_pur_storder",$param['id']);
        $stOrderInfoModel->commit();
        $this->jkReturn('0000','删除成功');
    }

    /**
     * @title 待入库申请单确认
     * @description 接口说明
     * @author gyl
     * @url /ConfirmWait/reviewed
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:pur_storder_num type:string require:1 default: other: desc:申请单编号
     *
     */
    public function reviewed(Request $request, PurStOrderModel $stOrderModel, PurOrderModel $purOrderModel, QualityCheckWaitModel $checkWaitModel, PurStOrderInfoModel $stOrderInfoModel, QualityCheckWaitInfoModel $waitInfoModel){
        $param = $request->param();
        //查询待入库订单数据
        $stOrder = $stOrderModel->where(['pur_storder_num'=>$param['pur_storder_num']])->find();
        //待入库申请单确认
        $result = $stOrderModel->allowField(true)->save(['delivery_status'=>1],['id'=>$stOrder['id']]);
        if($result){
            //添加操作日志
            $this->setAdminUserLog("审核","待入库申请单确认,编号是$param[pur_storder_num]","wms_pur_storder",$stOrder['id']);
            //添加审核日志
            $this->setReviewedLog("待入库申请单","$param[pur_storder_num]",'已确认');
            //采购订单状态改变
            $purOrderModel->allowField(true)->save(['pur_order_status'=>1],['pur_order_num'=>$stOrder['pur_order_num']]);
            $this->setReviewedLog("采购单","$stOrder[pur_order_num]",'待质检入库');
            $checkWait = $checkWaitModel->where(['pur_order_num'=>$stOrder['pur_order_num']])->find();
            if (count($checkWait) <= 0) {
                //生成一张质检单
                $data = array(
                    'pur_order_num'=>$stOrder['pur_order_num'],
                    'vendor'=>$stOrder['vendor'],
                    'wh_id'=>$stOrder['wh_id'],
                    'waerea_id'=>$stOrder['waerea_id'],
                    'create_by'=>$stOrder['create_by']
                );
                //生成质检单编号
                $date = date('Y-m-d');
                $where = "date_format(create_date,'%Y-%m-%d') = date_format('" . $date . "','%Y-%m-%d')";
                $checkWait = $checkWaitModel->where($where)->count();
                $number = $this->number($checkWait);
                $data['check_wait_num'] = 'DZJ-' . date('Ymd') . '-' . $number;//待质检申请单编号
                //新建质检单
                $checkWaitModel->create($data);
                $checkWaitId = $checkWaitModel->getLastInsID();
                $this->setAdminUserLog("新建","新建质检申请单","wms_quality_check_wait",$checkWaitId);
                //添加质检申请单详细信息
                $stOrderInfo = $stOrderInfoModel->where(['pur_storder_num'=>$param['pur_storder_num']])->select()->toArray();
                if (count($stOrderInfo) > 0) {
                    foreach ($stOrderInfo as &$orderInfo) {
                        $orderInfo['check_wait_num'] = $data['check_wait_num'];
                        unset($orderInfo['id']);
                    }
                    $waitInfoModel->allowField(true)->saveAll($stOrderInfo);
                }
            }
            $this->jkReturn('0000','审核成功');
        }else{
            $this->jkReturn('-1004','审核失败');
        }
    }

    /**
     * @title 待入库申请订单导出
     * @description 接口说明
     * @author gyl
     * @url /ConfirmWait/stOrderExport
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:pur_storder_num type:string require:0 default: other: desc:待入库申请单编号
     */
    public function stOrderExport(Request $request, PurStOrderInfoModel $stOrderInfoModel) {
        ob_end_clean();
        $param = $request->param();
//        $param['pur_storder_num'] = 'SH-20181203-0002';
        $field = "o.pur_order_num,o.pur_storder_num,w.warehouse_name,v.vendor_name,o.delivery_status,p.product_num,i.bar_code,p.product_name,p.specifications,p.product_amount,p.wait_storage_num,p.pro_create_date,p.shelf_month";
        $res = $stOrderInfoModel
            ->field($field)
            ->alias('p')
            ->where('p.pur_storder_num',$param['pur_storder_num'])
            ->join('wms_pur_storder o','p.pur_order_num = o.pur_order_num','left')
            ->join('wms_warehouse w','o.wh_id = w.id','left')
            ->join('wms_vendor v','v.vendor_num = o.vendor','left')
            ->join('wms_product_info i','p.product_num = i.product_num','left')
            ->select();
        $count = $stOrderInfoModel
            ->alias('p')
            ->where('p.pur_storder_num',$param['pur_storder_num'])
            ->join('wms_pur_storder o','p.pur_order_num = o.pur_order_num','left')
            ->join('wms_warehouse w','o.wh_id = w.id','left')
            ->join('wms_vendor v','v.vendor_num = o.vendor','left')
            ->join('wms_product_info i','p.product_num = i.product_num','left')
            ->count();
        $productCount = '至本页合计单品数：' . $count;
        // 实例化excel类
        $objPHPExcel = new \PHPExcel();
        // 操作第一个工作表
        $objPHPExcel->setActiveSheetIndex(0);
        // 设置sheet名
        $objPHPExcel->getActiveSheet()->setTitle('purStOrderProduct');
        // 设置表格宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(10);
        // 列名表头文字加粗
        $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->getFont()->setBold(true);
        // 列表头文字居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // 列名赋值
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '采购单编号');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '申请单编号');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '收货仓库');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '供应商');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '收货状态');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '商品编号');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '商品条码');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '商品名称');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '规格');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '采购数量');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '实际到货数量');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', '生产日期');
        $objPHPExcel->getActiveSheet()->setCellValue('M1', '保质期');

        // 数据起始行
        $row_num = 2;
        // 向每行单元格插入数据
        foreach ($res as $value) {
            // 设置所有垂直居中
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row_num . ':' . 'M' . $row_num)->getAlignment()
                ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            // 设置单元格数值
            $delivery_status = '待收货确认';
            if ($value['delivery_status'] == 1) {
                $delivery_status = '确认收货';
            }
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('A' . $row_num, $value['pur_order_num'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row_num, $value['pur_storder_num'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row_num, $value['warehouse_name'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row_num, $value['vendor_name'],\PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('E' . $row_num, $delivery_status, \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row_num, $value['product_num'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row_num, $value['bar_code'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row_num, $value['product_name'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row_num, $value['specifications'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row_num, $value['product_amount'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row_num, $value['wait_storage_num'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row_num, $value['pro_create_date'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $row_num, $value['shelf_month'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $row_num++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row_num, $productCount, \PHPExcel_Cell_DataType::TYPE_STRING);

        $outputFileName = '入库申请商品_' . date('Y-m-d H:i:s') . '.xls';
        $xlsWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        //输出内容 到浏览器
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $outputFileName . '"');
        header("Content-Transfer-Encoding: binary");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");

        $xlsWriter->save("php://output");
    }
}