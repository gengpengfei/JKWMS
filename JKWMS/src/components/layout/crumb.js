import { Breadcrumb } from 'antd';
import React, { Component } from 'react';
export default class CrumbLayout extends Component {
    crumbJson = {
        "home": ["管理中心", ""],
        "goods": ["商品管理", "商品列表"],
        "goodsBindSupplier": ["商品管理", "绑定供应商"],
        "goodsType": ["商品管理", "商品类型管理"],
        "goodsTypeAdd": ["商品管理", "添加商品类型"],
        "goodsTypeAddChild": ["商品管理", "添加商品类型子类"],
        "goodsTypeEdit": ["商品管理", "编辑商品类型"],
        "proExclude": ["商品管理", "不推送库存商品管理"],
        "proExcludeAdd": ["商品管理", "不推送库存商品添加"],
        "proExcludeEdit": ["商品管理", "不推送库存商品编辑"],
        "proFruit": ["商品管理", "商品转换管理"],
        "proFruitAdd": ["商品管理", "商品转换添加"],
        "proFruitEdit": ["商品管理", "商品转换编辑"],
        "proOffline": ["商品管理", "商品下架申请"],
        "proOfflineFirst": ["商品管理", "商品下架初审"],
        "proOfflineSecond": ["商品管理", "商品下架二审"],
        "proOfflineThird": ["商品管理", "商品下架终审"],
        "vendor": ['厂商管理', "厂商列表"],
        "vendorAdd": ['厂商管理', "新建厂商"],
        "vendorEdit": ['厂商管理', "编辑厂商"],
        "vendorBindProduct": ['厂商管理', "厂商绑定商品"],
        "warehouse": ['仓库管理', '仓库设置'],
        "warehouseAdd": ['仓库管理', '新建仓库'],
        "warehouseEdit": ['仓库管理', '编辑仓库'],
        "warehouseArea": ['仓库管理', '库区设置'],
        "warehouseAreaAdd": ['仓库管理', '新建库区'],
        "warehouseAreaEdit": ['仓库管理', '编辑库区'],
        "warehouseRowShelf": ['仓库管理', '货架设置'],
        "warehouseRowShelfAdd": ['仓库管理', '新建货架'],
        "warehouseRowShelfEdit": ['仓库管理', '编辑货架'],
        "warehouseLibrary": ['仓库管理', '库位设置'],
        "warehouseLibraryAdd": ['仓库管理', '新建库位'],
        "warehouseLibraryEdit": ['仓库管理', '编辑库位'],
        "customerDemandOrder": ['大客户专项', '大客户需求订单'],
        "customerDemandOrderAdd": ['大客户专项', '大客户需求订单添加'],
        "customerDemandOrderEdit": ['大客户专项', '大客户需求订单编辑'],
        "customerProgrammeOrderReviewed": ['大客户专项', '大客户订单审核'],
        "customerProgrammeOrder": ['大客户专项', '大客户方案'],
        "customerProgrammeOrderInfo": ['大客户专项', '大客户方案详情'],
        "customerProgrammeAdd": ['大客户专项', '大客户方案添加'],
        "customerProgrammeEdit": ['大客户专项', '大客户方案编辑'],
        "customerProgrammeReviewed": ['大客户专项', '大客户方案审核'],
        "customerProgrammeReviewedInfo": ['大客户专项', '大客户方案审核列表'],
        "customerCreateOrder": ['大客户专项', '大客户方案订单生成'],
        "customerCreateOrderReviewed": ['大客户专项', '大客户方案订单审核']
    }
    render() {
        const { pathname } = this.props.location
        const arr = pathname.split('/')
        return (
            <Breadcrumb>
                <Breadcrumb.Item key='index'>首页</Breadcrumb.Item>
                {
                    this.crumbJson[arr[1]] ? this.crumbJson[arr[1]].map((e, i) => {
                        return <Breadcrumb.Item key={i}>{e}</Breadcrumb.Item>
                    }) : <Breadcrumb.Item key={-1}>404</Breadcrumb.Item>
                }
            </Breadcrumb>

        );
    }
}