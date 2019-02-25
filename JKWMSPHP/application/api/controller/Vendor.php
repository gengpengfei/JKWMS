<?php
/**
 * Created by PhpStorm.
 * User: guanyl
 * Date: 2018/11/12
 * Time: 10:24
 */

namespace app\Api\controller;
use app\api\model\ProductModel;
use app\api\model\SystemConfigModel;
use app\api\model\VendorModel;
use app\api\model\VenProModel;
use app\api\service\UploadService;
use think\Request;

/**
 * @title 厂商管理
 * @description 接口说明
 * @group 厂商管理
 * @header name:key require:1 default: desc:秘钥(区别设置)
 */

class Vendor extends Common
{
    protected $uploadService;
    /**
     * @title 厂商列表
     * @description 接口说明
     * @author gyl
     * @url /Vendor/index
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:厂商编号、厂商编号
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1other: desc:页数
     *
     * @return vendorList:厂商列表@
     * @vendorList vendor_num:厂商编号 vendor_name:厂商名称 contact_name:联系人 contact_tel:联系人电话 tag:标记是物流厂商还是供应商（0：厂商；1：供应商） remark:备注  create_by:创建人 create_date:创建时间 modify_by:修改人 modify_date:修改时间
     * @return vendorCount:厂商数量
     */
    public function index(Request $request, VendorModel $vendorModel)
    {
        $param = $request->param();
        $where = "disabled = 1";
        if (!empty($param['search_key'])) {
            $where .= " and (vendor_num like  '%" . $param['search_key'] . "%' or vendor_name like '%" . $param['search_key'] . "%') ";
        }
        $vendorList = $vendorModel
            ->where($where)->limit($param["limit"]??10)->page($param['page']??1)->select();
        //echo $productTypeModel->getLastSql();die;
        $vendorCount = $vendorModel->where($where)->count();
        $data = array(
            'vendorList'=>$vendorList,
            'vendorCount'=>$vendorCount
        );
        $this->jkReturn('0000','厂商列表',$data);
    }

