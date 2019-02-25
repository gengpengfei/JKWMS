<?php
/**
 * Created by PhpStorm.
 * User: guanyl
 * Date: 2018/11/15
 * Time: 11:32
 */

namespace app\Api\controller;
use app\api\model\ApprovalModel;
use app\api\model\ProductModel;
use app\api\model\PurOrderModel;
use app\api\model\PurProductModel;
use app\api\model\SystemConfigModel;
use app\api\model\VendorModel;
use app\api\model\WarehouseModel;
use app\api\service\UploadService;
use think\Request;

/**
 * @title 采购订单管理
 * @description 接口说明
 * @group 采购管理
 * @header name:key require:1 default: desc:秘钥(区别设置)

 */

class Purchase extends Common
{
    protected $uploadService;
    /**
     * @title 采购单信息列表
     * @description 接口说明
     * @author gyl
     * @url /Purchase/purOrderList
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:采购单编号
     * @param name:in_flag type:string require:0 default: other: desc:是否入库
     * @param name:wh_id type:string require:0 default: other: desc:仓库id
     * @param name:start_date type:int require:0 default: 1 other: desc:搜索开始日期
     * @param name:end_date type:int require:0 default: 1 other: desc:搜索结束日期
     * @param name:appr_state type:int require:0 default: 1 other: desc:审核状态(0:未审核,1:初审中,2二审中,3:终审中,4:终审通过,5:审核不通过,6:已取消)
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1other: desc:页数
     *
     * @return purOrderList:采购单信息列表@
     * @purOrderList pur_order_num:采购单编号 pur_order_date:采购单日期 warehouse_name:仓库 pur_chaser:采购人姓名 supplier:供应商编码 vendor_name:供应商 remark:备注 appr_state:审核状态(0:未审核,1:初审中,2二审中,3:终审中,4:终审通过,5:审核不通过,6:已取消) appr_advice:审核意见 appr_date:审核时间 create_by:创建人 create_date：创建时间 in_flag:是否入库(0：否，1：是) pur_order_status:订单状态
     * @return purOrderCount:采购单数量
     */
    public function purOrderList(Request $request, PurOrderModel $purOrderModel){
        $param = $request->param();
        $where = "o.disabled = 1";
        $field = "o.id,o.pur_order_num,o.pur_order_date,o.wh_id,o.pur_chaser,o.supplier,o.remark,o.create_by,o.create_date,w.warehouse_name,v.vendor_name,a.appr_state,a.appr_advice,a.appr_date,s.in_flag,o.pur_order_status";
        if (!empty($param['search_key'])) {
            $where .= " and (o.pur_order_num like '%" . $param['search_key'] . "%' or o.pur_chaser like '%" . $param['search_key'] . "%' or o.supplier like '%" . $param['search_key'] . "%' or v.vendor_name like '%" . $param['search_key'] . "%') ";
        }
        if (!empty($param['start_date']) && !empty($param['end_date'])) {
            $where .= " and date_format(o.pur_order_date,'%Y-%m-%d') >= date_format('" . $param['start_date'] . "','%Y-%m-%d')";
            $where .= " and date_format(o.pur_order_date,'%Y-%m-%d') <= date_format('" . $param['end_date'] . "','%Y-%m-%d')";
        }
        if (!empty($param['appr_state'])) {
            $where .= " and a.appr_state = $param[appr_state]";
        }
        $purOrderList = $purOrderModel
            ->field($field)
            ->alias('o')
            ->where($where)
            ->join('wms_warehouse w','w.id = o.wh_id','left')
            ->join('wms_vendor v','v.vendor_num = o.supplier','left')
            ->join('wms_approval a','a.appr_num = o.pur_order_num','left')
            ->join('wms_pur_storder s','s.pur_order_id = o.id','left')
            ->limit($param["limit"]??10)
            ->page($param['page']??1)
            ->select();
        $purOrderCount = $purOrderModel
            ->field($field)
            ->alias('o')
            ->where($where)
            ->join('wms_warehouse w','w.id = o.wh_id','left')
            ->join('wms_vendor v','v.vendor_num = o.supplier','left')
            ->join('wms_approval a','a.appr_num = o.pur_order_num','left')
            ->join('wms_pur_storder s','s.pur_order_id = o.id','left')
            ->count();
        $data = array(
            'purOrderList'=>$purOrderList,
            'purOrderCount'=>$purOrderCount
        );
        $this->jkReturn('0000','采购单信息列表',$data);
    }

