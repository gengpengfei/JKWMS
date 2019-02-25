<?php
/**
 * Created by PhpStorm.
 * User: guanyl
 * Date: 2018/11/16
 * Time: 11:47
 */

namespace app\Api\controller;
use app\api\model\BigCustomerOrderDetailModel;
use app\api\model\BigCustomerOrderDetailSHModel;
use app\api\model\BigCustomerOrderModel;
use app\api\model\BigCustomerOrderSHModel;
use app\api\model\ProductModel;
use app\api\model\SystemConfigModel;
use app\api\service\UploadService;
use think\Request;

/**
 * @title 大客户方案管理
 * @description 接口说明
 * @group 大客户专项
 * @header name:key require:1 default: desc:秘钥(区别设置)

 */

class BigCustomer extends Common
{
    protected $uploadService;
    /**
     * @title 大客户需求订单管理
     * @description 接口说明
     * @author gyl
     * @url /BigCustomer/demandOrderList
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:订单编号、会员名称
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1 other: desc:页数
     * @param name:start_date type:int require:0 default: 1 other: desc:搜索开始日期
     * @param name:end_date type:int require:0 default: 1 other: desc:搜索结束日期
     * @param name:t_type type:int require:0 default: 1 1other: desc:类型（1：普通订单，2：三农订单）
     * @param name:sheng_yu type:int require:0 default: 1 1other: desc:类型 (0：有剩余，1：无剩余）
     * @param name:in_flag type:int require:0 default: 1 1other: desc:订单状态（0：未提交:1：采购提交方案中:2：采购方案初审中:3：确认方案中:4：方案未通过:5：渠道经理审核中:6：徐总审核中:7：钟总审核中，8：方案已通过，9：已生成订单）
     *
     *
     * @return demandOrderList:大客户需求订单列表@
     * @demandOrderList order_num:订单编号 in_flag:订单状态（0：未提交:1：采购提交方案中:2：采购方案初审中:3：确认方案中:4：方案未通过:5：渠道经理审核中:6：徐总审核中:7：钟总审核中，8：方案已通过，9：已生成订单）update_by:处理人 member_name:会员名称 receive_name:收件人 receive_tel:收件手机 receive_city:市  create_by:创建人 order_end_date:过期时间 t_type:类型（1：普通订单，2：三农订单）
     * @return demandOrderList:大客户需求订单数量
     *
     */
    public function demandOrderList(Request $request,BigCustomerOrderModel $bigCustomerOrderModel){
        $field = "id,order_num,in_flag,member_name,receive_name,receive_tel,receive_city,create_by,order_end_date,t_type,sheng_yu";
        $param = $request->param();
        $where = "1 = 1";
        if (!empty($param['search_key'])) {
            $where .= " and (order_num like  '%" . $param['search_key'] . "%' or member_name like '%" . $param['search_key'] . "%') ";
        }
        if (!empty($param['t_type'])) {
            $where .= " and t_type = $param[t_type] ";
        } 
        if (!empty($param['in_flag'])) {
            $where .= " and in_flag = $param[in_flag] ";
        }
        if (!empty($param['start_date']) && !empty($param['end_date'])) {
            $where .= " and date_format(create_date,'%Y-%m-%d') >= date_format('" . $param['start_date'] . "','%Y-%m-%d')";
            $where .= " and date_format(create_date,'%Y-%m-%d') <= date_format('" . $param['end_date'] . "','%Y-%m-%d')";
        }
        $demandOrderList = $bigCustomerOrderModel
            ->field($field)
            ->where($where)
            ->limit($param["limit"]??10)
            ->page($param['page']??1)
            ->order('order_num','DESC')
            ->select();
        $demandOrderCount = $bigCustomerOrderModel->where($where)->count();
        $data = array(
            'demandOrderList'=>$demandOrderList,
            'demandOrderCount'=>$demandOrderCount
        );
        $this->jkReturn('0000','大客户需求订单管理',$data);
    }

