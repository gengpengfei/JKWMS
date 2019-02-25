<?php
/**
 * Created by PhpStorm.
 * User: guanyl
 * Date: 2018/11/15
 * Time: 9:38
 */

namespace app\Api\controller;
use app\api\model\RowShelfModel;
use app\api\model\WLibraryModel;
use app\api\service\UploadService;
use app\api\model\SystemConfigModel;
use think\Request;

/**
 * @title 库位设置
 * @description 接口说明
 * @group 仓库管理
 * @header name:key require:1 default: desc:秘钥(区别设置)

 */

class WLibrary extends Common
{
    protected $uploadService;
    /**
     * @title 库位列表
     * @description 接口说明
     * @author gyl
     * @url /WLibrary/index
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:仓库编号、库区编号、库区名称
     * @param name:warea_num type:string require:0 default: other: desc:库区编号
     * @param name:warehouse_num type:string require:0 default: other: desc:仓库编号
     * @param name:shelf_id type:string require:0 default: other: desc:货架id
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1other: desc:页数
     *
     * @return wLibraryList:库位列表@
     * @wLibraryList warehouse_num:仓库编号 warehouse_name:仓库名称 warea_num:库区编号 warea_name:库区名称 warea_type:库区类型（0：收货区，1：存储区，2：次品区，3：拣货区） shelf_num:货架编号 wlibrary_num:库位编号 remark:备注 create_by:创建人 create_date:创建时间 modify_by:修改人 modify_date:修改时间
     * @return wLibraryCount:库位数量
     */

    public function index(Request $request, WLibraryModel $wLibraryModel){
        $param = $request->param();
        $where = "wl.disabled = 1";
        $field = "wl.*,r.shelf_num,w.warehouse_num,w.warehouse_name,a.warea_num,a.warea_name,a.warea_type";
        if (!empty($param['search_key'])) {
            $where .= " and (a.warea_num like '%" . $param['search_key'] . "%' or a.warea_name like '%" . $param['search_key'] . "%' or w.warehouse_num like '%" . $param['search_key'] . "%' or r.shelf_num like '%" . $param['search_key'] . "%' or wl.wlibrary_num like '%" . $param['search_key'] . "%') ";
        }
        if (!empty($param['warea_num'])) {
            $where .= " and a.warea_num = '$param[warea_num]'";
        }
        if (!empty($param['warehouse_num'])) {
            $where .= " and a.warehouse_num = '$param[warehouse_num]'";
        }
        if (!empty($param['shelf_id'])) {
            $where .= " and wl.shelf_id = '$param[shelf_id]'";
        }
        $wLibraryList = $wLibraryModel
            ->field($field)
            ->alias('wl')
            ->where($where)
            ->join('wms_row_shelf r','wl.shelf_id = r.id','left')
            ->join('wms_warehouse_area a','r.warea_num = a.warea_num','left')
            ->join('wms_warehouse w','w.warehouse_num = a.warehouse_num','left')
            ->limit($param["limit"]??10)
            ->page($param['page']??1)
            ->select();
        $wLibraryCount = $wLibraryModel
            ->alias('wl')
            ->where($where)
            ->join('wms_row_shelf r','wl.shelf_id = r.id','left')
            ->join('wms_warehouse_area a','r.warea_num = a.warea_num','left')
            ->join('wms_warehouse w','w.warehouse_num = a.warehouse_num','left')
            ->count();
        $data = array(
            'wLibraryList'=>$wLibraryList,
            'wLibraryCount'=>$wLibraryCount
        );
        $this->jkReturn('0000','库位列表',$data);
    }
    /**
     * @title 货架列表
     * @description 接口说明
     * @author gyl
     * @url /WLibrary/rowShelf
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     * @param name:shelf_id type:int require:1 default: other: desc:货架id
     *
     * @return warea_num:库区编号
     * @return shelf_num:货架编号
     * @return id:货架id
     */
    public function rowShelf(Request $request, RowShelfModel $rowShelfModel){
        $param = $request->param();
        $rowShelfList = array();
        if (!empty($param['warea_num'])) {
            $where = "disabled = 1 and warea_num = '$param[warea_num]'";
            $rowShelfList = $rowShelfModel->field("id,warea_num,shelf_num")->where($where)->select();
        }
        $this->jkReturn('0000','货架列表',$rowShelfList);
    }