    /**
     * @title 采购订单详情
     * @description 接口说明
     * @author gyl
     * @url /Purchase/purOrderDetail
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     * @param name:pur_order_num type:int require:1 default: other: desc:采购订单编号
     *
     * @return pur_order_num:采购订单编号
     * @return pur_order_date:采购订单日期
     * @return supplier:供应商编码
     * @return vendor_name:供应商
     * @return order_place:订购地点
     * @return warehouse_name:仓库选择
     * @return delivery_date:送货日期
     * @return delivery_way:送货方式
     * @return allPrice:采购金额
     * @return remark:采购单备注
     * @return in_flag:是否入库(0：否，1：是)
     * @return appr_state:审核状态(0:未审核,1:初审中,2二审中,3:终审中,4:终审通过,5:审核不通过,6:已取消)
     * @return appr_advice:审核意见
     * @return appr_date:审核时间
     * @return pur_order_status:订单状态
     * @return product_info:采购商品信息@
     * @product_info product_num:商品编号 product_name:商品名称 bar_code:商品条形码 vendor_name:供应商名称 price:进价 unit:单位 shelf_month:保质期 specifications:规格 carton:箱规 input_tax:税率 batch_code:批次号 product_amount:采购数量
     *
     */
    public function purOrderDetail(Request $request, PurOrderModel $purOrderModel, PurProductModel $purProductModel){
        $param = $request->param();
        $field = "o.id,o.pur_order_num,o.pur_order_date,o.supplier,v.vendor_name,o.wh_id,o.order_place,w.warehouse_name,o.delivery_start_date,o.delivery_end_date,o.delivery_way,o.remark,a.appr_state,a.appr_advice,a.appr_date,s.in_flag,o.pur_order_status";
        $purOrder = $purOrderModel
            ->field($field)
            ->alias('o')
            ->where('o.pur_order_num',$param['pur_order_num'])
            ->join('wms_warehouse w','w.id = o.wh_id','left')
            ->join('wms_vendor v','v.vendor_num = o.supplier','left')
            ->join('wms_approval a','a.appr_num = o.pur_order_num','left')
            ->join('wms_pur_storder s','s.pur_order_id = o.id','left')
            ->find();
        $allPrice = 0;
        if (count($purOrder) >= 1) {
            $purOrder['product_info'] = $purProductModel
                ->field('pp.*,p.bar_code')
                ->alias('pp')
                ->where('pur_order_num',$purOrder['pur_order_num'])
                ->join('wms_product_info p','p.product_num = pp.product_num','left')
                ->select();
            if (count($purOrder['product_info']) >= 1) {
                foreach ($purOrder['product_info'] as $product_info) {
                    $allAmount = $product_info['price'] * $product_info['product_amount'];
                    $allPrice += $allAmount;
                }
            }
        }
        $purOrder['allPrice'] = $allPrice;
        $this->jkReturn('0000','采购订单详情',$purOrder);
    }

    /**
     * @title 采购订单模板下载
     * @description 接口说明
     * @author gyl
     * @url /Purchase/purOrderDownload
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     */
    public function purOrderDownload(SystemConfigModel $systemConfigModel){
        $code = 'base_url';
        $system = $systemConfigModel->where('code',$code)->find();
        $downUrl = $system->value . '/import/pur_order.xlsx';
        $this->jkReturn('0000','采购订单模板',$downUrl);
    }