    /**
     * @title 大客户需求订单添加(编辑)
     * @description 接口说明
     * @author gyl
     * @url /BigCustomer/demandOrderSave
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:string require:0 default: other: desc:id(如果传id，就是编辑)
     * @param name:remark type:string require:1 default: other: desc:大客户需求说明
     * @param name:order_num type:string require:1 default: other: desc:订单编号
     * @param name:member_name type:string require:1 default: other: desc:客户名称
     * @param name:t_type type:int require:0 default: 1 1other: desc:类型（1：普通订单，2：三农订单）
     * @param name:order_end_date type:string require:1 default: other: desc:预销售单过期时间
     * @param name:product_info type:array require:0 default: other: desc:商品信息
     * @param name:id type:string require:1 default: other: desc:id
     * @param name:product_num type:string require:1 default: other: desc:商品编号
     * @param name:product_name type:string require:1 default: other: desc:商品名称
     * @param name:price type:string require:1 default: other: desc:单价
     * @param name:specifications type:string require:1 default: other: desc:规格
     * @param name:md_price type:string require:1 default: other: desc:门店价格
     * @param name:final_price type:string require:0 default: other: desc:建议销售价格
     * @param name:buy_num type:string require:0 default: other: desc:数量
     *
     *
     */
    public function demandOrderSave(Request $request, BigCustomerOrderModel $bigCustomerOrderModel,BigCustomerOrderDetailModel $bigCustomerOrderDetailModel){
        $param = $request->param();
        //-- 开启事物
        $bigCustomerOrderDetailModel->startTrans();
        if (empty($param['id'])) {
            $msg = "添加";
            $type = "新增";
            $result = $bigCustomerOrderModel->allowField(true)->save($param);
        }else{
            $msg = "修改";
            $type = "更新";
            $where = ['id'=>$param['id']];
            $result = $bigCustomerOrderModel->allowField(true)->save($param,$where);
        }
        if($result){
            if (empty($param['id'])) {
                $bigCustomerOrderId = $bigCustomerOrderModel->getLastInsID();
            }else{
                $bigCustomerOrderId = $param['id'];
            }
            $this->setAdminUserLog($type,$msg . "预销售订单","wms_big_cust_order",$bigCustomerOrderId);
            $this->setReviewedLog("大客户","$param[order_num]","提交预售单");
            if (!empty($param['product_info'])) {
                foreach ($param['product_info'] as &$product_info) {
                    $product_info['order_num'] = $param['order_num'];
                    $product_info['select_fa'] = 1;
                    unset($product_info['id']);
                }
                //-- 删除绑定商品
                $result = $bigCustomerOrderDetailModel->where(['order_num'=>$param['product_info'][0]['order_num']])->delete();
                if($result<0){
                    $bigCustomerOrderDetailModel->rollback();
                    $this->jkReturn('-1004','删除绑定商品失败');
                }
                if(!$bigCustomerOrderDetailModel->allowField(true)->saveAll($param['product_info'])){
                    $bigCustomerOrderDetailModel->rollback();
                    $this->jkReturn('-1004','绑定失败');
                }
            }
            $bigCustomerOrderDetailModel->commit();
            $this->jkReturn('0000',$msg . '成功');
        }else{
            $this->jkReturn('-1004',$msg . '失败');
        }
    }

    /**
     * @title 大客户需求订单提交
     * @description 接口说明
     * @author gyl
     * @url /BigCustomer/submitReviewed
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     * @param name:order_num type:array require:1 default: other: desc:预售订单编号(数组)
     *
     *
     */
    public function submitReviewed(Request $request, BigCustomerOrderModel $orderModel){
        $param = $request->param();
        //多个提交
        foreach ($param['order_num'] as $v){
            $where = array('order_num'=>$v);
            $data = array('in_flag'=>1);
            if(!$orderModel->update($data,$where)){
                $this->jkReturn('-1004',"提交失败,预售订单编号为'$v'");
            }
            $this->setAdminUserLog("提交","大客户提交,预售订单编号为'$v'","wms_big_cust_order");
            $this->setReviewedLog("大客户","$v","提交方案");
        }
        $this->jkReturn('0000','提交成功');
    }

/**
     * @title 大客户需求订单详情
     * @description 接口说明
     * @author gyl
     * @url /BigCustomer/demandOrderDetail
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:int require:1 default: other: desc:id
     *
     * @return order_num:订单编号
     * @return member_name:客户名称
     * @return receive_name:收件人
     * @return receive_mobile:收件人电话
     * @return receive_tel:收件人手机
     * @return receive_province:省
     * @return receive_city:市
     * @return receive_area:区
     * @return receive_address:详细地址
     * @return invoice_header:发票标题
     * @return order_create_date:创建时间
     * @return in_flag:订单状态（0：未提交:1：采购提交方案中:2：采购方案初审中:3：确认方案中:4：方案未通过:5：渠道经理审核中:6：徐总审核中:7：钟总审核中，8：方案已通过，9：已生成订单）
     * @return create_by:创建人
     * @return create_date:创建时间
     * @return file_address:文件地址
     * @return remark:大客户需求说明
     * @return sh_remark:审核意见
     * @return order_end_date:预销售单过期时间
     * @return update_by:处理人
     * @return modify_date:更新时间
     * @return order_num_list:会员信息详情
     * @return t_type:类型（1：普通订单，2：三农订单）
     * @return sheng_yu:判断是否可以生成订单（0:否 1:是）
     * @return product_info:商品列表
     * @product_info order_num:订单编号 product_num:商品编号 product_name:商品名称 specifications:规格 unit:单位 price:进价  md_price:门店价格 buy_num:购买数量 zhe_kou:折扣 final_price:最终价格 send_num:提交数量
     */
    public function demandOrderDetail(Request $request, BigCustomerOrderModel $bigCustomerOrderModel, BigCustomerOrderDetailModel $bigCustomerOrderDetailModel){
        $param = $request->param();
        $bigCustomerOrder = $bigCustomerOrderModel->where('id',$param['id'])->find();
        if (count($bigCustomerOrder) >= 1) {
            $bigCustomerOrder['product_info'] = $bigCustomerOrderDetailModel->where('order_num',$bigCustomerOrder['order_num'])->select();
        }
        $this->jkReturn('0000','大客户需求订单详情',$bigCustomerOrder);
    }