    /**
     * @title 库位详情
     * @description 接口说明
     * @author gyl
     * @url /WLibrary/wLibraryDetail
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:int require:1 default: other: desc:库位id
     *
     * @return id:库位id
     * @return warehouse_num:仓库编号
     * @return warehouse_name:仓库名称
     * @return warea_num:库区编号
     * @return warea_name:库区名称
     * @return warea_type:库区类型（0：收货区，1：存储区，2：次品区，3：拣货区）
     * @return shelf_id:货架id
     * @return wlibrary_num:库位编号
     * @return is_temporary:是否为临时库位
     * @return is_th_storage:是否第三方仓储
     * @return logistics_mode:物流方式（0：常温，1：冷藏，2:冷冻）
     * @return unit:计量单位
     * @return stock_num_down:库存下限
     * @return stock_num_up:库存上限
     * @return remark:备注
     * @return create_by:创建人
     * @return create_date:创建时间
     * @return modify_by:修改人
     * @return modify_date:修改时间
     *
     */
    public function wLibraryDetail(Request $request, WLibraryModel $wLibraryModel){
        $param = $request->param();
        $where = "wl.disabled = 1 and wl.id = " . $param['id'];
        $field = "wl.*,r.shelf_num,w.warehouse_num,w.warehouse_name,a.warea_num,a.warea_name";
        $wLibraryList = $wLibraryModel
            ->field($field)
            ->alias('wl')
            ->where($where)
            ->join('wms_row_shelf r','wl.shelf_id = r.id','left')
            ->join('wms_warehouse_area a','r.warea_num = a.warea_num','left')
            ->join('wms_warehouse w','w.warehouse_num = a.warehouse_num','left')
            ->find();
        $this->jkReturn('0000','库位详情',$wLibraryList);
    }

    /**
     * @title 库位添加
     * @description 接口说明
     * @author gyl
     * @url /WLibrary/wLibraryAdd
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:warehouse_num type:string require:1 default: other: desc:仓库编号
     * @param name:warehouse_name type:string require:1 default: other: desc:仓库名称
     * @param name:warea_num type:string require:1 default: other: desc:库区编号
     * @param name:warea_name type:string require:1 default: other: desc:库区名称
     * @param name:shelf_num type:string require:1 default: other: desc:货架编号
     * @param name:wlibrary_num type:string require:1 default: other: desc:库位编号
     * @param name:is_temporary type:string require:1 default: other: desc:是否为临时库位
     * @param name:is_th_storage type:string require:1 default: other: desc:是否第三方仓储
     * @param name:logistics_mode type:string require:1 default: other: desc:物流方式（常温，冷藏，冷冻）
     * @param name:unit type:string require:1 default: other: desc:计量单位
     * @param name:stock_num_down type:string require:1 default: other: desc:库存下限
     * @param name:stock_num_up type:string require:1 default: other: desc:库存上限
     * @param name:remark type:string require:0 default: other: desc:备注
     * @param name:create_by type:string require:0 default: other: desc:创建人
     * @param name:create_date type:string require:0 default: other: desc:创建时间
     * @param name:modify_by type:string require:0 default: other: desc:修改人
     * @param name:modify_date type:string require:0 default: other: desc:修改时间
     *
     */
    public function wLibraryAdd(Request $request, WLibraryModel $wLibraryModel){
        $param = $request->param();
        $result = $wLibraryModel->allowField(true)->save($param);
        if($result){
            $wLibraryId = $wLibraryModel->getLastInsID();
            $this->setAdminUserLog("新增","添加库位","wms_wlibrary",$wLibraryId);
            $this->jkReturn('0000','添加成功');
        }else{
            $this->jkReturn('-1004','添加失败');
        }
    }