    /**
     * @title 采购订单导入
     * @description 接口说明
     * @author gyl
     * @url /Purchase/purOrderImport
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     * @param name:wh_id type:string require:0 default: other: desc:仓库id
     * @param name:order_place type:string require:0 default: other: desc:订购地点
     */
    public function productImport(Request $request, PurOrderModel $purOrderModel, PurProductModel $purProductModel, VendorModel $vendorModel){
        /*文件编码*/
        header("Content-type: text/html; charset=utf-8");
        $this->uploadService = new UploadService();
        $param = $request->param();
        if (!empty($_FILES['file'])) {
            $tmp_file = $_FILES['file'] ['tmp_name'];
            $file_types = explode(".", $_FILES ['file'] ['name']);
            $file_type = end($file_types);
            /*判断是不是.xls文件，判断是不是excel文件*/
            if (strtolower($file_type) != "xlsx") {
                $this->jkReturn('-1004','不是规定文件，重新上传');
            }
            /*设置上传路径*/
            $savePath = '/upload';
            if(!file_exists('.'.$savePath)) $this->uploadService->createFile($savePath);

            /*以时间来命名上传的文件*/
            $str = date('Ymdhis');
            $file_name = "Product_" . $str . "." . $file_type;

            /*是否上传成功*/
            if (!move_uploaded_file($tmp_file, $savePath . $file_name)) {
                $this->jkReturn('-1004','上传失败');
            }
            $filePath = $savePath . $file_name;
            if (empty($filePath) or !file_exists($filePath)) {
                $this->jkReturn('-1004','file not exists');
            }

            $PHPReader = new \PHPExcel_Reader_Excel2007();        //建立reader对象
            if (!$PHPReader->canRead($filePath)) {
                $PHPReader = new \PHPExcel_Reader_Excel5();
                if (!$PHPReader->canRead($filePath)) {
                    $this->jkReturn('-1004','no Excel');
                }
            }
            $a = 0;

            $PHPExcel = $PHPReader->load($filePath);        //建立excel对象
            $currentSheet = $PHPExcel->getSheet(0);        //**读取excel文件中的指定工作表*/
            $allRow = $currentSheet->getHighestRow();        //**取得一共有多少行*/
            //记录操作日志
            $this->setAdminUserLog("导入","采购订单导入","wms_product");
            for ($currentRow = 0; $currentRow <= $allRow; $currentRow++) {
                // 跳过第一行
                if ($currentRow == 0) {
                    $currentRow++;
                    continue;
                }
                $supplier= (string)$currentSheet->getCellByColumnAndRow(ord("A") - 65,$currentRow)->getValue();//供应商编号
                $vendor_name = addslashes((string)$currentSheet->getCellByColumnAndRow(ord("B") - 65, $currentRow)->getValue());//供应商名称
                $delivery_date = (string)$currentSheet->getCellByColumnAndRow(ord("C") - 65, $currentRow)->getValue();//送货日期
                $delivery_way = (string)$currentSheet->getCellByColumnAndRow(ord("D") - 65, $currentRow)->getValue();//送货方式
                $product_num = (string)$currentSheet->getCellByColumnAndRow(ord("E") - 65, $currentRow)->getValue();//商品编码
                $product_name = (string)$currentSheet->getCellByColumnAndRow(ord("F") - 65, $currentRow)->getValue();//商品名称
                $price = (string)$currentSheet->getCellByColumnAndRow(ord("G") - 65, $currentRow)->getValue();//进价
                $product_amount = (string)$currentSheet->getCellByColumnAndRow(ord("H") - 65, $currentRow)->getValue();//数量
                $input_tax = (string)$currentSheet->getCellByColumnAndRow(ord("I") - 65, $currentRow)->getValue();//税率
                $remark = (string)$currentSheet->getCellByColumnAndRow(ord("J") - 65, $currentRow)->getValue();//备注
                if (empty($product_name) || empty($product_num)) {
                    continue;
                }
                $vendor = $vendorModel->where(['vendor_num'=>$supplier, 'vendor_name'=>$vendor_name])->find();
                if (count($vendor) <= 0) {
                    $data = array(
                        'vendor_num'=>$supplier,
                        'vendor_name'=>$vendor_name
                    );
                    $vendorModel->create($data);
                }
                $product = array(
                    'product_num'=>$product_num,
                    'product_name'=>$product_name,
                    'price'=>$price,
                    'product_amount'=>$product_amount,
                    'input_tax'=>$input_tax
                );
                $purProductModel->create($product);
                $order = array(
                    'supplier'=>$supplier,
                    'wh_id'=>$param['wh_id'],
                    'order_place'=>$param['order_place'],
                    'delivery_start_date'=>$delivery_date,
                    'delivery_end_date'=>$delivery_date,
                    'delivery_way'=>$delivery_way,
                    'remark'=>$remark
                );
                $purOrderModel->create($order);
                $a ++;
            }
            $msg = "导入成功</br>成功导入采购订单：" . $a;
            $this->jkReturn('0000','采购订单导入成功',$msg);
        }
    }