    /**
     * @title 大客户方案管理
     * @description 接口说明
     * @author gyl
     * @url /BigCustomer/programmeList
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:订单编号、会员名称
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1 other: desc:页数
     * @param name:start_date type:int require:0 default: 1 other: desc:搜索开始日期
     * @param name:end_date type:int require:0 default: 1 other: desc:搜索结束日期
     * @param name:t_type type:int require:0 default: 1 1other: desc:类型（1：普通订单，2：三农订单）
     * @param name:in_flag type:int require:0 default: 1 1other: desc:订单状态（0：未提交:1：采购提交方案中:2：采购方案初审中:3：确认方案中:4：方案未通过:5：渠道经理审核中:6：徐总审核中:7：钟总审核中，8：方案已通过，9：已生成订单）
     *
     *
     * @return bigCustomerOrderList:大客户方案列表@
     * @bigCustomerOrderList order_num:订单编号 member_name:会员名称 receive_name:收件人 receive_tel:收件手机 receive_city:市 remark:备注  create_by:创建人 order_end_date:过期时间 update_by:处理人 in_flag:订单状态（0：未提交:1：采购提交方案中:2：采购方案初审中:3：确认方案中:4：方案未通过:5：渠道经理审核中:6：徐总审核中:7：钟总审核中，8：方案已通过，9：已生成订单）
     * @return bigCustomerOrderCount:大客户方案数量
     *
     */
    public function programmeOrderList(Request $request,BigCustomerOrderModel $bigCustomerOrderModel){
        $field = "id,order_num,member_name,receive_name,receive_tel,receive_city,create_by,order_end_date,update_by,in_flag,create_date,t_type";
        $param = $request->param();
        $where = "1 = 1";
        if (!empty($param['search_key'])) {
            $where .= " and (order_num like  '%" . $param['search_key'] . "%' or member_name like '%" . $param['search_key'] . "%') ";
        }
        if (!empty($param['t_type'])) {
            $where .= " and t_type = $param[t_type] ";
        }
        if (!empty($param['in_flag'])) {
            $where .= " and in_flag = $param[in_flag] ";
        }
        if (!empty($param['start_date']) && !empty($param['end_date'])) {
            $where .= " and date_format(create_date,'%Y-%m-%d') >= date_format('" . $param['start_date'] . "','%Y-%m-%d')";
            $where .= " and date_format(create_date,'%Y-%m-%d') <= date_format('" . $param['end_date'] . "','%Y-%m-%d')";
        }
        $bigCustomerOrderList = $bigCustomerOrderModel
            ->field($field)
            ->where($where)
            ->limit($param["limit"]??10)
            ->page($param['page']??1)
            ->order('order_num','DESC')
            ->select();
        $bigCustomerOrderCount = $bigCustomerOrderModel->where($where)->count();
        $data = array(
            'bigCustomerOrderList'=>$bigCustomerOrderList,
            'bigCustomerOrderCount'=>$bigCustomerOrderCount
        );
        $this->jkReturn('0000','大客户方案管理',$data);
    }
    
    /**
     * @title 大客户方案详情
     * @description 接口说明
     * @author gyl
     * @url /BigCustomer/programmeDetail
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     * @param name:id type:int require:1 default: other: desc:客户订单id
     * @param name:select_fa type:int require:0 default: other: desc:方案编号
     *
     * @return remark:大客户需求说明
     * @return y_order_num:订单编号
     * @return member_:客户名称
     * @return receive_:收货人
     * @return receive_mobile:收货电话
     * @return receive_tel:收货手机
     * @return receive_province:省
     * @return receive_city:市
     * @return receive_area:区
     * @return receive_address:详细地址
     * @return invoice_header:发票标题
     * @return order_end_date:预销售单过期时间
     * @return file_address:文件地址
     * @return order_send_date:订单配送时间
     * @return order_remark:大客户订单备注
     * @return order_type:订单类型
     * @return productList:商品信息
     * @productList allCost:成本总额 allRetail:门店零售总额 allSale:方案销售总额 allProfit:总利润 allAmount:总数量 product:商品详情
     *
     */
    public function programmeOrderDetail(Request $request, BigCustomerOrderModel $orderModel, BigCustomerOrderDetailModel $orderDetailModel){
        $param = $request->param();
        $bigCustomerOrder = $orderModel->where('id',$param['id'])->find();
        if (count($bigCustomerOrder) >= 1) {
            $where = 'order_num="'.$bigCustomerOrder['order_num'].'"';
            if($bigCustomerOrder->select_fa>0){
                $where .= ' and select_fa='.$bigCustomerOrder->select_fa;
            }else{
                if(!empty($param['select_fa'])){
                    $where .= ' and select_fa='.$param['select_fa'];
                }
            }
            $product = $orderDetailModel->where($where)->order('select_fa','ASC')->select();
            $arr = [];
            if (count($product) >= 1) {
                $list = $this->array_group_by($product,'select_fa');
                foreach($list as $key => $productList){
                    $allCost = 0;//成本总额
                    $allRetail = 0;//门店零售总额
                    $allSale = 0;//方案销售总额
                    $allProfit = 0;//总利润
                    $allAmount = 0;//总数量
                    $brr=[];
                    foreach ($productList as $product_info) {
                        $sales = $product_info['final_price'] * $product_info['buy_num'];//方案销售总额
                        $prices = $product_info['price'] * $product_info['buy_num'];//成本总额
                        $retail = $product_info['md_price'] * $product_info['buy_num'];//门店零售总额
                        $profit = $sales - $prices;
                        $allCost += $prices;//成本总额
                        $allRetail += $retail;//门店零售总额
                        $allSale += $sales;//方案销售总额
                        $allProfit += $profit;//总利润
                        $allAmount += $product_info['buy_num'];//总数量
                        $selectFa = $product_info['select_fa'];
                    }
                    $brr['product'] = $productList;
                    $brr['select_fa'] = $selectFa;
                    $brr['allCost'] = round($allCost,2);//成本总额
                    $brr['allRetail'] = round($allRetail,2);//门店零售总额
                    $brr['allSale'] = round($allSale,2);//方案销售总额
                    $brr['allProfit'] = round($allProfit,2);//总利润
                    $brr['allAmount'] = round($allAmount,2);//总数量
                    $arr[] = $brr;
                }
            }
            $bigCustomerOrder['productList'] = $arr;
        }

        $this->jkReturn('0000','大客户订单详情',$bigCustomerOrder);
    }

