<?php
/**
 * Created by PhpStorm.
 * User: guanyl
 * Date: 2018/11/27
 * Time: 18:05
 */

namespace app\Api\controller;
use app\api\model\ProductModel;
use app\api\model\ReceiveInfoModel;
use app\api\model\ReceiveModel;
use app\api\model\ReceiveReviewModel;
use app\api\model\WarehouseModel;
use think\Request;

/**
 * @title 领取管理
 * @description 接口说明
 * @group 采购管理
 * @header name:key require:1 default: desc:秘钥(区别设置)

 */

class Receive extends Common
{
    /**
     * @title 领取列表
     * @description 接口说明
     * @author gyl
     * @url /Receive/index
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:领取编号
     * @param name:inflag type:string require:0 default: other: desc:是否出库
     * @param name:start_date type:int require:0 default: 1 other: desc:搜索开始日期
     * @param name:end_date type:int require:0 default: 1 other: desc:搜索结束日期
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1other: desc:页数
     *
     * @return receiveList:领取列表@
     * @receiveList get_order_num:领取编号 get_order_date:订单日期 y_get_date:预计领取日期 get_date:实际领取日期 receiver:领取人姓名 receiver_phone:领取人手机号 warehouse_name:仓库 remark：备注 appr_state:审核状态 appr_advice:审核意见 in_flag:是否入库(0：否，1：是)
     * @return receiveCount:领取数量
     */
    public function index(Request $request, ReceiveModel $receiveModel){
        $param = $request->param();
        $where = "r.disable = 1";
        $field = "r.id,r.get_order_num,r.get_order_date,r.y_get_date,r.get_date,r.receiver,r.receiver_phone,r.wh_id,w.warehouse_name,r.remark,r.appr_state,r.appr_advice,r.in_flag";
        if (!empty($param['search_key'])) {
            $where .= " and r.get_order_num like '%" . $param['search_key'] . "%' ";
        }
        if (!empty($param['start_date']) && !empty($param['end_date'])) {
            $where .= " and date_format(r.get_order_date,'%Y-%m-%d') >= date_format('" . $param['start_date'] . "','%Y-%m-%d')";
            $where .= " and date_format(r.get_order_date,'%Y-%m-%d') <= date_format('" . $param['end_date'] . "','%Y-%m-%d')";
        }
        $receiveList = $receiveModel
            ->field($field)
            ->alias('r')
            ->where($where)
            ->join('wms_warehouse w','w.id = r.wh_id','left')
            ->limit($param["limit"]??10)
            ->page($param['page']??1)
            ->select();
        $receiveCount = $receiveModel
            ->field($field)
            ->alias('r')
            ->where($where)
            ->join('wms_warehouse w','w.id = r.wh_id','left')
            ->count();
        $data = array(
            'receiveList'=>$receiveList,
            'receiveCount'=>$receiveCount
        );
        $this->jkReturn('0000','领取列表',$data);
    }

    /**
     * @title 领取详情
     * @description 接口说明
     * @author gyl
     * @url /Receive/receiveDetail
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     * @param name:get_order_num type:int require:1 default: other: desc:领取编号
     *
     * @return get_order_num:领取单编号
     * @return get_order_date:领取订单日期
     * @return y_get_date:预计领取时间
     * @return get_date:实际领取时间
     * @return receiver:领取人姓名
     * @return receiver_phone:领取人联系方式
     * @return warehouse_name:仓库
     * @return remark:领取单备注
     * @return appr_state:审核状态
     * @return appr_advice:审核意见
     * @return product_info:已选商品信息@
     * @product_info product_num:商品编号 product_name:商品名称 price:单价 unit:计量单位 yj_count:预计数量  gh_count:归还数量 sj_count:实际数量 sy_count:使用数量
     *
     */
    public function receiveDetail(Request $request, ReceiveModel $receiveModel, ReceiveInfoModel $infoModel){
        $param = $request->param();
        $field = "r.id,r.get_order_num,r.get_order_date,r.y_get_date,r.get_date,r.receiver,r.receiver_phone,r.wh_id,w.warehouse_name,r.remark,r.appr_state,r.appr_advice";
        $receive = $receiveModel
            ->field($field)
            ->alias('r')
            ->where('get_order_num',$param['get_order_num'])
            ->join('wms_warehouse w','w.id = r.wh_id','left')
            ->find();
        if (count($receive) >= 1) {
            $receive['product_info'] = $infoModel
                ->where('get_order_num',$receive['get_order_num'])
                ->select();
        }
        $this->jkReturn('0000','领取详情',$receive);
    }

