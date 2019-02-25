<?php
/**
 * Created by PhpStorm.
 * User: guanyl
 * Date: 2018/12/3
 * Time: 16:11
 */

namespace app\Api\controller;
use app\api\model\PurOrderModel;
use app\api\model\PurProductModel;
use app\api\model\PurStOrderModel;
use app\api\model\QualityCheckInfoModel;
use app\api\model\QualityCheckModel;
use app\api\model\QualityCheckWaitInfoModel;
use app\api\model\QualityCheckWaitModel;
use think\Request;

/**
 * @title 质检管理
 * @description 接口说明
 * @group 单据管理
 * @header name:key require:1 default: desc:秘钥(区别设置)

 */

class QualityCheck extends Common
{
    /**
     * @title 待质检申请单列表
     * @description 接口说明
     * @author gyl
     * @url /QualityCheck/checkWaitList
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:采购单编号、质检申请单编号
     * @param name:wh_id type:string require:0 default: other: desc:仓库id
     * @param name:start_date type:int require:0 default: 1 other: desc:搜索开始日期
     * @param name:end_date type:int require:0 default: 1 other: desc:搜索结束日期
     * @param name:check_wait_status type:int require:0 default: 1 other: desc:待质检状态(0:待质检，1:部分质检，2:已质检)
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1other: desc:页数
     *
     * @return checkWaitList:待质检申请单列表@
     * @checkWaitList pur_order_num:采购单编号 check_wait_num:待质检申请单编号 warehouse_name:质检仓库 warea_name:质检库区 vendor:供应商编码 vendor_name:供应商 remark:备注 create_by:创建人 create_date：创建时间 check_wait_status:待质检状态(0:待质检，1:部分质检，2:已质检) wait_status:待质检状态
     * @return checkWaitCount:待质检入库申请单数量
     */
    public function checkWaitList(Request $request, QualityCheckWaitModel $checkWaitModel){
        $param = $request->param();
        $where = "q.disabled = 1";
        $field = "q.id,q.pur_order_num,q.check_wait_num,w.warehouse_name,a.warea_name,q.vendor,v.vendor_name,q.remark,q.create_by,q.create_date,q.check_wait_status";
        if (!empty($param['search_key'])) {
            $where .= " and (q.pur_order_num like '%" . $param['search_key'] . "%' or q.check_wait_num like '%" . $param['search_key'] . "%') ";
        }
        if (!empty($param['start_date']) && !empty($param['end_date'])) {
            $where .= " and date_format(q.create_date,'%Y-%m-%d') >= date_format('" . $param['start_date'] . "','%Y-%m-%d')";
            $where .= " and date_format(q.create_date,'%Y-%m-%d') <= date_format('" . $param['end_date'] . "','%Y-%m-%d')";
        }
        if (!empty($param['check_wait_status'])) {
            $where .= " and q.check_wait_status = $param[check_wait_status]";
        }
        $checkWaitList = $checkWaitModel
            ->field($field)
            ->alias('q')
            ->where($where)
            ->join('wms_warehouse w','w.id = q.wh_id','left')
            ->join('wms_warehouse_area a','a.id = q.waerea_id','left')
            ->join('wms_vendor v','v.vendor_num = q.vendor','left')
            ->limit($param["limit"]??10)
            ->page($param['page']??1)
            ->select();
        $checkWaitCount = $checkWaitModel
            ->alias('q')
            ->where($where)
            ->join('wms_warehouse w','w.id = q.wh_id','left')
            ->join('wms_warehouse_area a','a.id = q.waerea_id','left')
            ->join('wms_vendor v','v.vendor_num = q.vendor','left')
            ->count();
        $data = array(
            'checkWaitList'=>$checkWaitList,
            'checkWaitCount'=>$checkWaitCount
        );
        $this->jkReturn('0000','待质检申请单列表',$data);
    }

