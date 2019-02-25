<?php
/**
 * Created by PhpStorm.
 * User: guanyl
 * Date: 2018/11/13
 * Time: 17:47
 */

namespace app\Api\controller;
use app\api\model\RowShelfModel;
use app\api\model\SystemConfigModel;
use app\api\model\WarehouseModel;
use app\api\model\WLibraryModel;
use app\api\service\UploadService;
use think\Request;

/**
 * @title 货架设置
 * @description 接口说明
 * @group 仓库管理
 * @header name:key require:1 default: desc:秘钥(区别设置)

 */

class RowShelf extends Common
{
    protected $uploadService;
    /**
     * @title 货架列表
     * @description 接口说明
     * @author gyl
     * @url /RowShelf/index
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:仓库编号、库区编号、库区名称
     * @param name:warea_num type:string require:0 default: other: desc:库区编号
     * @param name:warehouse_num type:string require:0 default: other: desc:仓库编号

     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1other: desc:页数
     *
     * @return rowShelfList:货架列表@
     * @rowShelfList warehouse_num:仓库编号 warehouse_name:仓库名称 warea_num:库区编号 warea_name:库区名称 warea_type:库区类型（0：收货区，1：存储区，2：次品区，3：拣货区） shelf_num:货架编号 remark:备注 create_by:创建人 create_date:创建时间 modify_by:修改人 modify_date:修改时间
     * @return rowShelfCount:货架数量
     */
    public function index(Request $request, RowShelfModel $rowShelfModel){
        $param = $request->param();
        $where = "r.disabled = 1";
        $field = "r.*,w.warehouse_num,w.warehouse_name,a.warea_num,a.warea_name,a.warea_type";
        if (!empty($param['search_key'])) {
            $where .= " and (a.warea_num like '%" . $param['search_key'] . "%' or a.warea_name like '%" . $param['search_key'] . "%' or w.warehouse_num like '%" . $param['search_key'] . "%' or r.shelf_num like '%" . $param['search_key'] . "%') ";
        }
        if (!empty($param['warehouse_num'])) {
            $where .= " and a.warehouse_num = '$param[warehouse_num]'";
        }
        if (!empty($param['warea_num'])) {
            $where .= " and a.warea_num = '$param[warea_num]'";
        }
        $rowShelfList = $rowShelfModel
            ->field($field)
            ->alias('r')
            ->where($where)
            ->join('wms_warehouse_area a','r.warea_num = a.warea_num','left')
            ->join('wms_warehouse w','w.warehouse_num = a.warehouse_num','left')
            ->limit($param["limit"]??10)
            ->page($param['page']??1)
            ->select();
        $rowShelfCount = $rowShelfModel
            ->alias('r')
            ->where($where)
            ->join('wms_warehouse_area a','r.warea_num = a.warea_num','left')
            ->join('wms_warehouse w','w.warehouse_num = a.warehouse_num','left')
            ->count();
        $data = array(
            'rowShelfList'=>$rowShelfList,
            'rowShelfCount'=>$rowShelfCount
        );
        $this->jkReturn('0000','货架列表',$data);
    }

    /**
     * @title 仓库列表
     * @description 接口说明
     * @author gyl
     * @url /RowShelf/warehouse
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
     * @title 库区列表
     * @description 接口说明
     * @author gyl
     * @url /RowShelf/warehouseArea
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     * @param name:warehouse_num type:int require:1 default: other: desc:仓库编号
     *
     * @return warea_num:库区编号
     * @return warea_name:库区名称
     */
    public function warehouseArea(Request $request, WarehouseModel $warehouseModel){
        $param = $request->param();
        $warehouseList = array();
        if (!empty($param['warehouse_num'])) {
            $where = "disabled = 1 and warehouse_num = '$param[warehouse_num]'";
            $warehouseList = $warehouseModel->field("id,warea_num,warea_name")->where($where)->select();
        }
        $this->jkReturn('0000','库区列表',$warehouseList);
    }

