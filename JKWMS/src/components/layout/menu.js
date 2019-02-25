import { Menu, Icon } from 'antd';
import React, { Component } from 'react';
import { Link } from 'react-router-dom';
import { isInAction } from '../../utils/helpMethod'
const SubMenu = Menu.SubMenu;
export default class MenuLayout extends Component {

    render() {
        return (
            <Menu
                mode="inline"
                theme="light"
                defaultSelectedKeys={['1']}
                openKeys={this.props.openKeys}
                onOpenChange={this.props.onOpenChange}
            >
                {isInAction("ProductProduct") ?
                    <SubMenu key="sub1" title={<span><Icon type="shop" /><span>商品管理</span></span>}>
                        {isInAction("ProductproductList") ?
                            <Menu.Item key="1"><Link to='/goods'>商品列表</Link></Menu.Item>
                            : null
                        }
                        {isInAction("ProductTypeindex") ?
                            <Menu.Item key="2"><Link to='/goodsType'>商品类型管理</Link></Menu.Item>
                            : null
                        }
                        {isInAction("ProExcludeindex") ?
                            <Menu.Item key="3"><Link to='/proExclude'>不推送库存管理</Link></Menu.Item>
                            : null
                        }
                        {isInAction("ProFruitindex") ?
                            <Menu.Item key="4"><Link to='/proFruit'>商品转换管理</Link></Menu.Item>
                            : null
                        }
                        {isInAction("ProOfflineindex") ?
                            <Menu.Item key="5"><Link to='/proOffline'>商品下架申请</Link></Menu.Item>
                            : null
                        }
                        {/*{isInAction("productList") ?*/}
                        {/*<Menu.Item key="6"><Link to='/proOfflineFirst'>商品下架初审</Link></Menu.Item>*/}
                        {/*: null*/}
                        {/*}*/}
                        {/*{isInAction("productList") ?*/}
                        {/*<Menu.Item key="7"><Link to='/proOfflineSecond'>商品下架二审</Link></Menu.Item>*/}
                        {/*: null*/}
                        {/*}*/}
                        {/*{isInAction("productList") ?*/}
                        {/*<Menu.Item key="8"><Link to='/proOfflineThird'>商品下架终审</Link></Menu.Item>*/}
                        {/*: null*/}
                        {/*}*/}
                    </SubMenu>
                    : null}
                <SubMenu key="sub2" title={<span><Icon type="hourglass" /><span>厂商管理</span></span>}>
                    <Menu.Item key="1"><Link to='/vendor'>厂商列表</Link></Menu.Item>
                </SubMenu>
                <SubMenu key="sub3" title={<span><Icon type="hdd" /><span>仓库管理</span></span>}>
                    <Menu.Item key="1"><Link to='/warehouse'>仓库设置</Link></Menu.Item>
                    <Menu.Item key="2"><Link to='/warehouseArea'>库区设置</Link></Menu.Item>
                    <Menu.Item key="3"><Link to='/warehouseRowShelf'>货架设置</Link></Menu.Item>
                    <Menu.Item key="4"><Link to='/warehouseLibrary'>库位设置</Link></Menu.Item>
                </SubMenu>
                <SubMenu key="sub4" title={<span><Icon type="audit" /><span>大客户专项</span></span>}>
                    <Menu.Item key="1"><Link to='/customerDemandOrder'>大客户需求订单</Link></Menu.Item>
                    <Menu.Item key="2"><Link to='/customerProgrammeOrder'>大客户方案提交</Link></Menu.Item>
                    <Menu.Item key="3"><Link to='/customerProgrammeReviewed'>大客户方案审核</Link></Menu.Item>
                    <Menu.Item key="4"><Link to='/customerCreateOrderReviewed'>大客户订单审核</Link></Menu.Item>
                </SubMenu>
                <SubMenu key="sub5" title={<span><Icon type="setting" /><span>系统设置</span></span>}>
                    <Menu.Item key="9">Option 9</Menu.Item>
                    <Menu.Item key="10">Option 10</Menu.Item>
                    <Menu.Item key="11">Option 11</Menu.Item>
                    <Menu.Item key="12">Option 12</Menu.Item>
                </SubMenu>
            </Menu>
        );
    }
}