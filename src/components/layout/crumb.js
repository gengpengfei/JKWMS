import { Breadcrumb } from 'antd';
import React, { Component } from 'react';
export default class CrumbLayout extends Component {
    crumbJson = {
        "goods": ["商品管理", "商品管理"],
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
                    }) : null
                }
            </Breadcrumb>
        );
    }
}