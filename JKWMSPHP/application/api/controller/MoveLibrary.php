<?php
/**
 * Created by PhpStorm.
 * User: guanyl
 * Date: 2018/12/5
 * Time: 9:55
 */

namespace app\Api\controller;
use app\api\model\MoveLibraryInfoModel;
use app\api\model\MoveLibraryModel;
use app\api\model\QualityCheckInfoModel;
use app\api\model\QualityCheckModel;
use app\api\model\WarehousingInfoModel;
use app\api\model\WarehousingModel;
use think\Request;

/**
 * @title 移库管理
 * @description 接口说明
 * @group 单据管理
 * @header name:key require:1 default: desc:秘钥(区别设置)

 */
class MoveLibrary extends Common
{
    /**
     * @title 移库单列表
     * @description 接口说明
     * @author gyl
     * @url /MoveLibrary/libraryList
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:移库单编号
     * @param name:wh_id type:string require:0 default: other: desc:仓库id
     * @param name:start_date type:int require:0 default: 1 other: desc:搜索开始日期
     * @param name:end_date type:int require:0 default: 1 other: desc:搜索结束日期
     * @param name:move_status type:int require:0 default: 1 other: desc:移库状态(0:待移库，1:已移库)
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1other: desc:页数
     *
     * @return libraryList:移库单列表@
     * @checkList move_library_num:移库单编号 check_warehouse_name:质检仓库 warehouse_name:移入仓库 warea_name:移入库区 shelf_num:移入货架 remark:备注 create_by:创建人 create_date：创建时间 move_status:移库状态(0:待移库，1:已移库)
     * @return libraryList:移库单数量
     */
    public function libraryList(Request $request, MoveLibraryModel $moveLibraryModel){
        $param = $request->param();
        $where = "m.disabled = 1";
        $field = "m.id,m.move_library_num,m.check_wh_id,w.warehouse_name as check_warehouse_name,m.wh_id,h.warehouse_name,m.waerea_id,a.warea_name,r.shelf_num,m.shelf_id,m.remark,m.create_by,m.create_date,m.move_status";
        if (!empty($param['search_key'])) {
            $where .= " and m.move_library_num like '%" . $param['search_key'] . "%'";
        }
        if (!empty($param['start_date']) && !empty($param['end_date'])) {
            $where .= " and date_format(m.create_date,'%Y-%m-%d') >= date_format('" . $param['start_date'] . "','%Y-%m-%d')";
            $where .= " and date_format(m.create_date,'%Y-%m-%d') <= date_format('" . $param['end_date'] . "','%Y-%m-%d')";
        }
        if (!empty($param['move_status'])) {
            $where .= " and m.move_status = $param[move_status]";
        }
        $checkList = $moveLibraryModel
            ->field($field)
            ->alias('m')
            ->where($where)
            ->join('wms_warehouse w','w.id = m.check_wh_id','left')
            ->join('wms_warehouse h','h.id = m.wh_id','left')
            ->join('wms_warehouse_area a','a.id = m.waerea_id','left')
            ->join('wms_row_shelf r','r.id = m.shelf_id','left')
            ->limit($param["limit"]??10)
            ->page($param['page']??1)
            ->select();
        $checkCount = $moveLibraryModel
            ->alias('m')
            ->where($where)
            ->join('wms_warehouse w','w.id = m.check_wh_id','left')
            ->join('wms_warehouse h','h.id = m.wh_id','left')
            ->join('wms_warehouse_area a','a.id = m.waerea_id','left')
            ->join('wms_row_shelf r','r.id = m.shelf_id','left')
            ->count();
        $data = array(
            'checkList'=>$checkList,
            'checkCount'=>$checkCount
        );
        $this->jkReturn('0000','移库单列表',$data);
    }

