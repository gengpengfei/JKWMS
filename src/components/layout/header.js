import { Layout } from 'antd';
import React, { Component } from 'react';
import { Link } from 'react-router-dom'
const { Header } = Layout;

export default class HeadLayout extends Component {
    _loginOut = () => {
        localStorage.clear();
        window.location.href = '/login'
    }
    render() {
        return (
            <Header style={{ width: '100%', position: 'fixed', top: 0, zIndex: 1, height: 60, backgroundColor: '#0077ff', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <div style={{ fontSize: 24, color: '#f4f4f4' }}>食恪物流管理系统</div>
                <div style={{ display: 'flex' }}>
                    <div style={{ color: '#f4f4f4' }}>Mr.Geng 您好 | </div>
                    <Link style={{ color: '#f4f4f4' }} to=''> 修改密码 | </Link>
                    <a style={{ color: '#f4f4f4' }} href='###' onClick={this._loginOut}> 安全退出</a>
                </div>
            </Header>
        );
    }
}