    /**
     * @title 待质检申请单详情
     * @description 接口说明
     * @author gyl
     * @url /QualityCheck/checkWaitDetail
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     * @param name:id type:int require:1 default: other: desc:申请单id
     *
     * @return check_wait_num:申请单编号
     * @return pur_order_num:采购订单编号
     * @return warehouse_name:质检仓库
     * @return warea_name:质检库区
     * @return wh_id:质检仓库id
     * @return waerea_id:质检库区id
     * @return vendor:供应商编码
     * @return vendor_name:供应商
     * @return create_by:创建人
     * @return create_date:创建时间
     * @return remark:备注
     * @return check_wait_status:待质检状态(0:待质检，1:部分质检，2:已质检)
     * @return wait_status:待质检状态
     * @return product_info:采购商品信息@
     * @product_info product_num:商品编号 product_name:商品名称 bar_code:商品条形码 vendor_name:供应商名称 price:进价 unit:单位 pro_create_date:生产日期 shelf_month:保质期 specifications:规格 carton:箱规 input_tax:税率 batch_code:批次号 product_amount:采购数量 wait_storage_num:实际到货数量、待质检商品数量
     *
     */
    public function checkWaitDetail(Request $request, QualityCheckWaitModel $checkWaitModel, QualityCheckWaitInfoModel $waitInfoModel){
        $param = $request->param();
        $field = "q.id,q.pur_order_num,q.check_wait_num,q.wh_id,w.warehouse_name,q.waerea_id,a.warea_name,q.vendor,v.vendor_name,q.create_by,q.create_date,q.check_wait_status,q.remark";
        $checkWait = $checkWaitModel
            ->field($field)
            ->alias('q')
            ->where('q.id',$param['id'])
            ->join('wms_warehouse w','w.id = q.wh_id','left')
            ->join('wms_warehouse_area a','a.id = q.waerea_id','left')
            ->join('wms_vendor v','v.vendor_num = q.vendor','left')
            ->find();
        $checkWait['wait_status'] = $checkWaitModel->getApplyStatusAttr($checkWait['check_wait_status']);
        if (count($checkWait) >= 1) {
            $checkWait['product_info'] = $waitInfoModel
                ->field('pp.*,p.bar_code')
                ->alias('pp')
                ->where('check_wait_num',$checkWait['check_wait_num'])
                ->join('wms_product_info p','p.product_num = pp.product_num','left')
                ->select();

        }
        $this->jkReturn('0000','待质检申请单详情',$checkWait);
    }

