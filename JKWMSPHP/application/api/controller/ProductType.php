<?php
namespace app\Api\controller;

/**
 * @title 商品类型管理
 * @description 接口说明
 * @group 商品管理
 * @header name:key require:1 default: desc:秘钥(区别设置)

 */
use app\api\model\ProductModel;
use app\api\model\ProductTypeModel;
use app\api\model\SystemConfigModel;
use app\api\service\UploadService;
use think\Request;

class ProductType extends Common
{
    protected $uploadService;
    /**
     * @title 商品类型列表
     * @description 接口说明
     * @author gyl
     * @url /ProductType/index
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:商品分类编号
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1other: desc:页数
     *
     * @return goodTypeList:商品类型列表@
     * @goodTypeList pro_type_code:商品分类编号 pro_type_name:商品分类名称 remark:备注 disabled:状态（1，有效0，无效)  create_by:创建人 create_date:创建时间 modify_by:修改人 modify_date:修改时间 parent_code:父级编号 depth:层级
     * @return goodTypeCount:商品分类数量
     */
    public function index(Request $request, ProductTypeModel $productTypeModel)
    {
        $param = $request->param();
        $where = "disabled = 1";
        if (!empty($param['search_key'])) {
            $where .= " and (pro_type_code like  '%" . $param['search_key'] . "%' or pro_type_name like '%" . $param['search_key'] . "%') ";
        }
        $goodList = $productTypeModel
            ->where($where)->limit($param["limit"]??10)->page($param['page']??1)->select();
        //echo $productTypeModel->getLastSql();die;
        $goodCount = $productTypeModel->where($where)->count();
        $data = array(
            'goodTypeList'=>$goodList,
            'goodTypeCount'=>$goodCount
        );
        $this->jkReturn('0000','商品类型列表',$data);
    }
    /**
     * @title 商品类型添加
     * @description 接口说明
     * @author gyl
     * @url /ProductType/productTypeAdd
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:pro_type_code type:string require:1 default: other: desc:商品分类编号
     * @param name:parent_code type:string require:1 default: other: desc:父级编号
     * @param name:pro_type_name type:string require:1 default: other: desc:商品分类名称
     * @param name:remark type:string require:0 default: other: desc:备注
     * @param name:user_name type:string require:0 default: other: desc:采购人员用户名
     *
     */
    public function productTypeAdd(Request $request, ProductTypeModel $productTypeModel){
        $param = $request->param();
        //$result = $productTypeModel->create($data);
        $result = $productTypeModel->allowField(true)->save($param);
        if($result){
            $product_type_id = $productTypeModel->getLastInsID();
            $this->setAdminUserLog("新增","添加商品类型","wms_product_type",$product_type_id);
            $this->jkReturn('0000','添加成功');
        }else{
            $this->jkReturn('-1004','添加失败');
        }
    }
    /**
     * @title 商品类型更新
     * @description 接口说明
     * @author gyl
     * @url /ProductType/productTypeEdit
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:pro_type_id type:int require:1 default: other: desc:商品分类id
     * @param name:pro_type_name type:string require:0 default: other: desc:商品分类名称
     * @param name:remark type:string require:0 default: other: desc:备注
     * @param name:user_name type:string require:0 default: other: desc:采购人员用户名
     *
     */
    public function productTypeEdit(Request $request, ProductTypeModel $productTypeModel){
        $param = $request->param();
        $proTypeDetail = $productTypeModel->where('id',$param['pro_type_id'])->select();
        if (count($proTypeDetail) <= 0) {
            $this->jkReturn('-1004','分类不存在');
        }
        $where = ['id'=>$param['pro_type_id']];
        $result = $productTypeModel->allowField(true)->save($param,$where);
        if($result){
            $this->setAdminUserLog("更新","更新商品类型","wms_product_type",$param['pro_type_id']);
            $this->jkReturn('0000','更新成功');
        }else{
            $this->jkReturn('-1004','更新失败');
        }
    }
    /**
     * @title 商品类型详情
     * @description 接口说明
     * @author gyl
     * @url /ProductType/proTypeDetail
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:pro_type_id type:int require:1 default: other: desc:商品分类id
     *
     * @return pro_type_code:商品分类编号
     * @return pro_type_name:商品分类名称
     * @return remark:备注
     * @return disabled:状态（1，有效     0，无效）
     * @return create_by:创建人
     * @return create_date:创建时间
     * @return modify_by:修改人
     * @return modify_date:修改时间
     * @return parent_code:父级编号
     * @return depth:层级
     * @return user_name:用户名
     *
     */
    public function proTypeDetail(Request $request, ProductTypeModel $productTypeModel){
        $param = $request->param();
        $proTypeDetail = $productTypeModel->where('id',$param['pro_type_id'])->find();
        $this->jkReturn('0000','商品类型详情',$proTypeDetail);
    }