    /**
     * @title 厂商添加
     * @description 接口说明
     * @author gyl
     * @url /Vendor/vendorAdd
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:vendor_num type:string require:1 default: other: desc:厂商编号
     * @param name:vendor_name type:string require:1 default: other: desc:厂商名称
     * @param name:contact_name type:string require:1 default: other: desc:联系人姓名
     * @param name:contact_tel type:string require:1 default: other: desc:联系人电话
     * @param name:contact_mobile type:string require:1 default: other: desc:联系人手机
     * @param name:contact_fax type:string require:0 default: other: desc:联系人传真
     * @param name:contact_qq type:string require:0 default: other: desc:联系人qq
     * @param name:contact_wechat type:string require:0 default: other: desc:联系人微信
     * @param name:contact_email type:string require:0 default: other: desc:联系人email
     * @param name:tag type:string require:1 default: other: desc:标记是物流厂商还是供应商（0：厂商；1：供应商）
     * @param name:remark type:string require:0 default: other: desc:备注
     * @param name:create_by type:string require:0 default: other: desc:创建人
     * @param name:create_date type:string require:0 default: other: desc:创建时间
     * @param name:modify_by type:string require:0 default: other: desc:修改人
     * @param name:modify_date type:string require:0 default: other: desc:修改时间
     *
     */
    public function vendorAdd(Request $request, VendorModel $vendorModel){
        $param = $request->param();
        //$result = $productTypeModel->create($data);
        $vendor = $vendorModel->where(['vendor_num'=>$param['vendor_num']])->find();
        if (count($vendor) >= 1) {
            $this->jkReturn('-1004',"厂商编号已存在");
        }
        $result = $vendorModel->allowField(true)->save($param);
        if($result){
            $vendor_id = $vendorModel->getLastInsID();
            $this->setAdminUserLog("新增","添加厂商","wms_vendor",$vendor_id);
            $this->jkReturn('0000','添加成功');
        }else{
            $this->jkReturn('-1004','添加失败');
        }
    }
    /**
     * @title 厂商编辑
     * @description 接口说明
     * @author gyl
     * @url /Vendor/vendorEdit
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:string require:1 default: other: desc:厂商id
     * @param name:vendor_num type:string require:1 default: other: desc:厂商编号
     * @param name:vendor_name type:string require:1 default: other: desc:厂商名称
     * @param name:contact_name type:string require:1 default: other: desc:联系人姓名
     * @param name:contact_tel type:string require:1 default: other: desc:联系人电话
     * @param name:contact_mobile type:string require:1 default: other: desc:联系人手机
     * @param name:contact_fax type:string require:0 default: other: desc:联系人传真
     * @param name:contact_qq type:string require:0 default: other: desc:联系人qq
     * @param name:contact_wechat type:string require:0 default: other: desc:联系人微信
     * @param name:contact_email type:string require:0 default: other: desc:联系人email
     * @param name:tag type:string require:1 default: other: desc:标记是物流厂商还是供应商（0：厂商；1：供应商）
     * @param name:remark type:string require:0 default: other: desc:备注
     * @param name:create_by type:string require:0 default: other: desc:创建人
     * @param name:create_date type:string require:0 default: other: desc:创建时间
     * @param name:modify_by type:string require:0 default: other: desc:修改人
     * @param name:modify_date type:string require:0 default: other: desc:修改时间
     *
     */
    public function vendorEdit(Request $request, VendorModel $vendorModel){
        $param = $request->param();
        $vendor = $vendorModel->where('id',$param['id'])->select();
        if (count($vendor) <= 0) {
            $this->jkReturn('-1004','分类不存在');
        }
        $vendor = $vendorModel->where(['vendor_num'=>$param['vendor_num']])->find();
        if (count($vendor) >= 1) {
            $this->jkReturn('-1004',"厂商编号已存在");
        }
        $where = ['id'=>$param['id']];
        $result = $vendorModel->allowField(true)->save($param,$where);
        if($result){
            $this->setAdminUserLog("更新","更新厂商信息","wms_vendor",$param['id']);
            $this->jkReturn('0000','更新成功');
        }else{
            $this->jkReturn('-1004','更新失败');
        }
    }

    /**
     * @title 厂商详情
     * @description 接口说明
     * @author gyl
     * @url /Vendor/vendorDetail
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:int require:1 default: other: desc:厂商id
     *
     * @return id:厂商id
     * @return vendor_num:厂商编号
     * @return vendor_name:厂商名称
     * @return contact_name:联系人姓名
     * @return contact_tel:联系人电话
     * @return contact_mobile:联系人手机
     * @return contact_fax:联系人传真
     * @return contact_qq:联系人qq
     * @return contact_wechat:联系人微信
     * @return contact_email:联系人email
     * @return tag:标记是物流厂商还是供应商（0：厂商；1：供应商）
     * @return remark:备注
     * @return create_by:创建人
     * @return create_date:创建时间
     * @return modify_by:修改人
     * @return modify_date:修改时间
     * @return address:地址
     * @return settlement:结算方式
     *
     */
    public function vendorDetail(Request $request, VendorModel $vendorModel){
        $param = $request->param();
        $proTypeDetail = $vendorModel->where('id',$param['id'])->find();
        $this->jkReturn('0000','厂商详情',$proTypeDetail);
    }

    /**
     * @title 厂商删除
     * @description 接口说明
     * @author gyl
     * @url /Vendor/vendorDel
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:array require:0 default: other: desc:厂商id(批量删除)
     *
     */
    public function vendorDel(Request $request, VendorModel $vendorModel, VenProModel $venProModel){
        $param = $request->param();
        //多个删除
        foreach ($param['id'] as $v){
            /**
             * 查看厂商下是否有商品，否则不允许删除
             */
            $vendor = $vendorModel->where(['id'=>$v])->find();
            $venPro = $venProModel->where(['vendor_num'=>$vendor->vendor_num])->select();
            if (count($venPro) >= 1) {
                $this->jkReturn('-1004',"此厂商下还有商品，不允许删除,id为'$v'");
            }
            if(!$vendorModel->where(['id'=>$v])->delete()){
                $this->jkReturn('-1004',"删除失败,id为'$v'");
            }
            $this->setAdminUserLog("删除","删除厂商","wms_vendor",$v);
        }
        $this->jkReturn('0000','删除成功');
    }

