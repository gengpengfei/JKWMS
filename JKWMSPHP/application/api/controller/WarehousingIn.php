<?php
/**
 * Created by PhpStorm.
 * User: guanyl
 * Date: 2018/12/11
 * Time: 10:53
 */

namespace app\Api\controller;
use app\api\model\MoveLibraryInfoModel;
use app\api\model\WarehouseModel;
use app\api\model\WarehousingInfoModel;
use app\api\model\WarehousingModel;
use think\Request;

/**
 * @title 入库管理
 * @description 接口说明
 * @group 单据管理
 * @header name:key require:1 default: desc:秘钥(区别设置)

 */

class WarehousingIn extends Common
{
    /**
     * @title 入库单列表
     * @description 接口说明
     * @author gyl
     * @url /WarehousingIn/warehousingList
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:入库单编号
     * @param name:wh_id type:string require:0 default: other: desc:仓库id
     * @param name:start_date type:int require:0 default: 1 other: desc:搜索开始日期
     * @param name:end_date type:int require:0 default: 1 other: desc:搜索结束日期
     * @param name:order_type type:int require:0 default: 1 other: desc:入库类型(0:待收货,1:采购入库,2:调拨入库,3:其他入库,4:盘盈入库,5:大客户退回入库,6:换货入库)
     * @param name:in_flag type:int require:0 default: 1 other: desc:是否入库（0:否，1:是）
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1other: desc:页数
     *
     * @return libraryList:入库单列表@
     * @checkList warehousing_num:入库单编号 order_type:入库单类型 wh_id:入库仓库 reviewed_status:审核状态 warehousing_name:入库人 warehousing_date:入库单日期 remark:备注 create_by:创建人 create_date：创建时间 in_flag:是否入库（0:否，1:是）
     * @return libraryList:入库单数量
     */
    public function warehousingList(Request $request, WarehousingModel $warehousingModel){
        $param = $request->param();
        $where = "m.disabled = 1";
        $field = "m.id,m.warehousing_num,m.order_type,m.wh_id,w.warehouse_name,m.reviewed_status,m.warehousing_name,m.warehousing_date,m.remark,m.create_by,m.create_date,m.in_flag";
        if (!empty($param['search_key'])) {
            $where .= " and m.warehousing_num like '%" . $param['search_key'] . "%'";
        }
        if (!empty($param['start_date']) && !empty($param['end_date'])) {
            $where .= " and date_format(m.create_date,'%Y-%m-%d') >= date_format('" . $param['start_date'] . "','%Y-%m-%d')";
            $where .= " and date_format(m.create_date,'%Y-%m-%d') <= date_format('" . $param['end_date'] . "','%Y-%m-%d')";
        }
        if (!empty($param['order_type'])) {
            $where .= " and m.order_type = $param[order_type]";
        }
        $warehousingList = $warehousingModel
            ->field($field)
            ->alias('m')
            ->where($where)
            ->join('wms_warehouse w','w.id = m.wh_id','left')
            ->limit($param["limit"]??10)
            ->page($param['page']??1)
            ->select();
        $warehousingCount = $warehousingModel
            ->alias('m')
            ->where($where)
            ->join('wms_warehouse w','w.id = m.wh_id','left')
            ->count();
        $data = array(
            'warehousingList'=>$warehousingList,
            'warehousingCount'=>$warehousingCount
        );
        $this->jkReturn('0000','入库单列表',$data);
    }