    /**
     * @title 质检单列表
     * @description 接口说明
     * @author gyl
     * @url /QualityCheck/qualityCheckList
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:采购单编号、质检申请单编号、质检单编号
     * @param name:wh_id type:string require:0 default: other: desc:仓库id
     * @param name:start_date type:int require:0 default: 1 other: desc:搜索开始日期
     * @param name:end_date type:int require:0 default: 1 other: desc:搜索结束日期
     * @param name:check_status type:int require:0 default: 1 other: desc:质检状态(0:待质检，1:已质检)
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1other: desc:页数
     *
     * @return checkList:质检单列表@
     * @checkList pur_order_num:采购单编号 check_wait_num:待质检申请单编号 check_num:质检编号 warehouse_name:质检仓库 warea_name:质检库区 vendor:供应商编码 vendor_name:供应商 remark:备注 create_by:创建人 create_date：创建时间 check_status:质检状态(0:待质检，1:已质检) status:状态
     * @return checkCount:质检单数量
     */
    public function qualityCheckList(Request $request, QualityCheckModel $checkModel){
        $param = $request->param();
        $where = "q.disabled = 1";
        $field = "q.id,q.pur_order_num,q.check_wait_num,q.check_num,w.warehouse_name,a.warea_name,q.vendor,v.vendor_name,q.remark,q.create_by,q.create_date,q.check_status";
        if (!empty($param['search_key'])) {
            $where .= " and (q.pur_order_num like '%" . $param['search_key'] . "%' or q.check_wait_num like '%" . $param['search_key'] . "%' or q.check_num like '%" . $param['search_key'] . "%') ";
        }
        if (!empty($param['start_date']) && !empty($param['end_date'])) {
            $where .= " and date_format(q.create_date,'%Y-%m-%d') >= date_format('" . $param['start_date'] . "','%Y-%m-%d')";
            $where .= " and date_format(q.create_date,'%Y-%m-%d') <= date_format('" . $param['end_date'] . "','%Y-%m-%d')";
        }
        if (!empty($param['check_status'])) {
            $where .= " and q.check_status = $param[check_status]";
        }
        $checkList = $checkModel
            ->field($field)
            ->alias('q')
            ->where($where)
            ->join('wms_warehouse w','w.id = q.wh_id','left')
            ->join('wms_warehouse_area a','a.id = q.waerea_id','left')
            ->join('wms_vendor v','v.vendor_num = q.vendor','left')
            ->limit($param["limit"]??10)
            ->page($param['page']??1)
            ->select();
        if (count($checkList) > 0) {
            foreach ($checkList as &$list) {
                $list['status'] = $checkModel->getApplyStatusAttr($list['check_status']);
            }
        }
        $checkCount = $checkModel
            ->alias('q')
            ->where($where)
            ->join('wms_warehouse w','w.id = q.wh_id','left')
            ->join('wms_warehouse_area a','a.id = q.waerea_id','left')
            ->join('wms_vendor v','v.vendor_num = q.vendor','left')
            ->count();
        $data = array(
            'checkList'=>$checkList,
            'checkCount'=>$checkCount
        );
        $this->jkReturn('0000','质检单列表',$data);
    }

    /**
     * @title 质检单详情
     * @description 接口说明
     * @author gyl
     * @url /QualityCheck/qualityCheckDetail
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     * @param name:id type:int require:1 default: other: desc:质检单id
     *
     * @return check_num:质检单编号
     * @return check_wait_num:申请单编号
     * @return pur_order_num:采购订单编号
     * @return warehouse_name:质检仓库
     * @return warea_name:质检库区
     * @return wh_id:质检仓库id
     * @return waerea_id:质检库区id
     * @return vendor:供应商编码
     * @return vendor_name:供应商
     * @return create_by:创建人
     * @return create_date:创建时间
     * @return remark:备注
     * @return check_status:质检状态(0:待质检，1:已质检)
     * @return status:质检状态
     * @return product_info:采购商品信息@
     * @product_info product_num:商品编号 product_name:商品名称 bar_code:商品条形码 vendor_name:供应商名称 price:进价 unit:单位 pro_create_date:生产日期 shelf_month:保质期 specifications:规格 carton:箱规 input_tax:税率 batch_code:批次号 product_amount:采购数量 wait_storage_num:实际到货数量、待质检商品数量
     *
     */
    public function qualityCheckDetail(Request $request, QualityCheckModel $checkModel, QualityCheckInfoModel $checkInfoModel){
        $param = $request->param();
        $field = "q.id,q.check_num,q.pur_order_num,q.check_wait_num,q.wh_id,w.warehouse_name,q.waerea_id,a.warea_name,q.vendor,v.vendor_name,q.create_by,q.create_date,q.check_status,q.remark";
        $checkWait = $checkModel
            ->field($field)
            ->alias('q')
            ->where('q.id',$param['id'])
            ->join('wms_warehouse w','w.id = q.wh_id','left')
            ->join('wms_warehouse_area a','a.id = q.waerea_id','left')
            ->join('wms_vendor v','v.vendor_num = q.vendor','left')
            ->find();
        $checkWait['status'] = $checkModel->getApplyStatusAttr($checkWait['check_status']);
        $checkWait['product_info'] = $checkInfoModel
            ->field('pp.*,p.bar_code')
            ->alias('pp')
            ->where('check_num',$checkWait['check_num'])
            ->join('wms_product_info p','p.product_num = pp.product_num','left')
            ->select();


        $this->jkReturn('0000','待质检申请单详情',$checkWait);
    }

