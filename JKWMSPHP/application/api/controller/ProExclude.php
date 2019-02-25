<?php
/**
 * Created by PhpStorm.
 * User: guanyl
 * Date: 2018/11/8
 * Time: 17:22
 */

namespace app\Api\controller;
use app\api\model\ProExcludeModel;
use think\Request;

/**
 * @title 不推送库存管理
 * @description 接口说明
 * @group 商品管理
 * @header name:key require:1 default: desc:秘钥(区别设置)

 */

class ProExclude extends Common
{
    /**
     * @title 不推送库存列表
     * @description 接口说明
     * @author gyl
     * @url /ProExclude/index
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:search_key type:string require:0 default: other: desc:商品编码
     * @param name:limit type:int require:0 default:10 other: desc:数量
     * @param name:page type:int require:0 default: 1other: desc:页数
     *
     * @return proExclude:不推送库存列表@
     * @proExclude product_num:商品编码
     * @return proExcludeCount:不推送库存数量
     */
    public function index(Request $request, ProExcludeModel $proExcludeModel){
        $param = $request->param();
        $where = "1 = 1";
        if (!empty($param['search_key'])) {
            $where .= " and product_num like  '%" . $param['search_key'] . "%' ";
        }
        $proExclude = $proExcludeModel
            ->where($where)->limit($param["limit"]??10)->page($param['page']??1)->select();
        $proExcludeCount = $proExcludeModel->where($where)->count();
        $data = array(
            'proExclude'=>$proExclude,
            'proExcludeCount'=>$proExcludeCount
        );
        $this->jkReturn('0000','不推送库存列表',$data);
    }

    /**
     * @title 商品编码添加
     * @description 接口说明
     * @author gyl
     * @url /ProExclude/proExcludeAdd
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:product_num type:string require:1 default: other: desc:商品编码
     *
     */
    public function proExcludeAdd(Request $request, ProExcludeModel $proExcludeModel){
        $param = $request->param();
        $data = array(
            'product_num'=>$param['product_num']
        );
        $proExclude = $proExcludeModel->where($data)->find();
        if (count($proExclude) >= 1) {
            $this->jkReturn('-1004','此商品已存在');
        }
        $result = $proExcludeModel->allowField(true)->save($param);
        if($result){
            $proExcludeId = $proExcludeModel->getLastInsID();
            $this->setAdminUserLog("新增","添加不推送库存商品编码","wms_product_exclude",$proExcludeId);
            $this->jkReturn('0000','添加成功');
        }else{
            $this->jkReturn('-1004','添加失败');
        }
    }

    /**
     * @title 商品编码更新
     * @description 接口说明
     * @author gyl
     * @url /ProExclude/proExcludeEdit
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:int require:1 default: other: desc:id
     * @param name:product_num type:string require:1 default: other: desc:商品编码
     *
     */
    public function proExcludeEdit(Request $request, ProExcludeModel $proExcludeModel){
        $param = $request->param();
        $proExcludeDetail = $proExcludeModel->where('id',$param['id'])->select();
        if (count($proExcludeDetail) <= 0) {
            $this->jkReturn('-1004','数据不存在');
        }
        $data = array(
            'product_num'=>$param['product_num']
        );
        $where = ['id'=>$param['id']];
        $result = $proExcludeModel->allowField(true)->save($data,$where);
        if($result){
            $this->setAdminUserLog("更新","更新商品编码","wms_product_exclude",$param['id']);
            $this->jkReturn('0000','更新成功');
        }else{
            $this->jkReturn('-1004','更新失败');
        }
    }
    /**
     * @title 商品编码删除
     * @description 接口说明
     * @author gyl
     * @url /ProExclude/proExcludeDel
     * @method POST
     *
     * @header name:device require:1 default: desc:设备号
     *
     * @param name:id type:int require:1 default: other: desc:不推送库存id
     *
     */
    public function proExcludeDel(Request $request, ProExcludeModel $proExcludeModel){
        $param = $request->param();
        if(!$proExcludeModel->where(['id'=>$param['id']])->delete()){
            $this->jkReturn('-1004','删除失败');
        }
        $this->setAdminUserLog("删除","删除商品编码","wms_product_exclude",$param['id']);
        $this->jkReturn('0000','删除成功');
    }
}