    /**
     * @title 库位修改
     * @description 接口说明
     * @author gyl
     * @url /WLibrary/wLibraryEdit
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:string require:1 default: other: desc:库位id
     * @param name:warehouse_num type:string require:1 default: other: desc:仓库编号
     * @param name:warehouse_name type:string require:1 default: other: desc:仓库名称
     * @param name:warea_num type:string require:1 default: other: desc:库区编号
     * @param name:warea_name type:string require:1 default: other: desc:库区名称
     * @param name:shelf_id type:int require:1 default: other: desc:货架id
     * @param name:wlibrary_num type:string require:1 default: other: desc:库位编号
     * @param name:is_temporary type:string require:1 default: other: desc:是否为临时库位
     * @param name:is_th_storage type:string require:1 default: other: desc:是否第三方仓储
     * @param name:logistics_mode type:string require:1 default: other: desc:物流方式（常温，冷藏，冷冻）
     * @param name:unit type:string require:1 default: other: desc:计量单位
     * @param name:stock_num_down type:string require:1 default: other: desc:库存下限
     * @param name:stock_num_up type:string require:1 default: other: desc:库存上限
     * @param name:remark type:string require:0 default: other: desc:备注
     * @param name:create_by type:string require:0 default: other: desc:创建人
     * @param name:create_date type:string require:0 default: other: desc:创建时间
     * @param name:modify_by type:string require:0 default: other: desc:修改人
     * @param name:modify_date type:string require:0 default: other: desc:修改时间
     *
     */
    public function wLibraryEdit(Request $request, WLibraryModel $wLibraryModel){
        $param = $request->param();
        $wLibrary = $wLibraryModel->where('id',$param['id'])->select();
        if (count($wLibrary) <= 0) {
            $this->jkReturn('-1004','库位不存在');
        }
        $where = ['id'=>$param['id']];
        $result = $wLibraryModel->allowField(true)->save($param,$where);
        if($result){
            $this->setAdminUserLog("更新","更新库位信息","wms_wlibrary",$param['id']);
            $this->jkReturn('0000','更新成功');
        }else{
            $this->jkReturn('-1004','更新失败');
        }
    }

    /**
     * @title 库位删除
     * @description 接口说明
     * @author gyl
     * @url /WLibrary/wLibraryDel
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:array require:0 default: other: desc:库位id(批量删除)
     *
     */
    public function wLibraryDel(Request $request, WLibraryModel $wLibraryModel){
        $param = $request->param();
        //多个删除
        foreach ($param['id'] as $v){
            if(!$wLibraryModel->where(['id'=>$v])->delete()){
                $this->jkReturn('-1004',"删除失败,id为'$v'");
            }
            $this->setAdminUserLog("删除","删除库位","wms_wlibrary",$v);
        }
        $this->jkReturn('0000','删除成功');
    }

    /**
     * @title 库位信息导入模板下载
     * @description 接口说明
     * @author gyl
     * @url /wLibrary/wLibraryDownload
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     */
    public function wLibraryDownload(SystemConfigModel $systemConfigModel){
        $code = 'base_url';
        $system = $systemConfigModel->where('code',$code)->find();
        $downUrl = $system->value . '/import/wLibrary.xlsx';
        $this->jkReturn('0000','库位信息导入模板',$downUrl);
    }
    /**
     * @title 库位信息导入
     * @description 接口说明
     * @author gyl
     * @url /WLibrary/wLibraryImport
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     */
    public function wLibraryImport(WLibraryModel $wLibraryModel){
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
                $shelf_id = (string)$currentSheet->getCellByColumnAndRow(ord("A") - 65,$currentRow)->getValue();//货架id
                $wlibrary_num = (string)$currentSheet->getCellByColumnAndRow(ord("C") - 65, $currentRow)->getValue();//库位编号
                $is_temporary = (string)$currentSheet->getCellByColumnAndRow(ord("C") - 65, $currentRow)->getValue();//是否为临时库位
                $is_th_storage = (string)$currentSheet->getCellByColumnAndRow(ord("C") - 65, $currentRow)->getValue();//是否第三方仓储
                $logistics_mode = (string)$currentSheet->getCellByColumnAndRow(ord("C") - 65, $currentRow)->getValue();//物流方式（常温，冷藏，冷冻）
                $unit = (string)$currentSheet->getCellByColumnAndRow(ord("C") - 65, $currentRow)->getValue();//计量单位
                $remark = (string)$currentSheet->getCellByColumnAndRow(ord("D") - 65, $currentRow)->getValue();//备注
                if (empty($shelf_id)) {
                    continue;
                }
                $temporary = '0';
                if ($is_temporary == '是') {
                    $temporary = '1';
                }
                $th_storage = '0';
                if ($is_th_storage == '是') {
                    $th_storage = '1';
                }
                $logistics = '0';
                if ($logistics_mode == '冷藏') {
                    $logistics = '1';
                }
                if ($logistics_mode == '冷冻') {
                    $logistics = '2';
                }
                $wLibrary = array(
                    'shelf_id'=>$shelf_id,
                    'wlibrary_num'=>$wlibrary_num,
                    'is_temporary'=>$temporary,
                    'is_th_storage'=>$th_storage,
                    'logistics_mode'=>$logistics,
                    'unit'=>$unit,
                    'remark'=>$remark
                );
                $wLibraryModel->create($wLibrary);
                $a ++;
            }
            $msg = "导入成功</br>成功插入库位信息：" . $a;
            $this->jkReturn('0000','库位信息导入成功',$msg);
        }
    }
}