    /**
     * @title 仓库列表
     * @description 接口说明
     * @author gyl
     * @url /Receive/warehouse
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
     * @title 商品列表
     * @description 接口说明
     * @author gyl
     * @url /Receive/productList
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
     * @return vendor_name:供应商名称
     * @return price:进价
     * @return specifications_num:规格
     * @return unit:计量单位
     * @return shelf_month:保质期
     * @return carton:箱规
     * @return rate:税率
     */
    public function productList(Request $request,ProductModel $productModel){
        $param = $request->param();
        $where = "p.disabled = 1 ";
        $field = "p.id,p.product_num,p.product_name,i.bar_code,v.vendor_name,p.price,p.specifications_num,p.unit,p.shelf_month,p.carton,rate";
        if (!empty($param['search_key'])) {
            $where .= " and (p.product_num like '%" . $param['search_key'] . "%' or p.product_name like '%" . $param['search_key'] . "%') ";
        }
        $productList = $productModel
            ->field($field)
            ->alias('p')
            ->where($where)
            ->join('wms_product_info i','p.product_num = i.product_num','left')
            ->join('wms_vendor v','p.vendor_num = v.vendor_num','left')
            ->select();
        $this->jkReturn('0000','商品列表',$productList);
    }

    /**
     * @title 新增领用
     * @description 接口说明
     * @author gyl
     * @url /Receive/receiveAdd
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:wh_id type:string require:1 default: other: desc:预计领取时间
     * @param name:remark type:string require:0 default: other: desc:领取人姓名
     * @param name:remark type:string require:0 default: other: desc:领取人联系方式
     * @param name:wh type:string require:0 default: other: desc:仓库选择
     * @param name:wh type:string require:0 default: other: desc:领取单备注
     * @param name:product_info type:array require:0 default: other: desc:商品信息
     * @param name:product_num type:string require:1 default: other: desc:商品编号
     * @param name:product_name type:string require:1 default: other: desc:商品名称
     * @param name:unit type:string require:1 default: other: desc:计量单位
     * @param name:price type:string require:1 default: other: desc:价格
     * @param name:yj_count type:string require:1 default: other: desc:预计数量
     * @param name:gh_count type:string require:1 default: other: desc:归还数量
     * @param name:sj_count type:string require:1 default: other: desc:实际数量
     * @param name:sy_count type:string require:1 default: other: desc:使用数量
     *
     */
    public function receiveAdd(Request $request, ReceiveModel $receiveModel, ReceiveInfoModel $infoModel, ReceiveReviewModel $reviewModel){
        $param = $request->param();
        $date = date('Y-m-d');
        $where = "date_format(create_date,'%Y-%m-%d') = date_format('" . $date . "','%Y-%m-%d')";
        $receive = $receiveModel->where($where)->count();
        $number = $this->number($receive);
        $param['get_order_num'] = 'OI-' . date('Ymd') . '-' . $number;//领取单编号
        $param['get_order_date'] = date('Y-m-d H:i:s');//订单日期
        $result = $receiveModel->allowField(true)->save($param);
        if($result){
            $receiveId = $receiveModel->getLastInsID();
            $this->setAdminUserLog("新增","新增领用","wms_get_order",$receiveId);
            if (!empty($param['product_info'])) {
                foreach ($param['product_info'] as &$product_info) {
                    $product_info['get_order_num'] = $param['get_order_num'];//领取单编号
                }
            }
            $infoModel->allowField(true)->saveAll($param['product_info']);
            $reviewModel->allowField(true)->save($param);
            $this->jkReturn('0000','添加成功');
        }else{
            $this->jkReturn('-1004','添加失败');
        }
    }