    /**
     * @title 客户方案添加
     * @description 接口说明
     * @author gyl
     * @url /BigCustomer/programmeAdd
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     * @param name:order_num type:array require:1 default: other: desc:订单编号
     * @param name:customerProgramme type:array require:1 default: other: desc:数组
     * @param name:product_num type:string require:1 default: other: desc:商品编号
     * @param name:product_name type:string require:1 default: other: desc:商品名称
     * @param name:price type:string require:1 default: other: desc:单价
     * @param name:specifications type:string require:1 default: other: desc:规格
     * @param name:md_price type:string require:1 default: other: desc:门店价格
     * @param name:final_price type:string require:0 default: other: desc:建议销售价格
     * @param name:send_num type:string require:0 default: other: desc:数量
     *
     */
    public function programmeAdd(Request $request, BigCustomerOrderDetailModel $bigCustomerOrderDetailModel){
        $param = $request->param();
        if (empty($param['customerProgramme'])) {
            $this->jkReturn('-1004','数据为空');
        }
        $res = $bigCustomerOrderDetailModel->where(['order_num'=>$param['order_num']])->max('select_fa');
        foreach ($param['customerProgramme'] as $k => &$v) {
            $v['select_fa'] = $res+1;
            $v['order_num'] = $param['order_num'];
            unset($v['id']);
        }
        if(!$bigCustomerOrderDetailModel->allowField(true)->saveAll($param['customerProgramme'])){
            $this->jkReturn('-1004','客户方案添加失败');
        }
        $this->setAdminUserLog("新增","客户方案添加","wms_big_cust_order_detail");
        $this->jkReturn('0000','客户方案添加成功');
    }
    