    /**
     * @title 入库单详情
     * @description 接口说明
     * @author gyl
     * @url /WarehousingIn/warehousingDetail
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     * @param name:id type:int require:1 default: other: desc:入库单id
     *
     * @return warehousing_num:入库单编号
     * @return order_type:入库单类型
     * @return wh_id:入库id
     * @return warehouse_name:入库仓库
     * @return warehousing_name:入库人
     * @return warehousing_date:入库单日期
     * @return create_by:创建人
     * @return create_date:创建时间
     * @return remark:备注
     * @return in_flag:入库状态(0:待入库，1:已入库)
     * @return reviewed_status:审核状态
     * @return product_info:入库商品信息@
     * @product_info product_num:商品编号 product_name:商品名称 bar_code:商品条形码 vendor_name:供应商名称 price:进价 unit:单位 pro_create_date:生产日期 shelf_month:保质期 specifications:规格 carton:箱规 input_tax:税率 batch_code:批次号 wait_storage_num:实际入库数量 waerea_id:移入库区 shelf_id:移入货架 library_id:移入库位
     *
     */
    public function warehousingDetail(Request $request, WarehousingModel $warehousingModel, WarehousingInfoModel $infoModel){
        $param = $request->param();
        $field = "m.id,m.warehousing_num,m.order_type,m.wh_id,w.warehouse_name,m.warehousing_name,m.warehousing_date,m.remark,m.create_by,m.create_date,m.in_flag,m.reviewed_status";
        $moveLibrary = $warehousingModel
            ->field($field)
            ->alias('m')
            ->where('m.id',$param['id'])
            ->join('wms_warehouse w','w.id = m.wh_id','left')
            ->find();
        $moveLibrary['product_info'] = $infoModel
            ->field('pp.*,p.bar_code,pp.wh_id,w.warehouse_name,pp.waerea_id,a.warea_name,r.shelf_num,pp.shelf_id,pp.library_id,l.wlibrary_num')
            ->alias('pp')
            ->where('warehousing_num',$moveLibrary['warehousing_num'])
            ->join('wms_product_info p','p.product_num = pp.product_num','left')
            ->join('wms_warehouse w','w.id = pp.wh_id','left')
            ->join('wms_warehouse_area a','a.id = pp.waerea_id','left')
            ->join('wms_row_shelf r','r.id = pp.shelf_id','left')
            ->join('wms_wlibrary l','l.id = pp.library_id','left')
            ->select();
        $this->jkReturn('0000','入库单详情',$moveLibrary);
    }

    /**
     * @title 商品列表
     * @description 接口说明
     * @author gyl
     * @url /WarehousingIn/productList
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:varchar require:0 default: other: desc:查询值
     * @param name:wh_id type:varchar require:1 default: other: desc:仓库id
     * @param name:waerea_id type:varchar require:1 default: other: desc:库区id
     *
     * @return product_num:商品编号
     * @return product_name:商品名称
     * @return bar_code:商品条码
     * @return price:进价
     * @return specifications:规格
     * @return unit:计量单位
     * @return shelf_month:保质期
     * @return pro_create_date:生产日期
     * @return carton:箱规
     * @return input_tax:税率
     * @return wait_storage_num:待入库数量
     */
    public function productList(Request $request,MoveLibraryInfoModel $libraryInfoModel){
        $param = $request->param();
        if (empty($param['wh_id']) || empty($param['waerea_id'])) {
            $this->jkReturn('-1004','请先选择仓库和库区！');
        }
        $where = "p.disabled = 1 and p.wh_id = '$param[wh_id]' and p.waerea_id = '$param[waerea_id]'";
        $field = "p.id,p.product_num,p.product_name,i.bar_code,p.price,p.specifications,p.unit,p.shelf_month,p.pro_create_date,p.carton,p.input_tax,p.wait_storage_num";
        if (!empty($param['search_key'])) {
            $where .= " and (p.product_num like '%" . $param['search_key'] . "%' or p.product_name like '%" . $param['search_key'] . "%') ";
        }
        $productList = $libraryInfoModel
            ->field($field)
            ->alias('p')
            ->where($where)
            ->join('wms_product_info i','p.product_num = i.product_num','left')
            ->select();
        $this->jkReturn('0000','商品列表',$productList);
    }

