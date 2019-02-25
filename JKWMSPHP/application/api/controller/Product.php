<?php
namespace app\Api\controller;
@set_time_limit(0);
/**
 * @title 商品管理
 * @description 接口说明
 * @group 商品管理
 * @header name:key require:1 default: desc:秘钥(区别设置)

 */
use app\api\model\ProductModel;
use app\api\model\SystemConfigModel;
use app\api\model\VendorModel;
use app\api\model\VenProModel;
use app\api\service\UploadService;
use think\Db;
use think\Request;

class Product extends Common
{
    protected $uploadService;

    /**
     * @title 商品列表
     * @description 接口说明
     * @author gyl
     * @url /Product/productList
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:product_num type:varchar require:0 default: other: desc:商品编号
     * @param name:product_name type:varchar require:0 default: other: desc:商品名称
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1other: desc:页数
     *
     * @return id:商品id
     * @return product_num:商品编号
     * @return product_name:商品名称
     * @return product_type:商品类型
     * @return price:进货价格
     * @return sale_price:销售价
     * @return shelf_month:保质期
     * @return specifications_num:规格编号
     * @return unit:单位
     * @return remark:备注
     * @return create_by:创建人
     * @return create_date:创建时间
     * @return modify_by:修改人
     * @return modify_date:修改日期
     */
    public function productList(Request $request,ProductModel $productModel){
        $field = "id,product_num,product_name,product_type,price,sale_price,shelf_month,specifications_num,unit,remark,create_by,create_date,modify_by,modify_date";
        $param = $request->param();
        $where = "disabled = 1";
        if (!empty($param['search_key'])) {
            $where .= " and (product_num like  '%" . $param['search_key'] . "%' or product_name like '%" . $param['search_key'] . "%') ";
        }
        $productList = $productModel
            ->field($field)->where($where)->limit($param["limit"]??10)->page($param['page']??1)->select();
        $productCount = $productModel->where($where)->count();
        $data = array(
            'productList'=>$productList,
            'productCount'=>$productCount
        );
        $this->jkReturn('0000','商品列表',$data);
    }
    /**
     * @title 商品详情
     * @description 接口说明
     * @author gyl
     * @url /Product/productDetail
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:product_id type:varchar require:0 default: other: desc:商品id
     *
     * @return product_num:商品编号
     * @return product_name:商品名称
     */
    public function productDetail(Request $request,ProductModel $productModel){
        $param = $request->param();
        $goodDetail = $productModel
            ->field('product_num,product_name')->where(['id'=>$param['product_id']])->find();
        $this->jkReturn('0000','商品详情',$goodDetail);
    }

    /**
     * @title 商品信息导入模板下载
     * @description 接口说明
     * @author gyl
     * @url /Product/productDownload
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     */
    public function productDownload(SystemConfigModel $systemConfigModel){
        $code = 'base_url';
        $system = $systemConfigModel->where('code',$code)->find();
        $downUrl = $system->value . '/import/product.xlsx';
        $this->jkReturn('0000','商品信息导入模板',$downUrl);
    }