    /**
     * @title 客户方案编辑
     * @description 接口说明
     * @author gyl
     * @url /BigCustomer/programmeEdit
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:order_num type:string require:1 default: other: desc:订单编号
     * @param name:select_fa type:string require:1 default: other: desc:方案编码
     * @param name:customerProgramme type:array require:1 default: other: desc:数组
     * @param name:id type:string require:1 default: other: desc:id
     * @param name:product_num type:string require:1 default: other: desc:商品编号
     * @param name:product_name type:string require:1 default: other: desc:商品名称
     * @param name:price type:string require:1 default: other: desc:单价
     * @param name:specifications type:string require:1 default: other: desc:规格
     * @param name:md_price type:string require:1 default: other: desc:门店价格
     * @param name:final_price type:string require:0 default: other: desc:建议销售价格
     * @param name:send_num type:string require:0 default: other: desc:数量
     *
     *
     */
    public function programmeEdit(Request $request, BigCustomerOrderDetailModel $bigCustomerOrderDetailModel){
        $param = $request->param();
        //-- 开启事物
        $bigCustomerOrderDetailModel->startTrans();
        $param = $request->param();
        if (empty($param['product_info'])||empty($param['order_num'])||empty($param['select_fa'])) {
            $this->jkReturn('-1004','数据为空');
        }
        if(!$bigCustomerOrderDetailModel->where(['order_num'=>$param['order_num'],'select_fa'=>$param['select_fa']])->delete()){
            $bigCustomerOrderDetailModel->rollback();
            $this->jkReturn('-1004','客户方案编辑失败');
        }
        foreach ($param['product_info'] as $k => &$v) {
            $v['select_fa'] = $param['select_fa'];
            $v['order_num'] = $param['order_num'];
            unset($v['id']);
        }
        if(!$bigCustomerOrderDetailModel->allowField(true)->saveAll($param['product_info'])){
            $bigCustomerOrderDetailModel->rollback();
            $this->jkReturn('-1004','客户方案编辑失败');
        }
        $this->setAdminUserLog("编辑","客户方案编辑","wms_big_cust_order_detail");
        $bigCustomerOrderDetailModel->commit();
        $this->jkReturn('0000','客户方案编辑成功');
    }
    /**
     * @title 客户方案删除
     * @description 接口说明
     * @author gyl
     * @url /BigCustomer/programmeDel
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:select_fa type:array require:1 default: other: desc:方案编号
     * @param name:order_num type:string require:1 default: other: desc:订单编号
     */
    public function programmeDel(Request $request, BigCustomerOrderDetailModel $bigCustomerOrderDetailModel){
        $param = $request->param();
        if (empty($param['order_num'])||empty($param['select_fa'])) {
            $this->jkReturn('-1004','数据为空');
        }
        $result = $bigCustomerOrderDetailModel->where(['order_num'=>$param['order_num'],'select_fa'=>$param['select_fa']])->delete();
        if($result<0){
            $this->jkReturn('-1004','删除客户方案失败');
        }
        $this->setAdminUserLog("删除","客户方案删除","wms_big_cust_order_detail");
        $this->jkReturn('0000','客户方案删除成功');
    }
    /**
     * @title 客户方案合并
     * @description 接口说明
     * @author gyl
     * @url /BigCustomer/programmeMerge
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:select_fa type:array require:1 default: other: desc:合并方案编号
     * @param name:order_num type:string require:1 default: other: desc:订单编号
     *
     *
     */
    public function programmeMerge(Request $request, BigCustomerOrderDetailModel $bigCustomerOrderDetailModel){
        $param = $request->param();
        if(count($param['select_fa'])>1){
            $selectFa = $param['select_fa'][0];
            unset($param['select_fa'][0]);
            $updateFa = $param['select_fa'];
            $res = $bigCustomerOrderDetailModel->where('select_fa','IN',$updateFa)->where('order_num="'+$param['order_num']+'"')->update(['select_fa'=>$selectFa]);
            if(!$res){
                $this->jkReturn('-1004','客户方案合并失败'); 
            }
        }
        $this->jkReturn('0000','客户方案合并成功');
    }
/**
     * @title 大客户订单待审核列表
     * @description 接口说明
     * @author gyl
     * @url /BigCustomer/programmeReviewedList
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:订单编号、会员名称
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1 other: desc:页数
     * @param name:start_date type:int require:0 default: 1 other: desc:搜索开始日期
     * @param name:end_date type:int require:0 default: 1 other: desc:搜索结束日期
     * @param name:t_type type:int require:0 default: 1 1other: desc:类型（1：普通订单，2：三农订单）
     * @param name:sheng_yu type:int require:0 default: 1 1other: desc:类型 (0：有剩余，1：无剩余）
     * @param name:in_flag type:int require:0 default: 1 1other: desc:订单状态（0：未提交:1：采购提交方案中:2：采购方案初审中:3：确认方案中:4：方案未通过:5：渠道经理审核中:6：徐总审核中:7：钟总审核中，8：方案已通过，9：已生成订单）
     *
     *
     * @return programmeReviewedList:大客户订单待审核列表@
     * @programmeReviewedList order_num:订单编号 in_flag:订单状态（0：未提交:1：采购提交方案中:2：采购方案初审中:3：确认方案中:4：方案未通过:5：渠道经理审核中:6：徐总审核中:7：钟总审核中，8：方案已通过，9：已生成订单）update_by:处理人 member_name:会员名称 receive_name:收件人 receive_tel:收件手机 receive_city:市  create_by:创建人 order_end_date:过期时间 t_type:类型（1：普通订单，2：三农订单）
     * @return programmeReviewedCount:大客户订单数量
     *
     */
    public function programmeReviewedList(Request $request,BigCustomerOrderModel $bigCustomerOrderModel){
        $field = "id,order_num,in_flag,member_name,receive_name,receive_tel,receive_city,create_by,order_end_date,t_type,sheng_yu";
        $param = $request->param();
        $where = "1 = 1 and in_flag<> 0 and in_flag<>1 and in_flag<>4 and in_flag<>8 and in_flag<>9 ";
        if (!empty($param['search_key'])) {
            $where .= " and (order_num like  '%" . $param['search_key'] . "%' or member_name like '%" . $param['search_key'] . "%') ";
        }
        if (!empty($param['t_type'])) {
            $where .= " and t_type = $param[t_type] ";
        } 
        if (!empty($param['in_flag'])) {
            $where .= " and in_flag = $param[in_flag] ";
        }
        if (!empty($param['start_date']) && !empty($param['end_date'])) {
            $where .= " and date_format(create_date,'%Y-%m-%d') >= date_format('" . $param['start_date'] . "','%Y-%m-%d')";
            $where .= " and date_format(create_date,'%Y-%m-%d') <= date_format('" . $param['end_date'] . "','%Y-%m-%d')";
        }
        $demandOrderList = $bigCustomerOrderModel
            ->field($field)
            ->where($where)
            ->limit($param["limit"]??10)
            ->page($param['page']??1)
            ->order('order_num','DESC')
            ->select();
        $demandOrderCount = $bigCustomerOrderModel->where($where)->count();
        $data = array(
            'demandOrderList'=>$demandOrderList,
            'demandOrderCount'=>$demandOrderCount
        );
        $this->jkReturn('0000','大客户订单待审核列表',$data);
    }