    /**
     * @title 质检单新建
     * @description 接口说明
     * @author gyl
     * @url /QualityCheck/qualityCheckAdd
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:pur_order_num type:string require:0 default: 1 1other: desc:采购单编号
     * @param name:check_wait_num type:string require:0 default: 1 1other: desc:质检申请单编号
     * @param name:create_by type:string require:1 default: other: desc:创建人
     * @param name:create_date type:string require:1 default: other: desc:创建时间
     * @param name:remark type:string require:1 default: other: desc:备注
     * @param name:check_status type:string require:1 default: other: desc:质检状态(0:待质检，1:已质检)
     * @param name:pur_product type:array require:1 default: other: desc:采购商品信息
     * @param name:id type:int require:1 default: other: desc:采购商品id
     * @param name:good_number type:string require:1 default: other: desc:良品数量
     * @param name:defective_number type:string require:1 default: other: desc:次品数量
     * @param name:wait_storage_num type:string require:1 default: other: desc:已检数量
     *
     */
    public function qualityCheckAdd(Request $request,QualityCheckModel $checkModel, QualityCheckWaitModel $checkWaitModel,QualityCheckInfoModel $checkInfoModel, QualityCheckWaitInfoModel $waitInfoModel){
        $param = $request->param();
        /*$param = array(
            'pur_order_num'=>'OI-20170703-0002',
            'check_wait_num'=>'DZJ-20181205-0001',
            'create_by'=>'admin',
            'remark'=>'质检单新建',
            'pur_product'=>array(
                array(
                    'id'=>1,
                    'good_number'=>'115',
                    'defective_number'=>'5'
                ),
                array(
                    'id'=>2,
                    'good_number'=>'118',
                    'defective_number'=>'2'
                ),
                array(
                    'id'=>3,
                    'good_number'=>'116',
                    'defective_number'=>'4'
                ),
                array(
                    'id'=>4,
                    'good_number'=>'68',
                    'defective_number'=>'2'
                ),
            )
        );*/
        //判断质检申请单编号或采购单编号
        if (empty($param['check_wait_num']) || empty($param['pur_order_num'])) {
            $this->jkReturn('-1004','请填写质检申请单编号或采购单编号');
        }

        //处理采购商品信息
        $pur_product = array();

        if (!empty($param['pur_product'])) {
            //判断质检单良品和次品加起来不能大于待检入库数量
            foreach ($param['pur_product'] as &$product) {
                $number = $product['good_number'] + $product['defective_number'];
                $waitInfo = $waitInfoModel->where(['id'=>$product['id']])->find();
                if ($number > $waitInfo['wait_storage_num']) {
                    $this->jkReturn('-1004','良品和次品之和不能大于待检数量');
                }
                $pur_product[$product['id']]['good_number'] = $product['good_number'];
                $pur_product[$product['id']]['defective_number'] = $product['defective_number'];
                $pur_product[$product['id']]['wait_storage_num'] = $product['wait_storage_num'];
            }
        }
        //待质检单数据
        $checkWait = $checkWaitModel->where(['check_wait_num'=>$param['check_wait_num']])->find();
        //根据待质检数据生成质检单数据
        $param['wh_id'] = $checkWait['wh_id'];//仓库
        $param['waerea_id'] = $checkWait['waerea_id'];//库区
        $param['vendor'] = $checkWait['vendor'];//供应商
        //生成质检单编号
        $date = date('Y-m-d');
        $where = "date_format(create_date,'%Y-%m-%d') = date_format('" . $date . "','%Y-%m-%d')";
        $check = $checkModel->where($where)->count();
        $number = $this->number($check);
        $param['check_num'] = 'ZJ-' . date('Ymd') . '-' . $number;//质检单编号
        //保存质检单数据
        $result = $checkModel->allowField(true)->save($param);
        if($result){
            //获取质检单ID
            $checkId = $checkModel->getLastInsID();
            //保存操作日志
            $this->setAdminUserLog("新增","质检单新建","wms_quality_check",$checkId);
            //添加质检单详细信息
            $waitInfo = $waitInfoModel->where(['check_wait_num'=>$param['check_wait_num']])->select()->toArray();
            if (count($waitInfo) > 0) {
                foreach ($waitInfo as &$info) {
                    $info['check_num'] = $param['check_num'];
                    if (!empty($pur_product)) {
                        if (empty($pur_product[$info['id']])) {
                            continue;
                        }
                        $info['good_number'] = $pur_product[$info['id']]['good_number'];
                        $info['defective_number'] = $pur_product[$info['id']]['defective_number'];
                        $info['wait_storage_num'] = $pur_product[$info['id']]['wait_storage_num'];
                    }
                }
                foreach ($waitInfo as &$k) {
                    unset($k['id']);
                }
                $checkInfoModel->allowField(true)->saveAll($waitInfo);
            }
            //判断是否全部质检完成
            $this->jkReturn('0000','添加成功');
        }else{
            $this->jkReturn('-1004','添加失败');
        }
    }

