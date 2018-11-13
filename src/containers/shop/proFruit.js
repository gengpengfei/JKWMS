// -- 商品转换列表
import React, { Component } from 'react';
import { Table, Pagination, Button, Input, Icon, Divider, message, Modal } from 'antd'
import { Link } from 'react-router-dom'
import { NetWork_Post } from '../../network/netUtils'
export default class proFruit extends Component {
    constructor(props) {
        super(props)
        this.state = {
            data: [],
            page: 1,
            limit: 10,
            search_key: '',
            deleteRowKeys: '',
        }
    }
    componentDidMount() {
        //-- 获取商品转换列表
        this._getProFruitList()
    }
    _getProFruitList = () => {
        const formData = {
            search_key: this.state.search_key,
            limit: this.state.limit,
            page: this.state.page
        }
        NetWork_Post('proFruitList', formData, (response) => {
            const { status, data, msg } = response
            if (status === '0000') {
                this.setState({
                    data: data.proFruitList,
                    total: data.proFruitCount
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    _proFruitDel = () => {
        const formData = {
            id: this.state.deleteRowKeys
        }
        console.log(formData)
        NetWork_Post('proFruitDel', formData, (response) => {
            const { status, msg } = response
            if (status === '0000') {
                message.success(msg)
                this._getProFruitList();
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    _showDeleteConfirm = () => {
        Modal.confirm({
            title: '系统提示',
            content: '您确定要删除所选数据？',
            okType: 'danger',
            onOk: this._proFruitDel
        });
    }
    columns = [
        {
            align: 'center',
            title: '单一商品编号',
            dataIndex: 'pro_code',
            key: 'pro_code',
        }, {
            align: 'center',
            title: '单一商品名称',
            dataIndex: 'pro_name',
            key: 'pro_name',
        }, {
            align: 'center',
            title: '组合商品编号',
            dataIndex: 'pro_code_mix',
            key: 'pro_code_mix',
        }, {
            align: 'center',
            title: '组合商品名称',
            dataIndex: 'pro_name_mix',
            key: 'pro_name_mix',
        }, {
            align: 'center',
            title: '转换后的规格',
            dataIndex: 'times',
            key: 'times',
        }, {
            align: 'center',
            title: '备注',
            dataIndex: 'remark',
            key: 'remark',
        }, {
            align: 'center',
            title: '操作',
            key: 'action',
            render: (text, record) => {
                return (
                    <span>
                        <Link to={'/proFruitEdit/' + text.id}>
                            编辑
                        </Link>
                        <Divider type="vertical" />
                        <a href='###' onClick={() => this.setState({ deleteRowKeys: [text.id] }, this._showDeleteConfirm)}>
                            删除
                        </a>
                    </span >
                )
            },
        }
    ];
    onChangePage = (pageNumber) => {
        this.setState({
            page: pageNumber
        }, () => {
            this._getProFruitList()
        })
    }
    onChangeLimit = (page, limit) => {
        this.setState({
            page: page,
            limit: limit
        }, () => {
            this._getProFruitList()
        })
    }
    render() {
        return (
            <div style={{ width: '100%' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', width: '100%', height: 50 }}>
                    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                        <Button icon="plus" onClick={() => this.props.history.push('/proFruitAdd')}>新建</Button>
                    </div>
                    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                        <Input value={this.state.search_key} prefix={<Icon type="search" style={{ color: '#b2b2b2' }} />} placeholder="搜索" onChange={(e) => this.setState({ search_key: e.target.value })} />
                        <Button style={{ margin: '0 10px' }} icon='search' onClick={() => { this._getProFruitList() }}>搜索</Button>
                    </div>
                </div>
                <Table
                    bordered
                    loading={false}
                    pagination={false} //-- 不使用自带的分页器
                    rowSelection={null} //-- 配置表格行是否可选
                    dataSource={this.state.data}
                    columns={this.columns}
                    rowKey={(row) => row.id}
                    size='small'
                />
                <div style={{ width: '100%', display: 'flex', justifyContent: 'flex-end', alignItems: 'center', height: 60 }}>
                    <Pagination
                        showQuickJumper
                        showSizeChanger
                        defaultCurrent={1}
                        total={this.state.total}
                        onChange={this.onChangePage}
                        onShowSizeChange={this.onChangeLimit}
                    />
                </div>
            </div>
        )
    }
}