    /**
     * @title 客户方案状态更新
     * @description 接口说明
     * @author gyl
     * @url /BigCustomer/programmeReviewed
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:in_flag type:int require:1 default: other: desc:订单状态（0：未提交;1：采购提交方案中;2：采购方案初审中（已申请，初审中）;3：确认方案中（初审通过）;4：方案未通过;5：渠道经理审核中（确认方案通过，审核中）;6：徐总审核中（确认方案审核通过，二审中）;7：钟总审核中（提交到总裁）;8：方案已通过;9：已生成订单）
     * @param name:select_fa type:string require:1 default: other: desc:选择的方案
     * @param name:id type:int require:1 default: other: desc:客户方案id
     * @param name:sh_remark type:int require:1 default: other: desc:审核意见
     * @param name:order_num type:string require:1 default: other: desc:订单编号
     *
     */
    public function programmeReviewed(Request $request, BigCustomerOrderModel $bigOrderModel,BigCustomerOrderDetailModel $bigCustomerOrderDetailModel){
        $param = $request->param();
        $bigCustomerOrder = $bigOrderModel->where('id',$param['id'])->find();
        if(!$bigCustomerOrder){
            $this->jkReturn('-1004','数据为空');
        }
        $bigCustomerProgramme = $bigCustomerOrderDetailModel->where('order_num',$bigCustomerOrder->order_num)->find();
        if(!$bigCustomerProgramme){
            $this->jkReturn('-1004','请至少添加一个方案');
        }
        $data['update_by'] = 'admin123';
        if (!empty($param['sh_remark'])) {
            $data['sh_remark'] = $bigCustomerOrder['sh_remark'] . ',' . $param['sh_remark'];
        }
        $data['in_flag'] = $param['in_flag'];
        if(isset($param['select_fa'])){
            $data['select_fa'] = $param['select_fa'];
        }
        $where = ['id'=>$param['id']];
        $result = $bigOrderModel->allowField(true)->save($data,$where);
        if($result){
            $this->setAdminUserLog("审核","客户方案状态更新","wms_big_cust_order",$param['id']);
            $sh_info = $bigOrderModel->getApplyStatusAttr($param['in_flag']);
            $this->setReviewedLog("大客户",$bigCustomerOrder->order_num,$sh_info);
            $this->jkReturn('0000','操作成功');
        }else{
            $this->jkReturn('-1004','操作失败');
        }
    }

    /**
     * @title 大客户销售订单模板下载
     * @description 接口说明
     * @author gyl
     * @url /BigCustomer/BigCustomerDownload
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     */
    public function BigCustomerDownload(SystemConfigModel $systemConfigModel){
        $code = 'base_url';
        $system = $systemConfigModel->where('code',$code)->find();
        $downUrl = $system->value . '/import/BigCustomer.xlsx';
        $this->jkReturn('0000','大客户销售订单模板',$downUrl);
    }