    /**
     * @title 质检单更新
     * @description 接口说明
     * @author gyl
     * @url /QualityCheck/qualityCheckEdit
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:string require:0 default: 1 1other: desc:质检单id
     * @param name:check_num type:string require:0 default: 1 1other: desc:质检单编号
     * @param name:pur_order_num type:string require:0 default: 1 1other: desc:采购单编号
     * @param name:check_wait_num type:string require:0 default: 1 1other: desc:质检申请单编号
     * @param name:remark type:string require:1 default: other: desc:备注
     * @param name:check_status type:string require:1 default: other: desc:质检状态(0:待质检，1:已质检)
     * @param name:pur_product type:array require:1 default: other: desc:采购商品信息
     * @param name:id type:int require:1 default: other: desc:采购商品id
     * @param name:good_number type:string require:1 default: other: desc:良品数量
     * @param name:defective_number type:string require:1 default: other: desc:次品数量
     * @param name:wait_storage_num type:string require:1 default: other: desc:已检数量
     *
     */
    public function qualityCheckEdit(Request $request, QualityCheckModel $checkModel,QualityCheckInfoModel $checkInfoModel){
        $param = $request->param();
        /*$param = array(
            'id'=>'4',
            'pur_order_num'=>'OI-20170703-0002',
            'check_wait_num'=>'DZJ-20181205-0001',
            'create_by'=>'admin',
            'remark'=>'质检单更新',
            'pur_product'=>array(
                array(
                    'id'=>1,
                    'good_number'=>'120',
                    'defective_number'=>'0'
                ),
                array(
                    'id'=>2,
                    'good_number'=>'100',
                    'defective_number'=>'20'
                ),
                array(
                    'id'=>3,
                    'good_number'=>'116',
                    'defective_number'=>'4'
                ),
                array(
                    'id'=>4,
                    'good_number'=>'68',
                    'defective_number'=>'2'
                ),
            )
        );*/
        if (empty($param['id'])) {
            $this->jkReturn('-1004','请填写质检单id');
        }
        //判断质检单良品和次品加起来不能大于采购数量
        foreach ($param['pur_product'] as $pur_product) {
            $number = $pur_product['good_number'] + $pur_product['defective_number'];
            $product = $checkInfoModel->where(['id'=>$pur_product['id']])->find();
            if ($number > $product['wait_storage_num']) {
                $this->jkReturn('-1004','良品和次品之和不能大于待检数量');
            }
        }
        $where['id'] = $param['id'];
        $result = $checkModel->allowField(true)->save($param, $where);
        if($result){
            $this->setAdminUserLog("更新","质检单更新","wms_quality_check",$param['id']);
            $checkInfoModel->allowField(true)->saveAll($param['pur_product']);
            $this->jkReturn('0000','更新成功');
        }else{
            $this->jkReturn('-1004','更新失败');
        }
    }