    /**
     * @title 商品信息导入
     * @description 接口说明
     * @author gyl
     * @url /Product/productImport
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     */
    public function productImport(ProductModel $productModel){
        /*文件编码*/
        header("Content-type: text/html; charset=utf-8");
        $this->uploadService = new UploadService();
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
            $this->setAdminUserLog("导入","上传商品信息","wms_product");
            for ($currentRow = 0; $currentRow <= $allRow; $currentRow++) {
                // 跳过第一行
                if ($currentRow == 0) {
                    $currentRow++;
                    continue;
                }
                //食恪sku
                $product_num = (string)$currentSheet->getCellByColumnAndRow(ord("A") - 65,$currentRow)->getValue();//商品编号
                $product_name = addslashes((string)$currentSheet->getCellByColumnAndRow(ord("B") - 65, $currentRow)->getValue());//商品名称
                $product_type = (string)$currentSheet->getCellByColumnAndRow(ord("C") - 65, $currentRow)->getValue();//商品类型
                $unit = (string)$currentSheet->getCellByColumnAndRow(ord("D") - 65, $currentRow)->getValue();//计量单位
                $price = (string)$currentSheet->getCellByColumnAndRow(ord("E") - 65, $currentRow)->getValue();//正常进价
                $sale_price = (string)$currentSheet->getCellByColumnAndRow(ord("F") - 65, $currentRow)->getValue();//销售价
                $rate = (string)$currentSheet->getCellByColumnAndRow(ord("G") - 65, $currentRow)->getValue();//税率
                $shelf_month = (string)$currentSheet->getCellByColumnAndRow(ord("H") - 65, $currentRow)->getValue();//保质期
                $bar_code = (string)$currentSheet->getCellByColumnAndRow(ord("I") - 65, $currentRow)->getValue();//条形码
                $kgs= (string)$currentSheet->getCellByColumnAndRow(ord("J") - 65, $currentRow)->getValue();//规格
                $carton = (string)$currentSheet->getCellByColumnAndRow(ord("K") - 65, $currentRow)->getValue();//箱规
                $pro_tflag = (string)$currentSheet->getCellByColumnAndRow(ord("L") - 65, $currentRow)->getValue();//储存条件
                $vendor_num = (string)$currentSheet->getCellByColumnAndRow(ord("M") - 65, $currentRow)->getValue();//厂商编号
                $origin_place = (string)$currentSheet->getCellByColumnAndRow(ord("N") - 65, $currentRow)->getValue();//原产地
                $brand = (string)$currentSheet->getCellByColumnAndRow(ord("O") - 65, $currentRow)->getValue();//品牌
                if (empty($product_name)) {
                    continue;
                }
                $product = array(
                    'product_num'=>$product_num,
                    'product_name'=>$product_name,
                    'product_type'=>$product_type,
                    'unit'=>$unit,
                    'price'=>$price,
                    'sale_price'=>$sale_price,
                    'rate'=>$rate,
                    'shelf_month'=>$shelf_month,
                    'bar_code'=>$bar_code,
                    'kgs'=>$kgs,
                    'carton'=>$carton,
                    'pro_tflag'=>$pro_tflag,
                    'vendor_num'=>$vendor_num,
                    'origin_place'=>$origin_place,
                    'brand'=>$brand
                );
                $productModel->create($product);
                $a ++;
            }
            $msg = "导入成功</br>成功插入商品：" . $a;
            $this->jkReturn('0000','商品信息导入成功',$msg);
        }
    }

    /**
     * @title 商品信息导出
     * @description 接口说明
     * @author gyl
     * @url /Product/productExport
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     */
    public function productExport(Request $request,ProductModel $productModel) {
        ob_end_clean();
        $param = $request->param();
        $field = "product_num,product_name,product_type,price,sale_price,rate,shelf_month,pro_tflag,unit,bar_code,remark";
        if (!empty($param['id'])) {
            $res = $productModel->field($field)->whereIn('id',$param['id'])->where('disabled',1)->select();
        }else{
            $res = $productModel->field($field)->where('disabled',1)->select();;
        }

        // 实例化excel类
        $objPHPExcel = new \PHPExcel();
        // 操作第一个工作表
        $objPHPExcel->setActiveSheetIndex(0);
        // 设置sheet名
        $objPHPExcel->getActiveSheet()->setTitle('盘点数据列表');
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
        // 列名表头文字加粗
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFont()->setBold(true);
        // 列表头文字居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // 列名赋值
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '商品编号');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '商品名称');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '商品类型');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '进价');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '销售价');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '税率');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '保质期');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '商品规格');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '计量单位');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '条形码');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '备注');

        // 数据起始行
        $row_num = 2;
        // 向每行单元格插入数据
        foreach ($res as $value) {
            // 设置所有垂直居中
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row_num . ':' . 'K' . $row_num)->getAlignment()
                ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $proTFlag = '常温';
            if ($value['pro_tflag'] == 1){
                $proTFlag = '冷冻';
            }
            if ($value['pro_tflag'] == 2){
                $proTFlag = '冷藏';
            }
            // 设置单元格数值
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('A' . $row_num, $value['product_num'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row_num, $value['product_name'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row_num, $value['product_type'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row_num, $value['price'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row_num, $value['sale_price']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('F' . $row_num, $value['rate'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row_num, $value['shelf_month']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row_num, $proTFlag);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row_num, $value['unit']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row_num, $value['bar_code']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row_num, $value['remark']);

            $row_num++;
        }
        $outputFileName = 'product_' . date('Y-m-d H:i:s') . '.xls';
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
     * @title 商品供应商关系信息模板下载
     * @description 接口说明
     * @author gyl
     * @url /Product/venProDownload
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     */
    public function venProDownload(SystemConfigModel $systemConfigModel){
        $code = 'base_url';
        $system = $systemConfigModel->where('code',$code)->find();
        $downUrl = $system->value . '/import/ven_pro.xlsx';
        $this->jkReturn('0000','商品供应商关系信息模板',$downUrl);
    }

    /**
     * @title 商品供应商关系信息导入
     * @description 接口说明
     * @author gyl
     * @url /Product/venProImport
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     */
    public function venProImport(VenProModel $venProModel){
        /*文件编码*/
        header("Content-type: text/html; charset=utf-8");
        $this->uploadService = new UploadService();
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
            $this->setAdminUserLog("导入","商品供应商关系信息","wms_ven_pro");
            for ($currentRow = 0; $currentRow <= $allRow; $currentRow++) {
                // 跳过第一行
                if ($currentRow == 0) {
                    $currentRow++;
                    continue;
                }
                //食恪sku
                $vendor_num = (string)$currentSheet->getCellByColumnAndRow(ord("A") - 65,$currentRow)->getValue();//厂商编号
                $pro_code = addslashes((string)$currentSheet->getCellByColumnAndRow(ord("B") - 65, $currentRow)->getValue());//商品编码
                $is_deultveor = (string)$currentSheet->getCellByColumnAndRow(ord("C") - 65, $currentRow)->getValue();//是否主供应商
                $pro_state = (string)$currentSheet->getCellByColumnAndRow(ord("D") - 65, $currentRow)->getValue();//商品状态
                $contract_type = (string)$currentSheet->getCellByColumnAndRow(ord("E") - 65, $currentRow)->getValue();//合同类型
                $v_price = (string)$currentSheet->getCellByColumnAndRow(ord("F") - 65, $currentRow)->getValue();//进价
                $is_refund = (string)$currentSheet->getCellByColumnAndRow(ord("G") - 65, $currentRow)->getValue();//是否可退货
                $deultveor = 0;
                if ($is_deultveor == '是') {
                    $deultveor = 1;
                }
                $contract = 0;
                if ($contract_type == '代销') {
                    $contract = 1;
                }
                if ($contract_type == '联营') {
                    $contract = 2;
                }
                $refund = 0;
                if ($is_refund == '否') {
                    $refund = 1;
                }
                if (empty($pro_code)) {
                    continue;
                }
                $venPro = array(
                    'vendor_num'=>$vendor_num,
                    'product_num'=>$pro_code,
                    'is_deultveor'=>$deultveor,
                    'pro_state'=>$pro_state,
                    'contract_type'=>$contract,
                    'v_price'=>$v_price,
                    'is_refund'=>$refund
                );
                $venProModel->create($venPro);
                $a ++;
            }
            $msg = "导入成功</br>成功插入商品供应商关系：" . $a;
            $this->jkReturn('0000','商品供应商关系信息导入成功',$msg);
        }
    }
    /**
     * @title 供应商列表
     * @description 接口说明
     * @author gyl
     * @url /Product/vendor
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
        $vendorList = $vendorModel->field('vendor_num,vendor_name')->where($where)->select();
        $this->jkReturn('0000','供应商列表',$vendorList);
    }

    /**
     * @title 绑定供应商
     * @description 接口说明
     * @author gyl
     * @url /Product/vendorGood
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     * @param name:vendorGood type:array  require:1 default: other: desc:数组
     * @param name:vendor_num type:string require:1 default: other: desc:供应商编号
     * @param name:is_deultveor type:int require:1 default: other: desc:是否默认供应商(1是/0否)
     * @param name:pro_state type:string require:1 default: other: desc:商品状态
     * @param name:contract_type type:int require:1 default: other: desc:合同类型(0经销/1代销/2联营)
     * @param name:v_price type:string require:1 default: other: desc:正常进价
     * @param name:is_refund type:int require:1 default: other: desc:是否可退货(0是/1否)
     * @param name:product_num type:string require:1 default: other: desc:商品编号
     *
     */
    public function vendorGood(Request $request, VenProModel $venProModel){
        $param = $request->param();
        if (empty($param['vendorGood'])) {
            $this->jkReturn('-1004','数据为空');
        }
        foreach ($param['vendorGood'] as $k => $v) {
            if (empty($param['vendorGood'][$k]['product_num']) or empty($param['vendorGood'][$k]['vendor_num'])) {
                $this->jkReturn('-1004','请填写商品编号或供应商编号');
            }
        }
        if (!empty($param['vendorGood'])) {
            if(!$venProModel->allowField(true)->saveAll($param['vendorGood'])){
                $this->jkReturn('-1004','绑定失败');
            }
            $this->setAdminUserLog("新增","绑定供应商","wms_ven_pro");
            $this->jkReturn('0000','绑定成功');
        }
    }
}