    /**
     * @title 预销售订单导入
     * @description 接口说明
     * @author gyl
     * @url /BigCustomer/BigCustomerImport
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     */
    public function BigCustomerImport(BigCustomerOrderModel $bigCustomerOrderModel,BigCustomerOrderDetailModel $bigCustomerOrderDetailModel){
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
            $this->setAdminUserLog("导入","预销售订单导入","wms_big_cust_order");
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
            $msg = "导入成功</br>成功插入预销售订单：" . $a;
            $this->jkReturn('0000','预销售订单导入成功',$msg);
        }
    }
    /**
     * @title 大客户订单生成详情页
     * @description 接口说明
     * @author gpf
     * @url /BigCustomer/BigCustomerOrderShDetail
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     * @param name:id type:int require:1 default: other: desc:预售订单id
     *
     * @return remark:大客户需求说明
     * @return y_order_num:订单编号
     * @return member_:客户名称
     * @return receive_:收货人
     * @return receive_mobile:收货电话
     * @return receive_tel:收货手机
     * @return receive_province:省
     * @return receive_city:市
     * @return receive_area:区
     * @return receive_address:详细地址
     * @return invoice_header:发票标题
     * @return order_end_date:预销售单过期时间
     * @return file_address:文件地址
     * @return order_send_date:订单配送时间
     * @return order_remark:大客户订单备注
     * @return order_type:订单类型
     * @return productList:商品信息
     * @productList allCost:成本总额 allRetail:门店零售总额 allSale:方案销售总额 allProfit:总利润 allAmount:总数量 product:商品详情 send_num 已使用数量 pro_num 商品总数量
     * @return orderList:已生成订单列表 
     * @orderList order_num:订单编号 receive_:收货人 receive_mobile:收货电话 receive_province:省 receive_city:市 receive_area:区 receive_address:详细地址
     */
    public function bigCustomerOrderShDetail(Request $request, BigCustomerOrderModel $orderModel, BigCustomerOrderDetailModel $orderDetailModel,BigCustomerOrderSHModel $orderSHModel){
        $param = $request->param();
        $bigCustomerOrder = $orderModel->where('id',$param['id'])->find();
        if (count($bigCustomerOrder) >= 1) {
            $where = 'order_num="'.$bigCustomerOrder['order_num'].'" and select_fa='.$bigCustomerOrder->select_fa;
            $product = $orderDetailModel->where($where)->order('select_fa','ASC')->select();
            $arr = [];
            if (count($product) >= 1) {
                $list = $this->array_group_by($product,'select_fa');
                foreach($list as $key => $productList){
                    $allCost = 0;//成本总额
                    $allRetail = 0;//门店零售总额
                    $allSale = 0;//方案销售总额
                    $allProfit = 0;//总利润
                    $allAmount = 0;//总数量
                    $brr=[];
                    foreach ($productList as $product_info) {
                        $sales = $product_info['final_price'] * $product_info['buy_num'];//方案销售总额
                        $prices = $product_info['price'] * $product_info['buy_num'];//成本总额
                        $retail = $product_info['md_price'] * $product_info['buy_num'];//门店零售总额
                        $profit = $sales - $prices;
                        $allCost += $prices;//成本总额
                        $allRetail += $retail;//门店零售总额
                        $allSale += $sales;//方案销售总额
                        $allProfit += $profit;//总利润
                        $allAmount += $product_info['buy_num'];//总数量
                        $selectFa = $product_info['select_fa'];
                    }
                    $brr['product'] = $productList;
                    $brr['select_fa'] = $selectFa;
                    $brr['allCost'] = round($allCost,2);//成本总额
                    $brr['allRetail'] = round($allRetail,2);//门店零售总额
                    $brr['allSale'] = round($allSale,2);//方案销售总额
                    $brr['allProfit'] = round($allProfit,2);//总利润
                    $brr['allAmount'] = round($allAmount,2);//总数量
                    $arr[] = $brr;
                }
            }
            $bigCustomerOrder['productList'] = $arr;
            //-- 已生成订单列表
            $bigCustomerOrder['orderList'] = $orderSHModel->where('y_order_num',$bigCustomerOrder->order_num)->select();
        }
        $this->jkReturn('0000','大客户生成订单详情',$bigCustomerOrder);
    }

    /**
     * @title 大客户订单生成
     * @description 接口说明
     * @author gyl
     * @url /BigCustomer/BigCustomerOrderAdd
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:remark type:string require:1 default: other: desc:大客户需求说明
     * @param name:t_type type:int require:0 default: 1 1other: desc:类型（1：普通订单，2：三农订单）
     * @param name:y_order_num type:string require:1 default: other: desc:预售订单编号
     * @param name:member_name type:string require:1 default: other: desc:客户名称
     * @param name:receive_name type:string require:1 default: other: desc:收货人
     * @param name:receive_mobile type:string require:1 default: other: desc:收货手机
     * @param name:receive_tel type:string require:1 default: other: desc:收货座机
     * @param name:receive_province type:string require:1 default: other: desc:省
     * @param name:receive_city type:string require:1 default: other: desc:市
     * @param name:receive_area type:string require:1 default: other: desc:区
     * @param name:receive_address type:string require:1 default: other: desc:详细地址
     * @param name:invoice_header type:string require:1 default: other: desc:发票标题
     * @param name:order_end_date type:string require:1 default: other: desc:预销售单过期时间
     * @param name:file_address type:string require:1 default: other: desc:文件地址
     * @param name:order_send_date type:string require:1 default: other: desc:订单配送时间
     * @param name:order_remark type:string require:1 default: other: desc:大客户订单备注
     * @param name:order_type type:string require:1 default: other: desc:订单类型
     * @param name:product_info type:array require:0 default: other: desc:商品信息
     * @param name:id type:string require:1 default: other: desc:id
     * @param name:product_num type:string require:1 default: other: desc:商品编号
     * @param name:product_name type:string require:1 default: other: desc:商品名称
     * @param name:price type:string require:1 default: other: desc:进价
     * @param name:specifications type:string require:1 default: other: desc:规格
     * @param name:md_price type:string require:1 default: other: desc:门店价格
     * @param name:zhekou type:string require:1 default: other: desc:折扣率
     * @param name:final_price type:string require:0 default: other: desc:最终销售价格
     * @param name:buy_num type:string require:0 default: other: desc:购买数量
     *
     */
    public function BigCustomerOrderAdd(Request $request, BigCustomerOrderSHModel $orderSHModel,BigCustomerOrderDetailSHModel $orderDetailSHModel){
        $param = $request->param();
        $param['order_num'] = 'D' . date('YmdHis',time());//订单编号
        if (empty($param['t_type'])) {
            $param['t_type'] = 1;
        }
        $result = $orderSHModel->allowField(true)->save($param);
        if($result){
            $orderSHId = $orderSHModel->getLastInsID();
            $this->setAdminUserLog("新增","大客户订单生成","wms_big_cust_order_sh",$orderSHId);
            $data['all_money'] = 0;
            if (!empty($param['product_info'])) {
                foreach ($param['product_info'] as &$product_info) {
                    $product_info['order_num'] = $param['order_num'];
                    $allPrice = $param['final_price'] * $param['buy_num'];
                    $data['all_money'] += $allPrice;
                }
            }
            $orderDetailSHModel->allowField(true)->saveAll($param['product_info']);
            $this->jkReturn('0000','添加成功');
        }else{
            $this->jkReturn('-1004','添加失败');
        }
    }

    /**
     * @title 大客户订单审核管理
     * @description 接口说明
     * @author gyl
     * @url /BigCustomer/orderList
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:订单编号、会员名称
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1 other: desc:页数
     * @param name:start_date type:int require:0 default: 1 other: desc:搜索开始日期
     * @param name:end_date type:int require:0 default: 1 other: desc:搜索结束日期
     *
     *
     * @return orderList:大客户订单审核列表@
     * @orderList order_num:订单编号 member_name:客户名称 receive_name:收件人 receive_tel:收件手机 receive_city:市 all_money:总金额  create_by:创建人 create_date:创建时间 order_send_date:配送时间 in_flag:审核状态（0：未审核；1：审核通过；2：审核未通过；3：已撤销） stock_out_flag：是否出库 hx_state：核销状态(0：未核销，1：已核销)
     * @return orderCount:大客户订单审核数量
     *
     */
    public function orderList(Request $request,BigCustomerOrderSHModel $orderSHModel){
        $field = "id,y_order_num,order_num,member_name,receive_name,receive_tel,receive_city,all_money,create_by,create_date,order_send_date,in_flag,stock_out_flag,hx_state";
        $param = $request->param();
        $where = "1 = 1";
        if (!empty($param['search_key'])) {
            $where .= " and (order_num like  '%" . $param['search_key'] . "%' or member_name like '%" . $param['search_key'] . "%') ";
        }
        if (!empty($param['start_date']) && !empty($param['end_date'])) {
            $where .= " and date_format(create_date,'%Y-%m-%d') >= date_format('" . $param['start_date'] . "','%Y-%m-%d')";
            $where .= " and date_format(create_date,'%Y-%m-%d') <= date_format('" . $param['end_date'] . "','%Y-%m-%d')";
        }
        $orderList = $orderSHModel
            ->field($field)
            ->where($where)
            ->limit($param["limit"]??10)
            ->page($param['page']??1)
            ->order('order_num','DESC')
            ->select();
        $orderCount = $orderSHModel->where($where)->count();
        $data = array(
            'orderList'=>$orderList,
            'orderCount'=>$orderCount
        );
        $this->jkReturn('0000','大客户订单审核管理',$data);
    }

    /**
     * @title 大客户订单状态更新
     * @description 接口说明
     * @author gyl
     * @url /BigCustomer/BigCustomerOrderReviewed
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:in_flag type:int require:1 default: other: desc:审核状态（0：未审核；1：审核通过；2：审核未通过；3：已撤销）
     * @param name:id type:int require:1 default: other: desc:客户订单id
     * @param name:remark type:int require:1 default: other: desc:审核意见
     * @param name:order_num type:string require:1 default: other: desc:订单编号
     *
     */
    public function BigCustomerOrderReviewed(Request $request, BigCustomerOrderSHModel $orderSHModel){
        $param = $request->param();
        $bigCustomerOrder = $orderSHModel->where('id',$param['id'])->find();
        $data['update_by'] = 'admin123';
        $data['remark'] = $bigCustomerOrder['remark'] . ',' . $param['remark'];
        $data['in_flag'] = $param['in_flag'];
        $where = ['id'=>$param['id']];
        $result = $orderSHModel->allowField(true)->save($data,$where);
        if($result){
            $this->setAdminUserLog("审核","大客户订单状态更新","wms_big_cust_order_sh",$param['id']);
            $this->jkReturn('0000','审核成功');
        }else{
            $this->jkReturn('-1004','审核失败');
        }
    }

    /**
     * @title 大客户订单核销
     * @description 接口说明
     * @author gyl
     * @url /BigCustomer/orderVerification
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:hx_state type:int require:1 default: other: desc:核销状态（0：未核销；1：已核销）
     * @param name:id type:int require:1 default: other: desc:客户订单id
     * @param name:hx_invoice type:int require:1 default: other: desc:核销发票
     * @param name:hx_invoice_price type:string require:1 default: other: desc:核销金额
     * @param name:hx_invoice_date type:string require:1 default: other: desc:核销时间
     *
     */
    public function orderVerification(Request $request, BigCustomerOrderSHModel $orderSHModel){
        $param = $request->param();
        $where = ['id'=>$param['id']];
        $result = $orderSHModel->allowField(true)->save($param,$where);
        if($result){
            $this->setAdminUserLog("审核","大客户订单核销","wms_big_cust_order_sh",$param['id']);
            $this->jkReturn('0000','审核成功');
        }else{
            $this->jkReturn('-1004','审核失败');
        }
    }
    
    /**
     * @title 商品列表
     * @description 接口说明
     * @author gyl
     * @url /BigCustomer/product
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:varchar require:0 default: other: desc:查询值
     *
     * @return product_num:商品编号
     * @return product_name:商品名称
     * @return price:单价
     * @return specifications_num:规格
     * @return unit:计量单位
     * @return shelf_month:保质期
     * @return carton:箱规
     */
    public function product(Request $request,ProductModel $productModel){
        $param = $request->param();
        $field = "product_num,product_name,specifications_num as specifications,unit,shelf_month,carton";
        $where = "disabled = 1";
        if (!empty($param['search_key'])) {
            $where .= " and (product_num like '%" . $param['search_key'] . "%' or product_name like '%" . $param['search_key'] . "%') ";
        }
        $productList = $productModel->field($field)->where($where)->select();
        $this->jkReturn('0000','商品列表',$productList);
    }

    public static function array_group_by($arr, $key){
        $grouped = array();
        foreach ($arr as $value) {
            $grouped[$value[$key]][] = $value;
        }
        if (func_num_args() > 2) {
            $args = func_get_args();
            foreach ($grouped as $key => $value) {
                $parms = array_merge($value, array_slice($args, 2, func_num_args()));
                $grouped[$key] = call_user_func_array('array_group_by', $parms);
            }
        }
        return $grouped;
    }
}