    /**
     * @title 厂商信息导入模板下载
     * @description 接口说明
     * @author gyl
     * @url /Vendor/vendorDownload
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     */
    public function vendorDownload(SystemConfigModel $systemConfigModel){
        $code = 'base_url';
        $system = $systemConfigModel->where('code',$code)->find();
        $downUrl = $system->value . '/import/vendor.xlsx';
        $this->jkReturn('0000','厂商信息导入模板',$downUrl);
    }

    /**
     * @title 厂商信息导入
     * @description 接口说明
     * @author gyl
     * @url /Vendor/vendorImport
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     */
    public function vendorImport(VendorModel $vendorModel){
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
            $this->setAdminUserLog("导入","上传厂商信息","wms_vendor");
            for ($currentRow = 0; $currentRow <= $allRow; $currentRow++) {
                // 跳过第一行
                if ($currentRow == 0) {
                    $currentRow++;
                    continue;
                }
                //食恪sku
                $vendor_num = (string)$currentSheet->getCellByColumnAndRow(ord("A") - 65,$currentRow)->getValue();//厂商编号
                $vendor_name = addslashes((string)$currentSheet->getCellByColumnAndRow(ord("B") - 65, $currentRow)->getValue());//厂商名称
                $contact_name = (string)$currentSheet->getCellByColumnAndRow(ord("C") - 65, $currentRow)->getValue();//联系人姓名
                $contact_tel = (string)$currentSheet->getCellByColumnAndRow(ord("D") - 65, $currentRow)->getValue();//联系人电话
                $contact_mobile = (string)$currentSheet->getCellByColumnAndRow(ord("E") - 65, $currentRow)->getValue();//联系人手机
                $contact_fax = (string)$currentSheet->getCellByColumnAndRow(ord("F") - 65, $currentRow)->getValue();//联系人传真
                $contact_qq = (string)$currentSheet->getCellByColumnAndRow(ord("G") - 65, $currentRow)->getValue();//联系人qq
                $contact_wechat = (string)$currentSheet->getCellByColumnAndRow(ord("H") - 65, $currentRow)->getValue();//联系人微信
                $contact_email = (string)$currentSheet->getCellByColumnAndRow(ord("I") - 65, $currentRow)->getValue();//联系人email
                $tag = (string)$currentSheet->getCellByColumnAndRow(ord("J") - 65, $currentRow)->getValue();//标记是物流厂商还是供应商（0：厂商；1：供应商）
                $remark = (string)$currentSheet->getCellByColumnAndRow(ord("K") - 65, $currentRow)->getValue();//备注
                if (empty($vendor_name)) {
                    continue;
                }
                $product = array(
                    'vendor_num'=>$vendor_num,
                    'vendor_name'=>$vendor_name,
                    'contact_name'=>$contact_name,
                    'contact_tel'=>$contact_tel,
                    'contact_mobile'=>$contact_mobile,
                    'contact_fax'=>$contact_fax,
                    'contact_qq'=>$contact_qq,
                    'contact_wechat'=>$contact_wechat,
                    'contact_email'=>$contact_email,
                    'tag'=>$tag,
                    'remark'=>$remark,

                );
                $vendorModel->create($product);
                $a ++;
            }
            $msg = "导入成功</br>成功插入厂商：" . $a;
            $this->jkReturn('0000','厂商信息导入成功',$msg);
        }
    }


    /**
     * @title 商品列表
     * @description 接口说明
     * @author gyl
     * @url /Vendor/product
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:varchar require:0 default: other: desc:查询值
     *
     * @return product_num:商品编号
     * @return product_name:商品名称
     */
    public function product(Request $request,ProductModel $productModel){
        $param = $request->param();
        $where = "disabled = 1";
        if (!empty($param['search_key'])) {
            $where .= " and (product_num like '%" . $param['search_key'] . "%' or product_name like '%" . $param['search_key'] . "%') ";
        }
        $productList = $productModel->field('product_num,product_name')->where($where)->select();
        $this->jkReturn('0000','商品列表',$productList);
    }
    /**
     * @title 厂商绑定商品列表
     * @description 接口说明
     * @author gyl
     * @url /Vendor/venProduct
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:varchar require:0 default: other: desc:查询值
     * @param name:vendor_num type:int require:1 default: other: desc:厂商编号
     *
     * @return product_num:商品编号
     * @return product_name:商品名称
     * @return is_deultveor:是否默认供应商(1是/0否)
     * @return pro_state:商品状态
     * @return contract_type:合同类型(0经销/1代销/2联营)
     * @return v_price:正常进价
     * @return is_refund:是否可退货(0是/1否)
     */
    public function venProduct(Request $request, VenProModel $venProModel){
        $param = $request->param();
        $where = "v.vendor_num = '$param[vendor_num]'";
        $field = "v.*,p.product_num,p.product_name";
        if (!empty($param['search_key'])) {
            $where .= " and (p.product_num like '%" . $param['search_key'] . "%' or p.product_name like '%" . $param['search_key'] . "%') ";
        }
        $venProList = $venProModel
            ->field($field)
            ->alias('v')
            ->where($where)
            ->join('wms_product p','p.product_num = v.product_num','left')
            ->limit($param["limit"]??10)
            ->page($param['page']??1)
            ->select();
        $this->jkReturn('0000','厂商绑定商品列表',$venProList);
    }

    /**
     * @title 绑定商品
     * @description 接口说明
     * @author gyl
     * @url /Vendor/vendorGood
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
        //-- 开启事物
        $venProModel->startTrans();
        if (empty($param['vendorGood'])) {
            $this->jkReturn('-1004','数据为空');
        }
        foreach ($param['vendorGood'] as $k => &$v) {
            if (empty($param['vendorGood'][$k]['product_num']) or empty($param['vendorGood'][$k]['vendor_num'])) {
                $this->jkReturn('-1004','请填写商品编号或供应商编号');
            }
            unset($v['id']);
        }
        //-- 删除绑定商品
        $result = $venProModel->where(['vendor_num'=>$param['vendorGood'][0]['vendor_num']])->delete();
        if($result<0){
            $venProModel->rollback();
            $this->jkReturn('-1004','删除绑定商品失败');
        }
        if(!$venProModel->allowField(true)->saveAll($param['vendorGood'])){
            $venProModel->rollback();
            $this->jkReturn('-1004','绑定失败');
        }
        $this->setAdminUserLog("新增","绑定供应商","wms_ven_pro");
        $venProModel->commit();
        $this->jkReturn('0000','绑定成功');


    }
    /**
     * @title 厂商绑定商品模板下载
     * @description 接口说明
     * @author gyl
     * @url /Vendor/venProDownload
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     */
    public function venProDownload(SystemConfigModel $systemConfigModel){
        $code = 'base_url';
        $system = $systemConfigModel->where('code',$code)->find();
        $downUrl = $system->value . '/import/ven_pro.xlsx';
        $this->jkReturn('0000','厂商绑定商品模板',$downUrl);
    }

    /**
     * @title 厂商绑定商品导入
     * @description 接口说明
     * @author gyl
     * @url /Vendor/venProImport
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
            $this->setAdminUserLog("导入","厂商绑定商品","wms_ven_pro");
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
            $this->jkReturn('0000','厂商绑定商品导入成功',$msg);
        }
    }
}