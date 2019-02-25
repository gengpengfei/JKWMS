<?php
/**
 * Created by PhpStorm.
 * User: guanyl
 * Date: 2018/11/9
 * Time: 14:16
 */

namespace app\Api\controller;
use app\api\model\ProductModel;
use app\api\model\ProOfflineModel;
use think\Request;

/**
 * @title 商品下架申请
 * @description 接口说明
 * @group 商品管理
 * @header name:key require:1 default: desc:秘钥(区别设置)
 */

class ProOffline extends Common
{
    /**
     * @title 商品下架列表
     * @description 接口说明
     * @author gyl
     * @url /ProOffline/index
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:商品编号、商品名称
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1other: desc:页数
     * @param name:apply_state type:int require:0 default:10 other: desc:审核状态（1：终审通过；2：二审通过，终审中；3：初审通过，二审中；4：已申请，初审中；5：已拒绝）
     *
     * @return proOfflineList:商品下架列表@
     * @proOfflineList shop_id:商户id  branch_id:机构id  product_num:商品编号 product_name:商品名称 product_type:商品类型 price:进价 sale_price:销售价 shelf_month:保质期 specifications_num:商品规格 apply_by:申请人 apply_date:申请时间 apply_state:申请状态(1：终审通过；2：二审通过，终审中；3：初审通过，二审中；4：已申请，初审中；5：已拒绝）
     * @return proOfflineCount:商品数量
     */
    public function index(Request $request, ProOfflineModel $proOfflineModel, ProductModel $productModel)
    {
        $field = 'o.*,p.id as product_id,p.product_num,p.product_name,p.product_type,p.price,sale_price,p.shelf_month,p.specifications_num';
        $param = $request->param();
        $where = "disabled = 1";
        if (!empty($param['search_key'])) {
            $where .= " and (product_num like  '%" . $param['search_key'] . "%' or product_name like '%" . $param['search_key'] . "%') ";
        }
        if (!empty($param['apply_state'])) {
            $where .= " and o.apply_state = $param[apply_state]";
        }
        $proOfflineList = $productModel
            ->field($field)
            ->alias('p')
            ->where($where)
            ->join('wms_product_offline o','p.product_num=o.product_num','left')
            ->limit($param["limit"]??10)
            ->page($param['page']??1)
            ->order('id','DESC')
            ->select();
        $proOfflineCount = $productModel->alias('p')->where($where)->join('wms_product_offline o','p.product_num=o.product_num','left')->count();
        $data = array(
            'proOfflineList'=>$proOfflineList,
            'proOfflineCount'=>$proOfflineCount
        );
        $this->jkReturn('0000','商品下架列表',$data);
    }

    /**
     * @title 商品申请下架
     * @description 接口说明
     * @author gyl
     * @url /ProOffline/applyUnder
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:product_num type:string require:1 default: other: desc:商品编号
     * @param name:product_name type:string require:1 default: other: desc:商品名称
     *
     */
    public function applyUnder(Request $request, ProOfflineModel $proOfflineModel){
        $param = $request->param();
        $data = array(
            'product_num'=>$param['product_num'],
            'product_name'=>$param['product_name'],
            'apply_by'=>'admin123',
            'apply_date'=>date('Y-m-d H:i:s'),
            'apply_state'=>4,
        );
        $result = $proOfflineModel->allowField(true)->save($data);
        if($result){
            $pro_offline_id = $proOfflineModel->getLastInsID();
            $this->setAdminUserLog("新增","申请下架","wms_product_offline",$pro_offline_id);
            $this->jkReturn('0000','下架成功');
        }else{
            $this->jkReturn('-1004','下架失败');
        }
    }

    /**
     * @title 商品下架状态更新
     * @description 接口说明
     * @author gyl
     * @url /ProOffline/reviewed
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:apply_state type:int require:1 default: other: desc:审核状态（1：终审通过；2：二审通过，终审中；3：初审通过，二审中；4：已申请，初审中；5：已拒绝）
     * @param name:id type:int require:1 default: other: desc:下架id
     *
     */
    public function reviewed(Request $request, ProOfflineModel $proOfflineModel){
        $param = $request->param();
        $data['apply_state'] = $param['apply_state'];
        $where = ['id'=>$param['id']];
        if ($data['apply_state'] == 3) {
            $data['check_by'] = 'admin456';
            $data['check_date'] = date('Y-m-d H:i:s');
        }
        if ($data['apply_state'] == 2) {
            $data['final_check_by'] = 'admin789';
            $data['final_check_date'] = date('Y-m-d H:i:s');
        }
        if ($data['apply_state'] == 1) {
            $data['final_check_by'] = 'admin000';
            $data['final_check_date'] = date('Y-m-d H:i:s');
        }
        $result = $proOfflineModel->allowField(true)->save($data,$where);
        if($result){
            $this->setAdminUserLog("审核","更新商品下架状态","wms_product_offline",$param['id']);
            $this->jkReturn('0000','审核成功');
        }else{
            $this->jkReturn('-1004','审核失败');
        }
    }

    /**
     * @title 商品下架拒绝申请
     * @description 接口说明
     * @author gyl
     * @url /ProOffline/refuse
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:int require:1 default: other: desc:下架id
     *
     */
    public function refuse(Request $request, ProOfflineModel $proOfflineModel){
        $param = $request->param();
        $offlineInfo = $proOfflineModel->where('id',$param['id'])->find();
        $data['apply_state'] = 5;
        $where = ['id'=>$param['id']];
        if (empty($offlineInfo->check_date)) {
            $data['check_by'] = 'admin456';
            $data['check_date'] = date('Y-m-d H:i:s');
        }else {
            $data['final_check_by'] = 'admin789';
            $data['final_check_date'] = date('Y-m-d H:i:s');
        }
        $result = $proOfflineModel->update($data,$where);
        if($result){
            $this->setAdminUserLog("审核","商品下架拒绝申请","wms_product_offline",$param['id']);
            $this->jkReturn('0000','商品下架拒绝申请成功');
        }else{
            $this->jkReturn('-1004','商品下架拒绝申请失败');
        }
    }

    /**
     * @title 商品下架申请删除
     * @description 接口说明
     * @author gyl
     * @url /ProOffline/delete
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:int require:1 default: other: desc:下架id
     *
     */
    public function delete(Request $request, ProOfflineModel $proOfflineModel){
        $param = $request->param();
        $offlineInfo = $proOfflineModel->where('id',$param['id'])->find();
        if (count($offlineInfo) <= 0) {
            $this->jkReturn('-1004',"数据不存在");
        }
        if(!$proOfflineModel->where(['id'=>$param['id']])->delete()){
            $this->jkReturn('-1004',"删除商品下架删除失败,id为'$param[id]'");
        }
        $this->setAdminUserLog("删除","删除商品下架申请","wms_product_offline",$param['id']);
        $this->jkReturn('0000','删除商品下架更新成功');
    }

}