    /**
     * @title 质检单删除
     * @description 接口说明
     * @author gyl
     * @url /QualityCheck/qualityCheckDel
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:int require:0 default: other: desc:质检单id
     *
     */
    public function qualityCheckDel(Request $request, QualityCheckModel $checkModel, QualityCheckInfoModel $checkInfoModel){
        $param = $request->param();
        $checkInfoModel->startTrans();
        if (empty($param['id'])) {
            $this->jkReturn('-1004','请填写质检单id');
        }
        $check = $checkModel->where(['id'=>$param['id']])->find();
        if (count($check) <= 0) {
            $this->jkReturn('-1004','数据为空');
        }
        if ($check['check_status'] == 1) {
            $this->jkReturn('-1004','此单据已质检，不允许删除');
        }

        //-- 删除绑定商品
        if(!$checkInfoModel->where(['check_num'=>$check['check_num']])->delete()){
            $checkInfoModel->rollback();
            $this->jkReturn('-1004',"质检单商品删除失败,编号为'$check[check_num]'");
        }
        $this->setAdminUserLog("删除","删除质检单商品,编号为'$check[check_num]'","wms_pur_storder_info");

        //--质检单删除
        if(!$checkModel->where(['id'=>$param['id']])->delete()){
            $this->jkReturn('-1004',"删除失败,质检单编号为'$check[check_num]'");
        }
        $this->setAdminUserLog("删除","删除质检单,质检单编号为'$check[check_num]'","wms_quality_check",$param['id']);
        $checkInfoModel->commit();
        $this->jkReturn('0000','删除成功');
    }

