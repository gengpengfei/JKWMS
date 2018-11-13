import { Layout, Icon, LocaleProvider } from 'antd';
import React, { Component } from 'react';
import MenuLayout from '../../components/layout/menu';
import HeadLayout from '../../components/layout/header';
import CrumbLayout from '../../components/layout/crumb';
import zhCN from 'antd/lib/locale-provider/zh_CN';
const { Sider, Content } = Layout;
export default class Index extends Component {
  constructor(props) {
    super(props)
    this.state = {
      collapsed: false,
      openKeys: ['sub1']
    };
    this.openOldKeys = ['sub1']
    this.rootSubmenuKeys = ['sub1', 'sub2', 'sub4'];
  }
  componentDidMount() {
    const token = localStorage.getItem('token')
    //-- 判断登录
    if (!token) window.location.href = '/';
  }
  onOpenChange = (openKeys) => {
    const latestOpenKey = openKeys.find(key => this.state.openKeys.indexOf(key) === -1);
    if (this.rootSubmenuKeys.indexOf(latestOpenKey) === -1) {
      this.setState({ openKeys });
    } else {
      this.setState({
        openKeys: latestOpenKey ? [latestOpenKey] : [],
      });
      this.latestOpenKey = latestOpenKey;
    }
  }
  toggle = () => {
    if (this.state.collapsed) {
      this.setState({
        openKeys: this.openOldKeys
      }, () => {
        this.setState({
          collapsed: !this.state.collapsed,
        });
      });
    } else {
      this.openOldKeys = this.state.openKeys
      this.setState({
        openKeys: []
      }, () => {
        this.setState({
          collapsed: !this.state.collapsed,
        });
      });
    }
  }
  render() {
    return (
      <LocaleProvider locale={zhCN}>
        <Layout>
          {/* 头部 */}
          <HeadLayout toggle={this.toggle} collapsed={this.state.collapsed} />
          <Layout style={{ display: 'flex', marginTop: 60, minHeight: 100 }}>
            {/* 左侧导航栏 */}
            <Sider
              trigger={null}
              collapsible
              collapsed={this.state.collapsed}
              style={{ background: '#fff' }}
            >
              <MenuLayout onOpenChange={this.onOpenChange} openKeys={this.state.openKeys} />
            </Sider>
            <Content style={{ display: 'flex', margin: '10px 0px 0px 10px', backgroundColor: '#fff', flexDirection: 'column' }}>
              {/* 面包屑部分 */}
              <div style={{ display: 'flex', justifyContent: 'flex-start', height: 40, width: '100%', alignItems: 'center', borderBottom: '1px solid #efefef' }}>
                <Icon
                  type={this.state.collapsed ? 'menu-unfold' : 'menu-fold'}
                  onClick={this.toggle}
                  style={{ width: 40, height: 40, display: 'flex', alignItems: 'center', justifyContent: 'center' }}
                />
                <CrumbLayout {...this.props} />
              </div>
              {/* 主体部分 */}
              <div style={{ display: 'flex', padding: 10, width: '100%', borderBottom: '1px solid #efefef' }}>
                {this.props.children}
              </div>
            </Content>
          </Layout>
        </Layout>
      </LocaleProvider>
    );
  }
}