    /**
     * @title 货架详情
     * @description 接口说明
     * @author gyl
     * @url /RowShelf/rowShelfDetail
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:int require:1 default: other: desc:货架id
     *
     * @return id:货架id
     * @return warehouse_num:仓库编号
     * @return warehouse_name:仓库名称
     * @return warea_num:库区编号
     * @return warea_name:库区名称
     * @return shelf_num:货架编号
     * @return remark:备注
     * @return create_by:创建人
     * @return create_date:创建时间
     * @return modify_by:修改人
     * @return modify_date:修改时间
     *
     */
    public function rowShelfDetail(Request $request, RowShelfModel $rowShelfModel){
        $param = $request->param();
        $where = "r.disabled = 1 and r.id = " . $param['id'];
        $field = "r.*,w.warehouse_num,w.warehouse_name,a.warea_num,a.warea_name";
        $rowShelfList = $rowShelfModel
            ->field($field)
            ->alias('r')
            ->where($where)
            ->join('wms_warehouse_area a','r.warea_num = a.warea_num','left')
            ->join('wms_warehouse w','w.warehouse_num = a.warehouse_num','left')
            ->find();
        $this->jkReturn('0000','货架详情',$rowShelfList);
    }
    /**
     * @title 货架添加
     * @description 接口说明
     * @author gyl
     * @url /RowShelf/rowShelfAdd
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:warehouse_num type:string require:1 default: other: desc:仓库编号
     * @param name:warehouse_name type:string require:1 default: other: desc:仓库名称
     * @param name:warea_num type:string require:1 default: other: desc:库区编号
     * @param name:warea_name type:string require:1 default: other: desc:库区名称
     * @param name:shelf_num type:string require:1 default: other: desc:货架编号
     * @param name:remark type:string require:0 default: other: desc:备注
     * @param name:create_by type:string require:0 default: other: desc:创建人
     * @param name:create_date type:string require:0 default: other: desc:创建时间
     * @param name:modify_by type:string require:0 default: other: desc:修改人
     * @param name:modify_date type:string require:0 default: other: desc:修改时间
     *
     */
    public function rowShelfAdd(Request $request, RowShelfModel $rowShelfModel){
        $param = $request->param();
        $result = $rowShelfModel->allowField(true)->save($param);
        if($result){
            $rowShelfId = $rowShelfModel->getLastInsID();
            $this->setAdminUserLog("新增","添加货架","wms_row_shelf",$rowShelfId);
            $this->jkReturn('0000','添加成功');
        }else{
            $this->jkReturn('-1004','添加失败');
        }
    }
    /**
     * @title 货架修改
     * @description 接口说明
     * @author gyl
     * @url /RowShelf/rowShelfEdit
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:warehouse_num type:string require:1 default: other: desc:仓库编号
     * @param name:warehouse_name type:string require:1 default: other: desc:仓库名称
     * @param name:warea_num type:string require:1 default: other: desc:库区编号
     * @param name:warea_name type:string require:1 default: other: desc:库区名称
     * @param name:shelf_num type:string require:1 default: other: desc:货架编号
     * @param name:remark type:string require:0 default: other: desc:备注
     * @param name:create_by type:string require:0 default: other: desc:创建人
     * @param name:create_date type:string require:0 default: other: desc:创建时间
     * @param name:modify_by type:string require:0 default: other: desc:修改人
     * @param name:modify_date type:string require:0 default: other: desc:修改时间
     *
     */
    public function rowShelfEdit(Request $request, RowShelfModel $rowShelfModel){
        $param = $request->param();
        $rowShelf = $rowShelfModel->where('id',$param['id'])->select();
        if (count($rowShelf) <= 0) {
            $this->jkReturn('-1004','货架不存在');
        }
        $where = ['id'=>$param['id']];
        $result = $rowShelfModel->allowField(true)->save($param,$where);
        if($result){
            $this->setAdminUserLog("更新","更新货架信息","wms_row_shelf",$param['id']);
            $this->jkReturn('0000','更新成功');
        }else{
            $this->jkReturn('-1004','更新失败');
        }
    }

    /**
     * @title 货架删除
     * @description 接口说明
     * @author gyl
     * @url /RowShelf/rowShelfDel
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:array require:0 default: other: desc:货架id(批量删除)
     *
     */
    public function rowShelfDel(Request $request, RowShelfModel $rowShelfModel, WLibraryModel $wLibraryModel){
        $param = $request->param();
        //多个删除
        foreach ($param['id'] as $v){
            /**
             * 查看货架下是否有库位，否则不允许删除
             */
            $warehouse = $rowShelfModel->where(['id'=>$v])->find();
            $wLibrary = $wLibraryModel->where(['shelf_id'=>$warehouse->id])->select();
            if (count($wLibrary) >= 1) {
                $this->jkReturn('-1004',"此货架下还有库位，不允许删除,id为'$v'");
            }
            if(!$rowShelfModel->where(['id'=>$v])->delete()){
                $this->jkReturn('-1004',"删除失败,id为'$v'");
            }
            $this->setAdminUserLog("删除","删除货架","wms_row_shelf",$v);
        }
        $this->jkReturn('0000','删除成功');
    }
    /**
     * @title 货架信息导入模板下载
     * @description 接口说明
     * @author gyl
     * @url /RowShelf/rowShelfDownload
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     */
    public function rowShelfDownload(SystemConfigModel $systemConfigModel){
        $code = 'base_url';
        $system = $systemConfigModel->where('code',$code)->find();
        $downUrl = $system->value . '/import/rowShelf.xlsx';
        $this->jkReturn('0000','货架信息导入模板',$downUrl);
    }

    /**
     * @title 货架信息导入
     * @description 接口说明
     * @author gyl
     * @url /RowShelf/rowShelfImport
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     */
    public function rowShelfImport(RowShelfModel $rowShelfModel){
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
            $this->setAdminUserLog("导入","货架信息","wms_row_shelf");
            for ($currentRow = 0; $currentRow <= $allRow; $currentRow++) {
                // 跳过第一行
                if ($currentRow == 0) {
                    $currentRow++;
                    continue;
                }
                //食恪sku
                $warea_num = (string)$currentSheet->getCellByColumnAndRow(ord("A") - 65,$currentRow)->getValue();//库区编号
                $shelf_num = (string)$currentSheet->getCellByColumnAndRow(ord("C") - 65, $currentRow)->getValue();//货架编号
                $remark = (string)$currentSheet->getCellByColumnAndRow(ord("D") - 65, $currentRow)->getValue();//备注
                if (empty($warea_num)) {
                    continue;
                }
                $rowShelf = array(
                    'warea_num'=>$warea_num,
                    'shelf_num'=>$shelf_num,
                    'remark'=>$remark
                );
                $rowShelfModel->create($rowShelf);
                $a ++;
            }
            $msg = "导入成功</br>成功插入货架信息：" . $a;
            $this->jkReturn('0000','货架信息导入成功',$msg);
        }
    }

}