    /**
     * @title 质检单导出
     * @description 接口说明
     * @author gyl
     * @url /QualityCheck/qualityCheckExport
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:check_num type:string require:0 default: other: desc:质检单编号
     */
    public function qualityCheckExport(Request $request, QualityCheckModel $checkModel,QualityCheckInfoModel $checkInfoModel) {
        ob_end_clean();
        $param = $request->param();
//        $param['check_num'] = 'ZJ-20181206-0001';
        $field = "o.pur_order_num,o.check_wait_num,o.check_num,w.warehouse_name,a.warea_name,v.vendor_name,o.check_status,p.product_num,i.bar_code,p.product_name,p.specifications,p.product_amount,p.real_arrival_num,p.wait_storage_num,p.good_number,p.defective_number,p.pro_create_date,p.shelf_month";
        $res = $checkInfoModel
            ->field($field)
            ->alias('p')
            ->where('p.check_num',$param['check_num'])
            ->join('wms_quality_check o','p.check_num = o.check_num','left')
            ->join('wms_warehouse w','p.wh_id = w.id','left')
            ->join('wms_warehouse_area a','a.id = p.waerea_id','left')
            ->join('wms_vendor v','v.vendor_num = p.vendor','left')
            ->join('wms_product_info i','p.product_num = i.product_num','left')
            ->select();
        $count = $checkInfoModel
            ->alias('p')
            ->where('p.check_num',$param['check_num'])
            ->join('wms_quality_check o','p.check_num = o.check_num','left')
            ->join('wms_warehouse w','p.wh_id = w.id','left')
            ->join('wms_warehouse_area a','a.id = p.waerea_id','left')
            ->join('wms_vendor v','v.vendor_num = p.vendor','left')
            ->join('wms_product_info i','p.product_num = i.product_num','left')
            ->count();
        $productCount = '至本页合计单品数：' . $count;
        // 实例化excel类
        $objPHPExcel = new \PHPExcel();
        // 操作第一个工作表
        $objPHPExcel->setActiveSheetIndex(0);
        // 设置sheet名
        $objPHPExcel->getActiveSheet()->setTitle('qualityCheckProduct');
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(10);
        // 列名表头文字加粗
        $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->getFont()->setBold(true);
        // 列表头文字居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // 列名赋值
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '采购单编号');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '申请单编号');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '质检编号');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '收货仓库');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '收货库区');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '供应商');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '质检状态');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '商品编号');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '商品条码');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '商品名称');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '规格');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', '采购数量');
        $objPHPExcel->getActiveSheet()->setCellValue('M1', '实际到货数量');
        $objPHPExcel->getActiveSheet()->setCellValue('N1', '待质检数量');
        $objPHPExcel->getActiveSheet()->setCellValue('O1', '良品');
        $objPHPExcel->getActiveSheet()->setCellValue('P1', '不良品');
        $objPHPExcel->getActiveSheet()->setCellValue('Q1', '生产日期');
        $objPHPExcel->getActiveSheet()->setCellValue('R1', '保质期');

        // 数据起始行
        $row_num = 2;
        // 向每行单元格插入数据
        foreach ($res as $value) {
            // 设置所有垂直居中
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row_num . ':' . 'R' . $row_num)->getAlignment()
                ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            // 设置单元格数值
            $check_status = $checkModel->getApplyStatusAttr($value['check_status']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('A' . $row_num, $value['pur_order_num'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row_num, $value['check_wait_num'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row_num, $value['check_num'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row_num, $value['warehouse_name'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row_num, $value['warea_name'],\PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row_num, $value['vendor_name'],\PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('G' . $row_num, $check_status, \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row_num, $value['product_num'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row_num, $value['bar_code'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row_num, $value['product_name'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row_num, $value['specifications'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row_num, $value['product_amount'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $row_num, $value['real_arrival_num'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $row_num, $value['wait_storage_num'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . $row_num, $value['good_number'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . $row_num, $value['defective_number'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row_num, $value['pro_create_date'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('R' . $row_num, $value['shelf_month'], \PHPExcel_Cell_DataType::TYPE_STRING);
            $row_num++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row_num, $productCount, \PHPExcel_Cell_DataType::TYPE_STRING);

        $outputFileName = '质检单商品_' . date('Y-m-d H:i:s') . '.xls';
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
     * @title 确认质检
     * @description 接口说明
     * @author gyl
     * @url /QualityCheck/reviewed
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:check_num type:string require:0 default: other: desc:质检单编号
     *
     */
    public function reviewed(Request $request, QualityCheckModel $checkModel, QualityCheckInfoModel $checkInfoModel, QualityCheckWaitInfoModel $waitInfoModel){
        $param = $request->param();
        $param['check_num'] = 'ZJ-20181206-0001';
        //查询质检单数据
        $checkOrder = $checkModel->where(['check_num'=>$param['check_num']])->find()->toArray();
        //查询质检商品信息
        $checkInfo = $checkInfoModel->where(['check_num'=>$param['check_num']])->select()->toArray();
        //查询待质检商品
        $waitInfo = $waitInfoModel->where(['check_wait_num'=>$checkOrder['check_wait_num']])->select()->toArray();

        //质检单确认
        $result = $checkModel->allowField(true)->save(['check_status'=>1],['id'=>$checkOrder['id']]);
        if ($result) {
            //添加操作日志
            $this->setAdminUserLog("审核","质检确认,编号是$param[check_num]","wms_quality_check",$checkOrder['id']);
            //添加审核日志
            $this->setReviewedLog("质检单","$param[check_num]",'已质检');
            if (count($waitInfo) > 0) {
                foreach ($waitInfo as &$k) {
                    if (count($checkInfo) > 0) {
                        foreach ($checkInfo as &$v) {
                            $num = $k['wait_storage_num'] - $v['wait_storage_num'];
                            if ($num <= 0) {
                                $num = 0;
                            }
                            //确认质检，待质检商品数量改变
                            $waitInfoModel->allowField(true)->save(['wait_storage_num'=>$num],['id'=>$k['id']]);
                        }
                    }
                }
            }
            $this->jkReturn('0000','审核成功');
        }else{
            $this->jkReturn('-1004','审核失败');
        }
    }


}