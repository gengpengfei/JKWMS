// -- 库区管理
import React, { Component } from 'react';
import { Table, Pagination, Button, Input, Icon, Divider, message, Modal } from 'antd'
import { Link } from 'react-router-dom'
import { NetWork_Post } from '../../network/netUtils'
export default class warehouse extends Component {
    constructor(props) {
        super(props)
        this.state = {
            data: [],
            page: 1,
            limit: 10,
            search_key: '',
            selectedRowKeys: [],
            loading: true,
        }
    }
    componentDidMount() {
        //-- 获取仓库列表
        this._getWarehouseList()
    }
    _getWarehouseList = () => {
        const formData = {
            search_key: this.state.search_key,
            limit: this.state.limit,
            page: this.state.page
        }
        NetWork_Post('warehouseList', formData, (response) => {
            const { status, data, msg } = response
            console.log('warehouseList', response)

            if (status === '0000') {
                this.setState({
                    data: data.warehouseList,
                    total: data.warehouseCount,
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
            this.setState({
                loading: false
            })
        });
    }
    rowSelection = {
        onChange: (index, row) => {
            this.setState({
                selectedRowKeys: index
            })
        }
    }
    onChangePage = (pageNumber) => {
        this.setState({
            page: pageNumber
        }, () => {
            this._getWarehouseList()
        })
    }
    onChangeLimit = (page, limit) => {
        this.setState({
            page: page,
            limit: limit
        }, () => {
            this._getWarehouseList()
        })
    }
    //-- 删除
    _deleteWarehouse = () => {
        const formData = {
            id: this.state.selectedRowKeys
        }
        NetWork_Post('warehouseDel', formData, (response) => {
            const { status, msg } = response
            if (status === '0000') {
                message.success(msg)
                this._getWarehouseList();
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
            onOk: this._deleteWarehouse
        });
    }
    columns = [
        {
            align: 'center',
            title: '仓库编号',
            dataIndex: 'warehouse_num',
            key: 'warehouse_num',
        }, {
            align: 'center',
            title: '仓库名称',
            dataIndex: 'warehouse_name',
            key: 'warehouse_name',
        }, {
            align: 'center',
            title: '负责人',
            dataIndex: 'pic_name',
            key: 'pic_name',
        }, {
            align: 'center',
            title: '负责人手机',
            dataIndex: 'pic_mobile',
            key: 'pic_mobile',
        }, {
            align: 'center',
            title: '仓库所在地',
            key: 'province',
            render: (text, record, index) => {
                return text.city + '-' + text.area + '-' + text.address;
            }
        }, {
            align: 'center',
            title: '备注',
            dataIndex: 'remark',
            key: 'remark',
        }, {
            align: 'center',
            title: '操作',
            key: 'action',
            render: (text, record) => (
                <span>
                    <Link to={'/warehouseEdit/' + text.id}>
                        编辑
                        </Link>
                    <Divider type="vertical" />
                    <span style={{ cursor: 'pointer', color: '#4490ff' }} onClick={() => this.setState({ selectedRowKeys: [text.id] }, this._showDeleteConfirm)}>
                        删除
                    </span>
                </span>
            ),
        }
    ];
    render() {
        return (
            <div style={{ width: '100%' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', width: '100%', height: 50 }}>
                    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                        <Button icon="plus" onClick={() => this.props.history.push('/warehouseAdd')}>新建</Button>
                        <Button style={{ margin: '0 10px' }} icon='delete' onClick={this._showDeleteConfirm}>批量删除</Button>
                    </div>
                    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                        <Input value={this.state.search_key} prefix={<Icon type="search" style={{ color: '#b2b2b2' }} />} placeholder="搜索" onChange={(e) => this.setState({ search_key: e.target.value })} />
                        <Button style={{ margin: '0 10px' }} icon='search' onClick={() => { this._getWarehouseList() }}>搜索</Button>
                    </div>
                </div>
                <Table
                    bordered
                    loading={this.state.loading}
                    pagination={false} //-- 不使用自带的分页器
                    rowSelection={this.rowSelection}
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
            </div >
        )
    }
}