    /**
     * @title 入库单新建
     * @description 接口说明
     * @author gyl
     * @url /WarehousingIn/warehousingAdd
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:create_by type:string require:1 default: other: desc:创建人
     * @param name:create_date type:string require:1 default: other: desc:创建时间
     * @param name:remark type:string require:1 default: other: desc:备注
     * @param name:pur_product type:array require:1 default: other: desc:待入库商品信息
     * @param name:id type:int require:1 default: other: desc:移库商品id
     * @param name:real_arrival_num type:string require:1 default: other: desc:实际入库数量
     * @param name:wh_id type:string require:1 default: other: desc:入库仓库
     * @param name:waerea_id type:string require:1 default: other: desc:入库库区
     * @param name:shelf_id type:string require:1 default: other: desc:入库货架
     * @param name:library_id type:string require:1 default: other: desc:入库库位
     *
     */
    public function warehousingAdd(Request $request,WarehousingModel $warehousingModel, WarehousingInfoModel $infoModel, MoveLibraryInfoModel $libraryInfoModel){
        $param = $request->param();
        $param = array(
            'create_by'=>'admin',
            'remark'=>'入库单新建',
            'pur_product'=>array(
                array(
                    'id'=>1,
                    'real_arrival_num'=>'120',
                    'wh_id'=>'1',
                    'waerea_id'=>'1',
                    'shelf_id'=>'4',
                    'library_id'=>'66'
                ),
                array(
                    'id'=>2,
                    'real_arrival_num'=>'120',
                    'wh_id'=>'1',
                    'waerea_id'=>'1',
                    'shelf_id'=>'3',
                    'library_id'=>'50'
                ),
                array(
                    'id'=>3,
                    'real_arrival_num'=>'120',
                    'wh_id'=>'1',
                    'waerea_id'=>'1',
                    'shelf_id'=>'2',
                    'library_id'=>'26'
                ),
                array(
                    'id'=>4,
                    'real_arrival_num'=>'70',
                    'wh_id'=>'1',
                    'waerea_id'=>'1',
                    'shelf_id'=>'1',
                    'library_id'=>'5'
                ),
            )
        );
        //生成入库单编号
        $date = date('Y-m-d');
        $where = "date_format(create_date,'%Y-%m-%d') = date_format('" . $date . "','%Y-%m-%d')";
        $warehousing = $warehousingModel->where($where)->count();
        $number = $this->number($warehousing);
        $param['warehousing_num'] = 'IN-OI-' . date('Ymd') . '-' . $number;//入库单编号
        $param['warehousing_name'] = $param['create_by'];
        $param['warehousing_date'] = date('Y-m-d H:i:s');
        //保存入库单数据
        $result = $warehousingModel->allowField(true)->save($param);
        if($result){
            //获取入库单ID
            $warehousingId = $warehousingModel->getLastInsID();
            //保存操作日志
            $this->setAdminUserLog("新增","入库单新建","wms_warehousing_order",$warehousingId);
            //添加入库单详细信息
            $wh_id = '0';
            foreach ($param['pur_product'] as $product) {
                $libraryInfo = $libraryInfoModel->where(['id'=>$product['id']])->find();
                $wh_id = $product['wh_id'];
                $data = array(
                    'vendor'=>$libraryInfo['vendor'],
                    'product_num'=>$libraryInfo['product_num'],
                    'product_name'=>$libraryInfo['product_name'],
                    'unit'=>$libraryInfo['unit'],
                    'price'=>$libraryInfo['price'],
                    'sale_price'=>$libraryInfo['sale_price'],
                    'shelf_month'=>$libraryInfo['shelf_month'],
                    'pro_create_date'=>$libraryInfo['pro_create_date'],
                    'wait_storage_num'=>$libraryInfo['wait_storage_num'],
                    'specifications'=>$libraryInfo['specifications'],
                    'carton'=>$libraryInfo['carton'],
                    'batch_code'=>$libraryInfo['batch_code'],
                    'wh_id'=>$product['wh_id'],//仓库
                    'waerea_id' => $product['waerea_id'],//库区
                    'shelf_id' => $product['shelf_id'],//货架
                    'library_id' => $product['library_id'],//库位
                    'real_arrival_num' => $product['real_arrival_num'],//实际入库数量
                    'warehousing_num' => $param['warehousing_num']
                );
                $infoModel->create($data);
            }
            $warehousingModel->allowField(true)->save(['wh_id'=>$wh_id],['id'=>$warehousingId]);
            $this->jkReturn('0000','添加成功');
        }else{
            $this->jkReturn('-1004','添加失败');
        }
    }

