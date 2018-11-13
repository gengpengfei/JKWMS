import { Menu, Icon } from 'antd';
import React, { Component } from 'react';
import { Link } from 'react-router-dom';
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
                <SubMenu key="sub1" title={<span><Icon type="shop" /><span>商品管理</span></span>}>
                    <Menu.Item key="1"><Link to='/goods'>商品管理</Link></Menu.Item>
                    <Menu.Item key="2"><Link to='/goodsType'>商品类型管理</Link></Menu.Item>
                    <Menu.Item key="3"><Link to='/proExclude'>不推送库存管理</Link></Menu.Item>
                    <Menu.Item key="4"><Link to='/proFruit'>商品转换管理</Link></Menu.Item>
                    <Menu.Item key="5"><Link to='/proOffline'>商品下架申请</Link></Menu.Item>
                    <Menu.Item key="6"><Link to='/proOfflineFirst'>商品下架初审</Link></Menu.Item>
                    <Menu.Item key="7"><Link to='/proOfflineSecond'>商品下架二审</Link></Menu.Item>
                    <Menu.Item key="8"><Link to='/proOfflineThird'>商品下架终审</Link></Menu.Item>
                </SubMenu>
                <SubMenu key="sub2" title={<span><Icon type="inbox" /><span>厂商管理</span></span>}>
                    <Menu.Item key="5">Option 5</Menu.Item>
                    <Menu.Item key="6">Option 6</Menu.Item>
                    <Menu.Item key="7">Option 7</Menu.Item>
                    <Menu.Item key="8">Option 8</Menu.Item>
                </SubMenu>
                <SubMenu key="sub4" title={<span><Icon type="setting" /><span>系统设置</span></span>}>
                    <Menu.Item key="9">Option 9</Menu.Item>
                    <Menu.Item key="10">Option 10</Menu.Item>
                    <Menu.Item key="11">Option 11</Menu.Item>
                    <Menu.Item key="12">Option 12</Menu.Item>
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