    /**
     * @title 编辑领用
     * @description 接口说明
     * @author gyl
     * @url /Receive/receiveEdit
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:string require:1 default: other: desc:id
     * @param name:remark type:string require:0 default: other: desc:备注
     * @param name:product_info type:array require:0 default: other: desc:商品信息
     *
     * @param name:get_order_num type:string require:1 default: other: desc:领取单编号
     * @param name:product_num type:string require:1 default: other: desc:商品编号
     * @param name:product_name type:string require:1 default: other: desc:商品名称
     * @param name:unit type:string require:1 default: other: desc:计量单位
     * @param name:price type:string require:1 default: other: desc:价格
     * @param name:yj_count type:string require:1 default: other: desc:预计数量
     * @param name:gh_count type:string require:1 default: other: desc:归还数量
     * @param name:sj_count type:string require:1 default: other: desc:实际数量
     * @param name:sy_count type:string require:1 default: other: desc:使用数量
     *
     */
    public function receiveEdit(Request $request, ReceiveModel $receiveModel, ReceiveInfoModel $infoModel){
        $param = $request->param();
        //-- 开启事物
        $infoModel->startTrans();
        $where = array('id'=>$param['id']);
        $result = $receiveModel->allowField(true)->save($param,$where);
        if($result){
            $this->setAdminUserLog("更新","领用更新","wms_get_order",$param['id']);
            //-- 删除领取单商品
            $result = $infoModel->where(['get_order_num'=>$param['product_info'][0]['get_order_num']])->delete();
            if($result<0){
                $infoModel->rollback();
                $this->jkReturn('-1004','删除领取单商品失败');
            }
            if(!$infoModel->allowField(true)->saveAll($param['product_info'])){
                $infoModel->rollback();
                $this->jkReturn('-1004','领取单商品保存失败');
            }
            $infoModel->commit();
            $this->jkReturn('0000','更新成功');
        }else{
            $this->jkReturn('-1004','更新失败');
        }
    }

    /**
     * @title 领用删除
     * @description 接口说明
     * @author gyl
     * @url /Receive/receiveDel
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:get_order_num type:string require:1 default: other: desc:领取单编号
     *
     */
    public function receiveDel(Request $request, ReceiveModel $receiveModel, ReceiveInfoModel $infoModel){
        $param = $request->param();
        $infoModel->startTrans();
        //-- 删除绑定商品
        if(!$infoModel->where(['get_order_num'=>$param['get_order_num']])->delete()){
            $infoModel->rollback();
            $this->jkReturn('-1004',"领取单商品删除失败,领取单编号为'$param[get_order_num]'");
        }
        $this->setAdminUserLog("删除","删除领取单商品,领取单编号为'$param[get_order_num]'","wms_order_out_copy_detail");
        if(!$receiveModel->where(['get_order_num'=>$param['get_order_num']])->delete()){
            $this->jkReturn('-1004',"删除失败,领取单编号为'$param[get_order_num]'");
        }
        $this->setAdminUserLog("删除","删除领取单,领取单编号为'$param[get_order_num]'","wms_get_order");
        $infoModel->commit();
        $this->jkReturn('0000','删除成功');
    }

    /**
     * @title 领用审核
     * @description 接口说明
     * @author gyl
     * @url /Receive/reviewed
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:approval_status type:int require:1 default: other: desc:审核状态(0：未审核，1：审核完成，2：审核未通过)
     * @param name:appr_advice type:int require:1 default: other: desc:审核意见
     * @param name:approval_remark type:int require:1 default: other: desc:审核备注
     * @param name:get_order_num type:string require:1 default: other: desc:领取单号
     *
     */
    public function reviewed(Request $request, ReceiveModel $receiveModel, ReceiveReviewModel $reviewModel){
        $param = $request->param();
        $param['approval_by'] = 'admin123';
        $param['approval_time'] = date('Y-m-d H:i:s');
        $where = ['get_order_num'=>$param['get_order_num']];
        $result = $reviewModel->allowField(true)->save($param,$where);
        if($result){
            $receive = array('appr_state'=>$param['approval_status'],'appr_advice'=>$param['appr_advice']);
            $receiveModel->allowField(true)->save($receive,$where);
            $this->setAdminUserLog("审核","领用审核,领取单编号是$param[get_order_num]","wms_get_review");
            $this->jkReturn('0000','审核成功');
        }else{
            $this->jkReturn('-1004','审核失败');
        }
    }
}