<?php
/**
 * Created by PhpStorm.
 * User: guanyl
 * Date: 2018/11/9
 * Time: 10:38
 */
namespace app\Api\controller;
use app\api\model\ProductFruitModel;
use think\Request;

/**
 * @title 商品转换管理
 * @description 接口说明
 * @group 商品管理
 * @header name:key require:1 default: desc:秘钥(区别设置)

 */

class ProFruit extends Common
{
    /**
     * @title 商品转换列表
     * @description 接口说明
     * @author gyl
     * @url /ProFruit/index
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:商品编码
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1other: desc:页数
     *
     * @return proFruitList:商品转换列表@
     * @proFruitList shop_id:商户id  branch_id:机构id  product_num:商品编码 product_name:商品名称 pro_code_mix:混合商品编号 pro_name_mix:混合商品名称 times:单果规格 remark:备注 flag:商品标识0:采购与销售一致；1：仅是销售商品；2：仅是采购商品
     * @return proFruitCount:商品转换数量
     */
    public function index(Request $request, ProductFruitModel $productFruitModel)
    {
        $param = $request->param();
        $where = "1 = 1";
        if (!empty($param['search_key'])) {
            $where .= " and (product_num like  '%" . $param['search_key'] . "%' or product_name like '%" . $param['search_key'] . "%' or pro_code_mix like '%" . $param['search_key'] . "%' or pro_name_mix like '%" . $param['search_key'] . "%') ";
        }
        $proFruitList = $productFruitModel
            ->where($where)->limit($param["limit"]??10)->page($param['page']??1)->select();
        $proFruitCount = $productFruitModel->where($where)->count();
        $data = array(
            'proFruitList'=>$proFruitList,
            'proFruitCount'=>$proFruitCount
        );
        $this->jkReturn('0000','商品转换列表',$data);
    }

    /**
     * @title 商品转换详情
     * @description 接口说明
     * @author gyl
     * @url /ProFruit/ProFruitDetail
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:varchar require:1 default: other: desc:商品转换id
     *
     * @return product_num:商品编号
     * @return product_name:商品名称
     * @return pro_code_mix:混合商品编号
     * @return pro_name_mix:混合商品名称
     * @return times:单果规格
     * @return remark:备注
     * @return flag:商品标识0:采购与销售一致；1：仅是销售商品；2：仅是采购商品
     */
    public function ProFruitDetail(Request $request,ProductFruitModel $productFruitModel){
        $param = $request->param();
        $productFruit = $productFruitModel->where(['id'=>$param['id']])->find();
        $this->jkReturn('0000','商品转换详情',$productFruit);
    }

    /**
     * @title 商品转换添加
     * @description 接口说明
     * @author gyl
     * @url /ProFruit/proFruitAdd
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:product_num type:string require:1 default: other: desc:商品编码
     * @param name:product_name type:string require:1 default: other: desc:商品名称
     * @param name:pro_code_mix type:string require:1 default: other: desc:混合商品编号
     * @param name:pro_name_mix type:string require:1 default: other: desc:混合商品名称
     * @param name:times type:string require:1 default: other: desc:单果规格
     * @param name:remark type:string require:0 default: other: desc:备注
     * @param name:flag type:int require:0 default: other: desc:商品标识0:采购与销售一致；1：仅是销售商品；2：仅是采购商品
     *
     */
    public function proFruitAdd(Request $request, ProductFruitModel $productFruitModel){
        $param = $request->param();
        $result = $productFruitModel->allowField(true)->save($param);
        if($result){
            $product_fruit_id = $productFruitModel->getLastInsID();
            $this->setAdminUserLog("新增","添加商品转换","wms_product_fruit",$product_fruit_id);
            $this->jkReturn('0000','添加商品转换成功');
        }else{
            $this->jkReturn('-1004','添加商品转换失败');
        }
    }

    /**
     * @title 商品转换更新
     * @description 接口说明
     * @author gyl
     * @url /ProFruit/proFruitEdit
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:int require:1 default: other: desc:商品转换id
     * @param name:product_num type:string require:1 default: other: desc:商品编码
     * @param name:product_name type:string require:1 default: other: desc:商品名称
     * @param name:pro_code_mix type:string require:1 default: other: desc:混合商品编号
     * @param name:pro_name_mix type:string require:1 default: other: desc:混合商品名称
     * @param name:times type:string require:1 default: other: desc:单果规格
     * @param name:remark type:string require:0 default: other: desc:备注
     * @param name:flag type:int require:0 default: other: desc:商品标识0:采购与销售一致；1：仅是销售商品；2：仅是采购商品
     *
     */
    public function proFruitEdit(Request $request, ProductFruitModel $productFruitModel){
        $param = $request->param();
        $proFruitDetail = $productFruitModel->where('id',$param['id'])->select();
        if (count($proFruitDetail) <= 0) {
            $this->jkReturn('-1004','数据不存在');
        }
        $where = ['id'=>$param['id']];
        $result = $productFruitModel->allowField(true)->save($param,$where);
        if($result){
            $this->setAdminUserLog("更新","商品转换更新","wms_product_fruit",$param['id']);
            $this->jkReturn('0000','商品转换更新成功');
        }else{
            $this->jkReturn('-1004','商品转换更新失败');
        }
    }

    /**
     * @title 商品转换删除
     * @description 接口说明
     * @author gyl
     * @url /ProFruit/proFruitDel
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:array require:0 default: other: desc:商品转换id(删除或批量删除)
     *
     */
    public function proFruitDel(Request $request, ProductFruitModel $productFruitModel){
        $param = $request->param();
        //多个删除
        foreach ($param['id'] as $v){
            if(!$productFruitModel->where(['id'=>$v])->delete()){
                $this->jkReturn('-1004',"商品转换删除失败,id为'$v'");
            }
            $this->setAdminUserLog("删除","删除商品转换","wms_product_fruit",$v);
        }
        $this->jkReturn('0000','商品转换删除成功');
    }
}