    /**
     * @title 移库单详情
     * @description 接口说明
     * @author gyl
     * @url /MoveLibrary/libraryDetail
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     * @param name:id type:int require:1 default: other: desc:移库单id
     *
     * @return move_library_num:移库单编号
     * @return check_warehouse_name:质检仓库
     * @return warehouse_name:移入仓库
     * @return wh_id:质检仓库id
     * @return check_wh_id:质检库区id
     * @return create_by:创建人
     * @return create_date:创建时间
     * @return remark:备注
     * @return move_status:移库状态(0:待移库，1:已移库)
     * @return reviewed_status:审核状态
     * @return product_info:移库商品信息@
     * @product_info product_num:商品编号 product_name:商品名称 bar_code:商品条形码 vendor_name:供应商名称 price:进价 unit:单位 pro_create_date:生产日期 shelf_month:保质期 specifications:规格 carton:箱规 input_tax:税率 batch_code:批次号 wait_storage_num:实际移库数量 waerea_id:移入库区 shelf_id:移入货架 library_id:移入库位
     *
     */
    public function libraryDetail(Request $request, MoveLibraryModel $moveLibraryModel, MoveLibraryInfoModel $libraryInfoModel){
        $param = $request->param();
        $field = "m.id,m.move_library_num,m.check_wh_id,w.warehouse_name as check_warehouse_name,m.wh_id,h.warehouse_name,m.waerea_id,a.warea_name,r.shelf_num,m.shelf_id,m.remark,m.create_by,m.create_date,m.move_status";
        $moveLibrary = $moveLibraryModel
            ->field($field)
            ->alias('m')
            ->where('m.id',$param['id'])
            ->join('wms_warehouse w','w.id = m.check_wh_id','left')
            ->join('wms_warehouse h','h.id = m.wh_id','left')
            ->join('wms_warehouse_area a','a.id = m.waerea_id','left')
            ->join('wms_row_shelf r','r.id = m.shelf_id','left')
            ->find();
        $moveLibrary['product_info'] = $libraryInfoModel
            ->field('pp.*,p.bar_code,pp.wh_id,w.warehouse_name,pp.waerea_id,a.warea_name,r.shelf_num,pp.shelf_id,pp.library_id,l.wlibrary_num')
            ->alias('pp')
            ->where('move_library_num',$moveLibrary['move_library_num'])
            ->join('wms_product_info p','p.product_num = pp.product_num','left')
            ->join('wms_warehouse w','w.id = pp.wh_id','left')
            ->join('wms_warehouse_area a','a.id = pp.waerea_id','left')
            ->join('wms_row_shelf r','r.id = pp.shelf_id','left')
            ->join('wms_wlibrary l','l.id = pp.library_id','left')
            ->select();
        $this->jkReturn('0000','移库单详情',$moveLibrary);
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
     * @return wait_storage_num:待移库数量
     */
    public function productList(Request $request,QualityCheckInfoModel $checkInfoModel){
        $param = $request->param();
        if (empty($param['wh_id']) || empty($param['waerea_id'])) {
            $this->jkReturn('-1004','请先选择仓库和库区！');
        }
        $where = "p.disabled = 1 and p.wh_id = '$param[wh_id]' and p.waerea_id = '$param[waerea_id]'";
        $field = "p.id,p.product_num,p.product_name,i.bar_code,p.price,p.specifications,p.unit,p.shelf_month,p.pro_create_date,p.carton,p.input_tax,p.wait_storage_num";
        if (!empty($param['search_key'])) {
            $where .= " and (p.product_num like '%" . $param['search_key'] . "%' or p.product_name like '%" . $param['search_key'] . "%') ";
        }
        $productList = $checkInfoModel
            ->field($field)
            ->alias('p')
            ->where($where)
            ->join('wms_product_info i','p.product_num = i.product_num','left')
            ->select();
        $this->jkReturn('0000','商品列表',$productList);
    }

    /**
     * @title 移库单新建
     * @description 接口说明
     * @author gyl
     * @url /MoveLibrary/libraryAdd
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:create_by type:string require:1 default: other: desc:创建人
     * @param name:create_date type:string require:1 default: other: desc:创建时间
     * @param name:remark type:string require:1 default: other: desc:备注
     * @param name:pur_product type:array require:1 default: other: desc:待移库商品信息
     * @param name:id type:int require:1 default: other: desc:质检商品id
     * @param name:real_arrival_num type:string require:1 default: other: desc:实际移库数量
     * @param name:waerea_id type:string require:1 default: other: desc:移入库区
     * @param name:shelf_id type:string require:1 default: other: desc:移入货架
     * @param name:library_id type:string require:1 default: other: desc:移入库位
     *
     */
    public function libraryAdd(Request $request,MoveLibraryModel $moveLibraryModel, QualityCheckInfoModel $checkInfoModel, MoveLibraryInfoModel $libraryInfoModel){
        $param = $request->param();
        /*$param = array(
            'create_by'=>'admin',
            'remark'=>'移库单新建',
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
        //待移库商品信息
        if (!empty($param['pur_product'])) {
            foreach ($param['pur_product'] as &$value) {
                $param['waerea_id'] = $value['waerea_id'];//库区
            }
        }
        //生成移库单编号
        $date = date('Y-m-d');
        $where = "date_format(create_date,'%Y-%m-%d') = date_format('" . $date . "','%Y-%m-%d')";
        $check = $moveLibraryModel->where($where)->count();
        $number = $this->number($check);
        $param['move_library_num'] = 'YK-' . date('Ymd') . '-' . $number;//移库单编号
        //保存移库单数据
        $result = $moveLibraryModel->allowField(true)->save($param);
        if($result){
            //获取移库单ID
            $moveLibraryId = $moveLibraryModel->getLastInsID();
            //保存操作日志
            $this->setAdminUserLog("新增","移库单新建","wms_move_library",$moveLibraryId);
            //添加移库单详细信息
            $wh_id = '0';
            foreach ($param['pur_product'] as $product) {
                $checkInfo = $checkInfoModel->where(['id'=>$product['id']])->find();
                $wh_id = $checkInfo['wh_id'];
                $data = array(
                    'vendor'=>$checkInfo['vendor'],
                    'product_num'=>$checkInfo['product_num'],
                    'product_name'=>$checkInfo['product_name'],
                    'unit'=>$checkInfo['unit'],
                    'price'=>$checkInfo['price'],
                    'sale_price'=>$checkInfo['sale_price'],
                    'shelf_month'=>$checkInfo['shelf_month'],
                    'pro_create_date'=>$checkInfo['pro_create_date'],
                    'wait_storage_num'=>$checkInfo['wait_storage_num'],
                    'specifications'=>$checkInfo['specifications'],
                    'carton'=>$checkInfo['carton'],
                    'batch_code'=>$checkInfo['batch_code'],
                    'wh_id'=>$checkInfo['wh_id'],//仓库
                    'waerea_id' => $product['waerea_id'],//库区
                    'shelf_id' => $product['shelf_id'],//货架
                    'library_id' => $product['library_id'],//库位
                    'real_arrival_num' => $product['real_arrival_num'],//实际移库数量
                    'move_library_num' => $param['move_library_num']
                );
                $libraryInfoModel->create($data);

            }
            $moveLibraryModel->allowField(true)->save(['wh_id'=>$wh_id],['id'=>$moveLibraryId]);
            $this->jkReturn('0000','添加成功');
        }else{
            $this->jkReturn('-1004','添加失败');
        }
    }

    /**
     * @title 移库单更新
     * @description 接口说明
     * @author gyl
     * @url /MoveLibrary/libraryEdit
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:string require:0 default: 1 1other: desc:移库单id
     * @param name:remark type:string require:1 default: other: desc:备注
     * @param name:pur_product type:array require:1 default: other: desc:待移库商品信息
     * @param name:id type:int require:1 default: other: desc:移库商品id
     * @param name:real_arrival_num type:string require:1 default: other: desc:实际移库数量
     * @param name:waerea_id type:string require:1 default: other: desc:移入库区
     * @param name:shelf_id type:string require:1 default: other: desc:移入货架
     * @param name:library_id type:string require:1 default: other: desc:移入库位
     *
     */
    public function libraryEdit(Request $request, MoveLibraryModel $moveLibraryModel,MoveLibraryInfoModel $libraryInfoModel){
        $param = $request->param();
        /*$param = array(
                   'id'=>1,
                   'remark'=>'移库单更新',
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
        $result = $moveLibraryModel->allowField(true)->save($param, $where);
        if($result){
            $this->setAdminUserLog("更新","移库单更新","wms_move_library",$param['id']);
            $libraryInfoModel->allowField(true)->saveAll($param['pur_product']);
            $this->jkReturn('0000','更新成功');
        }else{
            $this->jkReturn('-1004','更新失败');
        }
    }

    /**
     * @title 移库单删除
     * @description 接口说明
     * @author gyl
     * @url /MoveLibrary/libraryDel
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:int require:0 default: other: desc:移库单id
     *
     */
    public function libraryDel(Request $request, MoveLibraryModel $moveLibraryModel,MoveLibraryInfoModel $libraryInfoModel){
        $param = $request->param();
        $libraryInfoModel->startTrans();
        if (empty($param['id'])) {
            $this->jkReturn('-1004','请填写移库单id');
        }
        $moveLibrary = $moveLibraryModel->where(['id'=>$param['id']])->find();
        if (count($moveLibrary) <= 0) {
            $this->jkReturn('-1004','数据为空');
        }
        if ($moveLibrary['move_status'] == 1) {
            $this->jkReturn('-1004','此单据已移库，不允许删除');
        }

        //-- 删除绑定商品
        if(!$libraryInfoModel->where(['move_library_num'=>$moveLibrary['move_library_num']])->delete()){
            $libraryInfoModel->rollback();
            $this->jkReturn('-1004',"移库单商品删除失败,编号为'$moveLibrary[move_library_num]'");
        }
        $this->setAdminUserLog("删除","删除移库单商品,编号为'$moveLibrary[move_library_num]'","wms_move_library_info");

        //--移库单删除
        if(!$moveLibraryModel->where(['id'=>$param['id']])->delete()){
            $this->jkReturn('-1004',"删除失败,移库单编号为'$moveLibrary[move_library_num]'");
        }
        $this->setAdminUserLog("删除","删除移库单,移库单编号为'$moveLibrary[move_library_num]'","wms_move_library",$param['id']);
        $libraryInfoModel->commit();
        $this->jkReturn('0000','删除成功');
    }


    /**
     * @title 移库单导出
     * @description 接口说明
     * @author gyl
     * @url /MoveLibrary/libraryExport
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:move_library_num type:string require:0 default: other: desc:移库单编号
     */
    public function libraryExport(Request $request, MoveLibraryInfoModel $libraryInfoModel, MoveLibraryModel $moveLibraryModel) {
        ob_end_clean();
        $param = $request->param();
       // $param['move_library_num'] = 'YK-20181207-0001';
        $field = "o.move_library_num,h.warehouse_name as check_warehouse_name,w.warehouse_name,a.warea_name,v.shelf_num,wl.wlibrary_num,o.move_status,o.reviewed_status,p.product_num,i.bar_code,p.product_name,p.specifications,p.real_arrival_num,p.wait_storage_num,p.pro_create_date,p.shelf_month";
        $res = $libraryInfoModel
            ->field($field)
            ->alias('p')
            ->where('p.move_library_num',$param['move_library_num'])
            ->join('wms_move_library o','p.move_library_num = o.move_library_num','left')
            ->join('wms_warehouse w','p.wh_id = w.id','left')
            ->join('wms_warehouse h','o.check_wh_id = h.id','left')
            ->join('wms_warehouse_area a','a.id = p.waerea_id','left')
            ->join('wms_row_shelf v','v.id = p.shelf_id','left')
            ->join('wms_wlibrary wl','wl.id = p.library_id','left')
            ->join('wms_product_info i','p.product_num = i.product_num','left')
            ->select();
        $count = $libraryInfoModel
            ->alias('p')
            ->where('p.move_library_num',$param['move_library_num'])
            ->join('wms_move_library o','p.move_library_num = o.move_library_num','left')
            ->join('wms_warehouse w','p.wh_id = w.id','left')
            ->join('wms_warehouse h','o.check_wh_id = h.id','left')
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
        $objPHPExcel->getActiveSheet()->setTitle('libraryProduct');
        // 设置表格宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(10);
        // 列名表头文字加粗
        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getFont()->setBold(true);
        // 列表头文字居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // 列名赋值
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '移库单编号');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '质检仓库');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '移入仓库');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '移入库区');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '移入货架');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '移入库位');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '移库状态');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '审核状态');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '商品编号');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '商品条码');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '商品名称');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', '规格');
        $objPHPExcel->getActiveSheet()->setCellValue('M1', '实际移库数量');
        $objPHPExcel->getActiveSheet()->setCellValue('N1', '待移库数量');
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
            $move_status = $moveLibraryModel->getMoveStatusAttr($value['move_status']);
            $reviewed_status = $moveLibraryModel->getReviewedStatusAttr($value['reviewed_status']);

            $objPHPExcel->getActiveSheet()->setCellValueExplicit('A' . $row_num, $value['move_library_num'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row_num, $value['check_warehouse_name'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row_num, $value['warehouse_name'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row_num, $value['warea_name'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row_num, $value['shelf_num'],\PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row_num, $value['wlibrary_num'],\PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('G' . $row_num, $move_status, \PHPExcel_Cell_DataType::TYPE_STRING);
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

        $outputFileName = '移库单商品_' . date('Y-m-d H:i:s') . '.xls';
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
     * @title 确认移库
     * @description 接口说明
     * @author gyl
     * @url /MoveLibrary/reviewed
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:move_library_num type:string require:0 default: other: desc:移库单编号
     *
     */
    public function reviewed(Request $request, MoveLibraryModel $moveLibraryModel, MoveLibraryInfoModel $libraryInfoModel, WarehousingModel $warehousingModel, WarehousingInfoModel $warehousingInfoModel){
        $param = $request->param();
//        $param['move_library_num'] = 'YK-20181207-0001';
        //查询移库单数据
        $moveLibrary = $moveLibraryModel->where(['move_library_num'=>$param['move_library_num']])->find()->toArray();
        //查询移库商品信息
        $libraryInfo = $libraryInfoModel->where(['move_library_num'=>$param['move_library_num']])->select()->toArray();
        //移库单确认
        $result = $moveLibraryModel->allowField(true)->save(['move_status'=>1,'reviewed_status'=>1],['id'=>$moveLibrary['id']]);
        if ($result) {
            //添加操作日志
            $this->setAdminUserLog("审核","移库确认,编号是$param[move_library_num]","wms_move_library",$moveLibrary['id']);
            //添加审核日志
            $this->setReviewedLog("移库单","$param[move_library_num]",'已移库');
            //生成入库单编号
            $date = date('Y-m-d');
            $where = "date_format(create_date,'%Y-%m-%d') = date_format('" . $date . "','%Y-%m-%d')";
            $warehousing = $warehousingModel->where($where)->count();
            $number = $this->number($warehousing);
            $warehousing_num = 'IN-OI-' . date('Ymd') . '-' . $number;//入库单编号
            //入库单数据
            $warehousingData = array(
                'other_order_num'=>$param['move_library_num'],
                'order_type'=>1,
                'warehousing_name'=>'admin123',
                'warehousing_date'=>date('Y-m-d H:i:s'),
                'wh_id'=>$moveLibrary['wh_id'],
                'warehousing_num'=>$warehousing_num
            );
            $warehousingModel->create($warehousingData);
            //获取入库单ID
            $warehousingId = $warehousingModel->getLastInsID();
            //添加操作日志
            $this->setAdminUserLog("新增","新增入库单,编号是$warehousing_num","wms_warehousing_order",$warehousingId);
            if (count($libraryInfo) > 0) {
                foreach ($libraryInfo as &$k) {
                    $k['warehousing_num'] = $warehousing_num;
                    unset($k['id']);
                }
                $warehousingInfoModel->allowField(true)->saveAll($libraryInfo);
            }
            $this->jkReturn('0000','审核成功');
        }else{
            $this->jkReturn('-1004','审核失败');
        }
    }

}