    /**
     * @title 采购订单导出
     * @description 接口说明
     * @author gyl
     * @url /Purchase/purOrderExport
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:pur_order_num type:string require:0 default: other: desc:采购单编号
     */
    public function purOrderExport(Request $request,PurProductModel $purProductModel) {
        ob_end_clean();
        $param = $request->param();
        $field = "o.pur_order_num,o.pur_order_date,o.pur_chaser,o.pur_chaser_mobile,v.vendor_name,o.delivery_start_date,o.delivery_way,p.product_num,i.bar_code,p.product_name,p.specifications,p.product_amount,p.pro_create_date,p.shelf_month,p.price,p.input_tax";
        /*$param['pur_order_num'] = array(
            'OI-20170309-0002',
            'OI-20170309-0003'
        );*/
        if (!empty($param['pur_order_num']) && count($param['pur_order_num']) > 1) {
            $res = $purProductModel
                ->field($field)
                ->alias('p')
                ->whereIn('p.pur_order_num',$param['pur_order_num'])
                ->join('wms_pur_order o','p.pur_order_num = o.pur_order_num','left')
                ->join('wms_vendor v','v.vendor_num = o.supplier','left')
                ->join('wms_product_info i','p.product_num = i.product_num','left')
                ->select();
            $count = $purProductModel
                ->field($field)
                ->alias('p')
                ->whereIn('p.pur_order_num',$param['pur_order_num'])
                ->join('wms_pur_order o','p.pur_order_num = o.pur_order_num','left')
                ->join('wms_vendor v','v.vendor_num = o.supplier','left')
                ->join('wms_product_info i','p.product_num = i.product_num','left')
                ->count();
        }else{
            $res = $purProductModel
                ->field($field)
                ->alias('p')
                ->where('p.pur_order_num',$param['pur_order_num'])
                ->join('wms_pur_order o','p.pur_order_num = o.pur_order_num','left')
                ->join('wms_vendor v','v.vendor_num = o.supplier','left')
                ->join('wms_product_info i','p.product_num = i.product_num','left')
                ->select();
            $count = $purProductModel
                ->field($field)
                ->alias('p')
                ->where('p.pur_order_num',$param['pur_order_num'])
                ->join('wms_pur_order o','p.pur_order_num = o.pur_order_num','left')
                ->join('wms_vendor v','v.vendor_num = o.supplier','left')
                ->join('wms_product_info i','p.product_num = i.product_num','left')
                ->count();
        }
        $productCount = '至本页合计单品数：' . $count;
        // 实例化excel类
        $objPHPExcel = new \PHPExcel();
        // 操作第一个工作表
        $objPHPExcel->setActiveSheetIndex(0);
        // 设置sheet名
        $objPHPExcel->getActiveSheet()->setTitle('purOrderProduct');
        // 设置表格宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
        // 列名表头文字加粗
        $objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->getFont()->setBold(true);
        // 列表头文字居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // 列名赋值
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '采购单编号');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '采购订单日期');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '采购人姓名');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '采购人联系方式');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '供应商');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '送货日期');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '送货方式');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '商品编号');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '商品条码');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '商品名称');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '规格');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', '数量');
        $objPHPExcel->getActiveSheet()->setCellValue('M1', '生产日期');
        $objPHPExcel->getActiveSheet()->setCellValue('N1', '保质期');
        $objPHPExcel->getActiveSheet()->setCellValue('O1', '进价');
        $objPHPExcel->getActiveSheet()->setCellValue('P1', '订货金额');
        $objPHPExcel->getActiveSheet()->setCellValue('Q1', '进项税');

        // 数据起始行
        $row_num = 2;
        $allAmount = 0;
        // 向每行单元格插入数据
        foreach ($res as $value) {
            // 设置所有垂直居中
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row_num . ':' . 'Q' . $row_num)->getAlignment()
                ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $allPrice = $value['price'] * $value['product_amount'];
            $allAmount += $allPrice;
            // 设置单元格数值
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('A' . $row_num, $value['pur_order_num'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row_num, $value['pur_order_date'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row_num, $value['pur_chaser'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row_num, $value['pur_chaser_mobile'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row_num, $value['vendor_name'],\PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('F' . $row_num, $value['delivery_start_date'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row_num, $value['delivery_way'],\PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row_num, $value['product_num'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row_num, $value['bar_code'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row_num, $value['product_name'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row_num, $value['specifications'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row_num, $value['product_amount'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $row_num, $value['pro_create_date'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $row_num, $value['shelf_month'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . $row_num, $value['price'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . $row_num, $allPrice, \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row_num, $value['input_tax'], \PHPExcel_Cell_DataType::TYPE_STRING);

            $row_num++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row_num, $productCount, \PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValue('P' . $row_num, $allAmount, \PHPExcel_Cell_DataType::TYPE_STRING);

        $outputFileName = 'purOrderProduct_' . date('Y-m-d H:i:s') . '.xls';
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
     * @title 仓库列表
     * @description 接口说明
     * @author gyl
     * @url /Purchase/warehouseList
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     *
     * @return warehouse_num:仓库编号
     * @return warehouse_name:仓库名称
     */
    public function warehouseList(WarehouseModel $warehouseModel){
        $where = "disabled = 1";
        $warehouseList = $warehouseModel->field("id,warehouse_num,warehouse_name")->where($where)->select();
        $this->jkReturn('0000','仓库列表',$warehouseList);
    }

    /**
     * @title 供应商列表
     * @description 接口说明
     * @author gyl
     * @url /Purchase/vendorList
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:varchar require:0 default: other: desc:查询值
     *
     * @return vendor_num:供应商编号
     * @return vendor_name:供应商名称
     */
    public function vendor(Request $request,VendorModel $vendorModel){
        $param = $request->param();
        $where = "disabled = 1";
        if (!empty($param['search_key'])) {
            $where .= " and (vendor_num like '%" . $param['search_key'] . "%' or vendor_name like '%" . $param['search_key'] . "%') ";
        }
        $vendorList = $vendorModel->field('id,vendor_num,vendor_name')->where($where)->select();
        $this->jkReturn('0000','供应商列表',$vendorList);
    }

    /**
     * @title 商品列表
     * @description 接口说明
     * @author gyl
     * @url /Purchase/productList
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:varchar require:0 default: other: desc:查询值
     * @param name:vendor_num type:varchar require:1 default: other: desc:供应商编号
     *
     * @return product_num:商品编号
     * @return product_name:商品名称
     * @return bar_code:商品条码
     * @return price:进价
     * @return specifications_num:规格
     * @return unit:计量单位
     * @return shelf_month:保质期
     * @return carton:箱规
     * @return rate:税率
     */
    public function productList(Request $request,ProductModel $productModel){
        $param = $request->param();
        if (empty($param['vendor_num'])) {
            $this->jkReturn('-1004','请先选择供应商！');
        }
        $where = "p.disabled = 1 and p.vendor_num = '$param[vendor_num]' ";
        $field = "p.id,p.product_num,p.product_name,i.bar_code,p.price,p.specifications_num,p.unit,p.shelf_month,p.carton,p.rate";
        if (!empty($param['search_key'])) {
            $where .= " and (p.product_num like '%" . $param['search_key'] . "%' or p.product_name like '%" . $param['search_key'] . "%') ";
        }
        $productList = $productModel
            ->field($field)
            ->alias('p')
            ->where($where)
            ->join('wms_product_info i','p.product_num = i.product_num','left')
            ->select();
        $this->jkReturn('0000','商品列表',$productList);
    }

    /**
     * @title 采购单新建
     * @description 接口说明
     * @author gyl
     * @url /Purchase/purOrderAdd
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:order_place type:string require:1 default: other: desc:订货地(上海，北京，苏州)
     * @param name:pur_order_num type:string require:0 default: 1 1other: desc:采购单编号
     * @param name:pur_order_date type:string require:1 default: other: desc:采购订单日期
     * @param name:wh_id type:string require:1 default: other: desc:仓库id
     * @param name:supplier type:string require:1 default: other: desc:供应商编号
     * @param name:delivery_start_date type:string require:1 default: other: desc:送货开始日期
     * @param name:delivery_end_date type:string require:1 default: other: desc:送货结束日期
     * @param name:delivery_way type:string require:1 default: other: desc:送货方式（直送，自提）
     * @param name:remark type:string require:1 default: other: desc:采购单备注
     * @param name:pur_product type:array require:1 default: other: desc:采购商品信息
     * @param name:product_num type:string require:1 default: other: desc:商品编号
     * @param name:product_name type:string require:1 default: other: desc:商品名称
     * @param name:bar_code type:string require:1 default: other: desc:商品条码
     * @param name:price type:string require:1 default: other: desc:进价
     * @param name:specifications type:string require:1 default: other: desc:规格
     * @param name:unit type:string require:1 default: other: desc:计量单位
     * @param name:shelf_month type:string require:1 default: other: desc:保质期
     * @param name:carton type:string require:1 default: other: desc:箱规
     * @param name:input_tax type:string require:1 default: other: desc:税率
     * @param name:product_amount type:string require:1 default: other: desc:采购数量
     *
     */
    public function purOrderAdd(Request $request, PurOrderModel $purOrderModel, PurProductModel $purProductModel){
        $param = $request->param();
        $date = date('Y-m-d');
        $where = "date_format(pur_order_date,'%Y-%m-%d') = date_format('" . $date . "','%Y-%m-%d')";
        $purOrder = $purOrderModel->where($where)->count();
        $number = $this->number($purOrder);
        $param['pur_order_num'] = 'OI-' . date('Ymd') . '-' . $number;//采购单编号
        $param['pur_order_date'] = date('Y-m-d H:i:s');//采购订单日期
        $result = $purOrderModel->allowField(true)->save($param);
        if($result){
            $purOrderId = $purOrderModel->getLastInsID();
            $this->setAdminUserLog("新增","采购单新建","wms_pur_order",$purOrderId);
            if (!empty($param['pur_product'])) {
                foreach ($param['pur_product'] as &$pur_product) {
                    $pur_product['pur_order_num'] = $param['pur_order_num'];//采购单编号
                    $pur_product['supplier'] = $param['supplier'];//供应商编号
                }
            }
            $purProductModel->allowField(true)->saveAll($param['pur_product']);
            $this->jkReturn('0000','添加成功');
        }else{
            $this->jkReturn('-1004','添加失败');
        }
    }

    /**
     * @title 采购单更新
     * @description 接口说明
     * @author gyl
     * @url /Purchase/purOrderEdit
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:string require:1 default: other: desc:采购单id
     * @param name:order_place type:string require:1 default: other: desc:订货地(上海，北京，苏州)
     * @param name:pur_order_num type:string require:0 default: 1 1other: desc:采购单编号
     * @param name:wh_id type:string require:1 default: other: desc:仓库id
     * @param name:supplier type:string require:1 default: other: desc:供应商编号
     * @param name:delivery_start_date type:string require:1 default: other: desc:送货开始日期
     * @param name:delivery_end_date type:string require:1 default: other: desc:送货结束日期
     * @param name:delivery_way type:string require:1 default: other: desc:送货方式（直送，自提）
     * @param name:remark type:string require:1 default: other: desc:采购单备注
     * @param name:pur_product type:array require:1 default: other: desc:采购商品信息
     * @param name:product_num type:string require:1 default: other: desc:商品编号
     * @param name:product_name type:string require:1 default: other: desc:商品名称
     * @param name:bar_code type:string require:1 default: other: desc:商品条码
     * @param name:price type:string require:1 default: other: desc:进价
     * @param name:specifications type:string require:1 default: other: desc:规格
     * @param name:unit type:string require:1 default: other: desc:计量单位
     * @param name:shelf_month type:string require:1 default: other: desc:保质期
     * @param name:carton type:string require:1 default: other: desc:箱规
     * @param name:input_tax type:string require:1 default: other: desc:税率
     * @param name:product_amount type:string require:1 default: other: desc:采购数量
     *
     */
    public function purOrderEdit(Request $request, PurOrderModel $purOrderModel, PurProductModel $purProductModel){
        $param = $request->param();
        if (empty($param['id'])) {
            $this->jkReturn('-1004','请填写采购单id');
        }
        $where['id'] = $param['id'];
        $result = $purOrderModel->allowField(true)->save($param);
        if($result){
            $this->setAdminUserLog("更新","采购单更新","wms_pur_order",$param['id']);
            $purProductModel->allowField(true)->saveAll($param['pur_product']);
            $this->jkReturn('0000','更新成功');
        }else{
            $this->jkReturn('-1004','更新失败');
        }
    }

    /**
     * @title 采购单删除
     * @description 接口说明
     * @author gyl
     * @url /Purchase/purOrderDel
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:pur_order_num type:string require:0 default: other: desc:采购单编号
     *
     */
    public function purOrderDel(Request $request, PurOrderModel $purOrderModel, PurProductModel $purProductModel){
        $param = $request->param();
        $purProductModel->startTrans();
        //-- 删除绑定商品
        if(!$purProductModel->where(['pur_order_num'=>$param['pur_order_num']])->delete()){
            $purProductModel->rollback();
            $this->jkReturn('-1004',"采购单商品删除失败,采购单编号为'$param[pur_order_num]'");
        }
        $this->setAdminUserLog("删除","删除采购单商品,采购单编号为'$param[pur_order_num]'","wms_pur_product");
        if(!$purOrderModel->where(['pur_order_num'=>$param['pur_order_num']])->delete()){
            $this->jkReturn('-1004',"删除失败,采购单编号为'$param[pur_order_num]'");
        }
        $this->setAdminUserLog("删除","删除采购单,采购单编号为'$param[pur_order_num]'","wms_pur_order");
        $purProductModel->commit();
        $this->jkReturn('0000','删除成功');
    }

    /**
     * @title 采购单审核
     * @description 接口说明
     * @author gyl
     * @url /Purchase/reviewed
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:appr_state type:int require:1 default: other: desc:审核状态(0:未审核,1:初审中,2二审中,3:终审中,4:终审通过,5:审核不通过,6:已取消)
     * @param name:appr_advice type:int require:1 default: other: desc:审核意见
     * @param name:appr_num type:string require:1 default: other: desc:采购单编号
     *
     */
    public function reviewed(Request $request, ApprovalModel $approvalModel){
        $param = $request->param();
        $data['appr_name'] = 'admin123';
        $data['appr_date'] = date('Y-m-d H:i:s');
        $where = ['appr_num'=>$param['appr_num']];
        $result = $approvalModel->allowField(true)->save($param,$where);
        if($result){
            $this->setAdminUserLog("审核","采购单审核,采购单编号是$param[appr_num]","wms_approval");
            $sh_info = $approvalModel->getApplyStatusAttr($param['appr_state']);
            $this->setReviewedLog("采购单","$param[appr_num]",$sh_info);
            $this->jkReturn('0000','审核成功');
        }else{
            $this->jkReturn('-1004','审核失败');
        }
    }

    /**
     * @title 采购单提交
     * @description 接口说明
     * @author gyl
     * @url /Purchase/submit
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:appr_num type:string require:1 default: other: desc:采购单编号
     *
     */
    public function submit(Request $request, ApprovalModel $approvalModel){
        $param = $request->param();
        $data['appr_source'] = '采购单审核';
        $data['appr_state'] = 1;
        $data['appr_submit'] = date('Y-m-d H:i:s');
        $result = $approvalModel->allowField(true)->save($param);
        if($result){
            $this->setAdminUserLog("审核","采购单审核,采购单编号是$param[appr_num]","wms_approval");
            $this->setReviewedLog("采购单","$param[appr_num]","采购单提交审核");
            $this->jkReturn('0000','提交成功');
        }else{
            $this->jkReturn('-1004','提交失败');
        }
    }

    /**
     * @title 审核单详情
     * @description 接口说明
     * @author gyl
     * @url /Purchase/approvalDetail
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     * @param name:appr_num type:int require:1 default: other: desc:审核表编号
     *
     * @return appr_num:审核表编号
     * @return appr_source:审核单来源
     * @return warehouse_name:仓库选择
     * @return remark:采购单备注
     * @return appr_state:审核状态(0:未审核,1:初审中,2二审中,3:终审中,4:终审通过,5:审核不通过,6:已取消)
     * @return appr_name:审核人
     * @return appr_advice:审核意见
     * @return appr_submit:提交日期
     * @return appr_date:审核日期
     * @return create_by:创建人
     * @return create_date:创建时间
     * @return modify_by:修改人
     * @return modify_date:修改时间
     * @return product_info:采购商品信息@
     * @product_info product_num:商品编号 product_name:商品名称 product_amount:采购数量
     *
     */
    public function approvalDetail(Request $request, ApprovalModel $approvalModel, PurProductModel $purProductModel){
        $param = $request->param();
        $field = "a.appr_num,a.appr_source,w.warehouse_name,o.remark,a.appr_state,a.appr_name,a.appr_advice,a.appr_submit,a.appr_date,a.create_by,a.create_date,a.modify_by,a.modify_date";
        $approvalOrder = $approvalModel
            ->field($field)
            ->alias('a')
            ->where('a.appr_num',$param['appr_num'])
            ->join('wms_pur_order o','a.appr_num = o.pur_order_num','left')
            ->join('wms_warehouse w','w.id = o.wh_id','left')
            ->find();
        if (count($approvalOrder) >= 1) {
            $approvalOrder['product_info'] = $purProductModel
                ->field('product_num,product_name,product_amount')
                ->where('pur_order_num',$approvalOrder['appr_num'])
                ->select();
        }
        $this->jkReturn('0000','审核单详情',$approvalOrder);
    }
}