    /**
     * @title 入库单更新
     * @description 接口说明
     * @author gyl
     * @url /WarehousingIn/warehousingEdit
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:string require:0 default: 1 1other: desc:入库单id
     * @param name:remark type:string require:1 default: other: desc:备注
     * @param name:pur_product type:array require:1 default: other: desc:待入库商品信息
     * @param name:id type:int require:1 default: other: desc:入库商品id
     * @param name:real_arrival_num type:string require:1 default: other: desc:实际入库数量
     * @param name:wh_id type:string require:1 default: other: desc:入库仓库
     * @param name:waerea_id type:string require:1 default: other: desc:入库库区
     * @param name:shelf_id type:string require:1 default: other: desc:入库货架
     * @param name:library_id type:string require:1 default: other: desc:入库库位
     *
     */
    public function warehousingEdit(Request $request, WarehousingModel $warehousingModel, WarehousingInfoModel $infoModel){
        $param = $request->param();
        /*$param = array(
                   'id'=>1,
                   'remark'=>'入库单更新',
                   'pur_product'=>array(
                       array(
                           'id'=>1,
                           'real_arrival_num'=>'120',
                           'waerea_id'=>'1',
                           'shelf_id'=>'4',
                           'library_id'=>'66'
                       ),
                       array(
                           'id'=>2,
                           'real_arrival_num'=>'120',
                           'waerea_id'=>'1',
                           'shelf_id'=>'3',
                           'library_id'=>'50'
                       ),
                       array(
                           'id'=>3,
                           'real_arrival_num'=>'120',
                           'waerea_id'=>'1',
                           'shelf_id'=>'2',
                           'library_id'=>'26'
                       ),
                       array(
                           'id'=>4,
                           'real_arrival_num'=>'70',
                           'waerea_id'=>'1',
                           'shelf_id'=>'1',
                           'library_id'=>'5'
                       ),
                   )
               );*/
        $where['id'] = $param['id'];
        $result = $warehousingModel->allowField(true)->save($param, $where);
        if($result){
            $this->setAdminUserLog("更新","入库单更新","wms_warehousing_order",$param['id']);
            $infoModel->allowField(true)->saveAll($param['pur_product']);
            $this->jkReturn('0000','更新成功');
        }else{
            $this->jkReturn('-1004','更新失败');
        }
    }

    /**
     * @title 入库单删除
     * @description 接口说明
     * @author gyl
     * @url /WarehousingIn/warehousingDel
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:string require:0 default: 1 1other: desc:入库单id
     *
     */
    public function warehousingDel(Request $request, WarehousingModel $warehousingModel, WarehousingInfoModel $infoModel){
        $param = $request->param();
        $infoModel->startTrans();
        if (empty($param['id'])) {
            $this->jkReturn('-1004','请填写入库单id');
        }
        $warehousing = $warehousingModel->where(['id'=>$param['id']])->find();
        if (count($warehousing) <= 0) {
            $this->jkReturn('-1004','数据为空');
        }
        if ($warehousing['in_flag'] == 1) {
            $this->jkReturn('-1004','此单据已入库，不允许删除');
        }

        //-- 删除绑定商品
        if(!$infoModel->where(['warehousing_num'=>$warehousing['warehousing_num']])->delete()){
            $infoModel->rollback();
            $this->jkReturn('-1004',"入库单商品删除失败,编号为'$warehousing[warehousing_num]'");
        }
        $this->setAdminUserLog("删除","删除入库单商品,编号为'$warehousing[warehousing_num]'","wms_warehousing_order_info");

        //--入库单删除
        if(!$warehousingModel->where(['id'=>$param['id']])->delete()){
            $this->jkReturn('-1004',"删除失败,入库单编号为'$warehousing[warehousing_num]'");
        }
        $this->setAdminUserLog("删除","删除入库单,编号为'$warehousing[warehousing_num]'","wms_warehousing_order",$param['id']);
        $infoModel->commit();
        $this->jkReturn('0000','删除成功');
    }