    /**
     * @title 商品类型添加子类
     * @description 接口说明
     * @author gyl
     * @url /ProductType/proTypeChildAdd
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:pro_type_code type:string require:1 default: other: desc:商品分类编号
     * @param name:parent_code type:string require:1 default: other: desc:父级编号
     * @param name:pro_type_name type:string require:1 default: other: desc:商品分类名称
     * @param name:remark type:string require:0 default: other: desc:备注
     * @param name:create_date type:string require:0 default: other: desc:创建时间
     *
     */
    public function proTypeChildAdd(Request $request, ProductTypeModel $productTypeModel){
        $param = $request->param();
        $result = $productTypeModel->allowField(true)->create($param);
        if($result){
            $product_type_id = $productTypeModel->getLastInsID();
            $this->setAdminUserLog("新增","添加商品子类","wms_product_type",$product_type_id);
            $this->jkReturn('0000','添加成功');
        }else{
            $this->jkReturn(-1004,'添加失败');
        }
    }
    /**
     * @title 商品类型删除
     * @description 接口说明
     * @author gyl
     * @url /ProductType/proTypeDel
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:pro_type_id type:array require:0 default: other: desc:商品分类id(批量删除)
     *
     */
    public function proTypeDel(Request $request, ProductTypeModel $productTypeModel, ProductModel $productModel){
        $param = $request->param();
        //多个删除
        foreach ($param['pro_type_id'] as $v){
            /**
             * 查看此分类下是否有子类,否则不允许删除
             */
            $proType = $productTypeModel->where(['parent_code'=>$v])->select();
            if (count($proType) >= 1) {
                $this->jkReturn('-1004',"此类型还有子类，不允许删除,id为'$v'");
            }
            /**
             * 查看此分类下是否有商品，否则不允许删除
             */
            $proType1 = $productTypeModel->where(['id'=>$v])->find();
            $product = $productModel->where(['product_type'=>$proType1->pro_type_code])->select();
            if (count($product) >= 1) {
                $this->jkReturn('-1004',"此类型还有商品，不允许删除,id为'$v'");
            }
            if(!$productTypeModel->where(['id'=>$v])->delete()){
                $this->jkReturn('-1004',"删除失败,id为'$v'");
            }
            $this->setAdminUserLog("删除","删除商品类型","wms_product_type",$v);
        }
        $this->jkReturn('0000','删除成功');
    }

    /**
     * @title 商品类型信息导入模板下载
     * @description 接口说明
     * @author gyl
     * @url /ProductType/proTypeDownload
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     */
    public function proTypeDownload(SystemConfigModel $systemConfigModel){
        $code = 'base_url';
        $system = $systemConfigModel->where('code',$code)->find();
        $downUrl = $system->value . '/import/product_type.xlsx';
        $this->jkReturn('0000','商品类型信息导入模板',$downUrl);
    }

    /**
     * @title 商品类型信息导入
     * @description 接口说明
     * @author gyl
     * @url /ProductType/proTypeImport
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     */
    public function proTypeImport(ProductTypeModel $productTypeModel){
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
            $this->setAdminUserLog("导入","上传商品类型信息","wms_product_type");
            for ($currentRow = 0; $currentRow <= $allRow; $currentRow++) {
                // 跳过第一行
                if ($currentRow == 0) {
                    $currentRow++;
                    continue;
                }
                //食恪sku
                $pro_type_code = (string)$currentSheet->getCellByColumnAndRow(ord("A") - 65,$currentRow)->getValue();//商品类型编号
                $pro_type_name = addslashes((string)$currentSheet->getCellByColumnAndRow(ord("B") - 65, $currentRow)->getValue());//商品类型名称
                $parent_code = (string)$currentSheet->getCellByColumnAndRow(ord("C") - 65, $currentRow)->getValue();//父类编号
                $remark = (string)$currentSheet->getCellByColumnAndRow(ord("D") - 65, $currentRow)->getValue();//备注

                if (empty($pro_type_name)) {
                    continue;
                }
                $product = array(
                    'pro_type_code'=>$pro_type_code,
                    'pro_type_name'=>$pro_type_name,
                    'parent_code'=>$parent_code,
                    'remark'=>$remark,
                    'create_date'=>date('Y-m-d H:i:s')
                );
                $productTypeModel->create($product);
                $a ++;
            }
            $msg = "导入成功</br>成功插入商品类型：" . $a;
            $this->jkReturn('0000','商品类型导入成功',$msg);
        }
    }

    /**
     * @title 父类型列表
     * @description 接口说明
     * @author gyl
     * @url /ProductType/parentCode
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     *
     * @return id:商品分类id
     * @return pro_type_code:商品分类编号
     * @return pro_type_name:商品分类名称
     */
    public function parentCode(ProductTypeModel $productTypeModel)
    {
        $field = "id,pro_type_code,pro_type_name";
        $where = "disabled = 1 and parent_code = 0";
        $goodList = $productTypeModel->field($field)->where($where)->select();
        //echo $productTypeModel->getLastSql();die;
        $goodCount = $productTypeModel->where($where)->count();
        $data = array(
            'goodTypeList'=>$goodList,
            'goodTypeCount'=>$goodCount
        );
        $this->jkReturn('0000','商品类型列表',$data);
    }
}