    /**
     * @title 入库单导出
     * @description 接口说明
     * @author gyl
     * @url /WarehousingIn/warehousingExport
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:warehousing_num type:string require:0 default: other: desc:入库单编号
     */
    public function warehousingExport(Request $request, WarehousingModel $warehousingModel, WarehousingInfoModel $infoModel) {
        ob_end_clean();
        $param = $request->param();
        $param['warehousing_num'] = 'IN-OI-20181211-0001';
        $field = "o.warehousing_num,o.order_type,w.warehouse_name,a.warea_name,v.shelf_num,wl.wlibrary_num,o.in_flag,o.reviewed_status,p.product_num,i.bar_code,p.product_name,p.specifications,p.real_arrival_num,p.wait_storage_num,p.pro_create_date,p.shelf_month";
        $res = $infoModel
            ->field($field)
            ->alias('p')
            ->where('p.warehousing_num',$param['warehousing_num'])
            ->join('wms_warehousing_order o','p.warehousing_num = o.warehousing_num','left')
            ->join('wms_warehouse w','p.wh_id = w.id','left')
            ->join('wms_warehouse_area a','a.id = p.waerea_id','left')
            ->join('wms_row_shelf v','v.id = p.shelf_id','left')
            ->join('wms_wlibrary wl','wl.id = p.library_id','left')
            ->join('wms_product_info i','p.product_num = i.product_num','left')
            ->select();
        $count = $infoModel
            ->alias('p')
            ->where('p.warehousing_num',$param['warehousing_num'])
            ->join('wms_warehousing_order o','p.warehousing_num = o.warehousing_num','left')
            ->join('wms_warehouse w','p.wh_id = w.id','left')
            ->join('wms_warehouse_area a','a.id = p.waerea_id','left')
            ->join('wms_row_shelf v','v.id = p.shelf_id','left')
            ->join('wms_wlibrary wl','wl.id = p.library_id','left')
            ->join('wms_product_info i','p.product_num = i.product_num','left')
            ->count();
        $productCount = '至本页合计单品数：' . $count;
        // 实例化excel类
        $objPHPExcel = new \PHPExcel();
        // 操作第一个工作表
        $objPHPExcel->setActiveSheetIndex(0);
        // 设置sheet名
        $objPHPExcel->getActiveSheet()->setTitle('warehousingProduct');
        // 设置表格宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(10);
        // 列名表头文字加粗
        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getFont()->setBold(true);
        // 列表头文字居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // 列名赋值
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '入库单编号');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '入库单类型');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '仓库');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '库区');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '货架');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '库位');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '是否入库');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '审核状态');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '商品编号');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '商品条码');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '商品名称');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', '规格');
        $objPHPExcel->getActiveSheet()->setCellValue('M1', '实际入库数量');
        $objPHPExcel->getActiveSheet()->setCellValue('N1', '待入库数量');
        $objPHPExcel->getActiveSheet()->setCellValue('O1', '生产日期');
        $objPHPExcel->getActiveSheet()->setCellValue('P1', '保质期');

        // 数据起始行
        $row_num = 2;
        // 向每行单元格插入数据
        foreach ($res as $value) {
            // 设置所有垂直居中
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row_num . ':' . 'P' . $row_num)->getAlignment()
                ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            // 设置单元格数值
            $order_type = $warehousingModel->getOrderTypeAttr($value['order_type']);
            $in_flag = $warehousingModel->getInFlagAttr($value['in_flag']);
            $reviewed_status = $warehousingModel->getReviewedStatusAttr($value['reviewed_status']);

            $objPHPExcel->getActiveSheet()->setCellValueExplicit('A' . $row_num, $value['warehousing_num'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row_num, $order_type, \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row_num, $value['warehouse_name'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row_num, $value['warea_name'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row_num, $value['shelf_num'],\PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row_num, $value['wlibrary_num'],\PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('G' . $row_num, $in_flag, \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row_num, $reviewed_status, \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row_num, $value['product_num'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row_num, $value['bar_code'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row_num, $value['product_name'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row_num, $value['specifications'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $row_num, $value['real_arrival_num'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $row_num, $value['wait_storage_num'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . $row_num, $value['pro_create_date'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . $row_num, $value['shelf_month'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $row_num++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row_num, $productCount, \PHPExcel_Cell_DataType::TYPE_STRING);

        $outputFileName = '入库单商品_' . date('Y-m-d H:i:s') . '.xls';
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

    /**
     * @title 提交审核
     * @description 接口说明
     * @author gyl
     * @url /WarehousingIn/reviewed
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:warehousing_num type:string require:0 default: other: desc:入库单编号
     * @param name:reviewed_status type:string require:0 default: other: desc:审核状态（0：新建，1：待审核，2：审核通过，3：审核不通过）
     *
     */
    public function reviewed(Request $request, WarehousingModel $warehousingModel){
        $param = $request->param();
        $param['warehousing_num'] = 'IN-OI-20181211-0003';
        $param['reviewed_status'] = '2';
        //查询入库单数据
        $warehousing = $warehousingModel->where(['warehousing_num'=>$param['warehousing_num']])->find()->toArray();
        if ($param['reviewed_status'] == '2') {
            $warehousingModel->allowField(true)->save(['in_flag'=>1],['warehousing_num'=>$param['warehousing_num']]);
        }
        //入库单确认
        $result = $warehousingModel->allowField(true)->save(['reviewed_status'=>$param['reviewed_status']],['warehousing_num'=>$param['warehousing_num']]);
        if ($result) {
            //添加操作日志
            $this->setAdminUserLog("审核","提交审核,编号是$param[warehousing_num]","wms_warehousing_order",$warehousing['id']);
            //添加审核日志
            $this->setReviewedLog("入库单","$param[warehousing_num]",'提交审核');
            $this->jkReturn('0000','审核成功');
        }else{
            $this->jkReturn('-1004','审核失败');
        }
    }

    /**
     * 获取食恪订单
     *
     * @Author: guanyl
     * @Date: 2018-12-12
     */
    public function getOrderInfo(){
        $_POST['m'] = 'get_order';
        $_POST['sign'] = md5($_POST['m'] . 'CdByVoakbg7wkTGXMHdTqJy0X9IJJt5G');
        $url = "http://180.76.135.222/g_order_api.php" ;
        $data = $this->postData($url,$_POST);
        $data = json_decode($data);
        print_r($data);die;
    }
    function postData($url,$data='')
    {
        $ch = curl_init();// 初始化一个cURL会话
        $timeout = 300;
        curl_setopt($ch, CURLOPT_URL, $url);// 所请求api的url
        curl_setopt($ch, CURLOPT_POST, true); // 使用post请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);// 请求的数据，使用数组
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);// 将返回的内容作为变量储存
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);// 如果服务器300豪秒内没有响应，脚本就会断开连接
        $handles = curl_exec($ch);// 执行一个curl回话
        curl_close($ch);// 关闭一个CURL会话
        return $handles;// 返回结果
    }
}