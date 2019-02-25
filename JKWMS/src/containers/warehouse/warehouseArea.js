// -- 仓库管理
import React, { Component } from 'react';
import { Table, Pagination, Button, Input, Icon, Divider, message, Modal, Select } from 'antd'
import { Link } from 'react-router-dom'
import { NetWork_Post } from '../../network/netUtils'
const Option = Select.Option;
export default class warehouseArea extends Component {
    constructor(props) {
        super(props)
        this.state = {
            data: [],
            warehouseList: [],
            warehouse_num: null,
            warea_type: null,
            page: 1,
            limit: 10,
            search_key: '',
            selectedRowKeys: [],
            loading: true,
        }
    }
    componentDidMount() {
        //-- 获取库区列表
        this._getWarehouseAreaList()
        //-- 获取仓库列表
        this._getWarehouseList()
    }
    _getWarehouseAreaList = () => {
        const formData = {
            search_key: this.state.search_key,
            limit: this.state.limit,
            page: this.state.page,
        }
        if (this.state.warehouse_num) formData.warehouse_num = this.state.warehouse_num;
        if (this.state.warea_type !== false) formData.warea_type = this.state.warea_type;
        console.log(formData);
        NetWork_Post('warehouseAreaList', formData, (response) => {
            const { status, data, msg } = response
            console.log('warehouseAreaList', response)
            if (status === '0000') {
                this.setState({
                    data: data.warehouseAreaList,
                    total: data.warehouseCount
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
            this.setState({
                loading: false
            })
        })
    }
    _getWarehouseList = () => {
        const formData = {
            limit: 10000,
            page: 1
        }
        NetWork_Post('warehouseList', formData, (response) => {
            const { status, data, msg } = response
            console.log('warehouseList', response)
            if (status === '0000') {
                this.setState({
                    warehouseList: data.warehouseList
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
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
    _deleteWarehouseArea = () => {
        const formData = {
            id: this.state.selectedRowKeys
        }
        NetWork_Post('warehouseAreaDel', formData, (response) => {
            const { status, msg } = response
            if (status === '0000') {
                message.success(msg)
                this._getWarehouseAreaList();
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
            onOk: this._deleteWarehouseArea
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
            title: '库区编号',
            dataIndex: 'warea_num',
            key: 'warea_num',
        }, {
            align: 'center',
            title: '库区名称',
            dataIndex: 'warea_name',
            key: 'warea_name',
        }, {
            align: 'center',
            title: '库区类型',
            key: 'warea_type',
            render: (text, record) => {
                return text.warea_type === 0 ? '收货区' : text.warea_type === 1 ? '存储区' : text.warea_type === 2 ? '次品区' : text.warea_type === 3 ? '拣货区' : null
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
                    <Link to={'/warehouseAreaEdit/' + text.id}>
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
                        <Button icon="plus" onClick={() => this.props.history.push('/warehouseAreaAdd')}>新建</Button>
                        <Button style={{ margin: '0 10px' }} icon='delete' onClick={this._showDeleteConfirm}>批量删除</Button>
                    </div>
                    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                        <Select placeholder='选择仓库' allowClear={true} style={{ width: 200, marginRight: 10 }} onChange={(value) => this.setState({ warehouse_num: value })}>
                            {
                                this.state.warehouseList.map((item, index) => <Option key={index} value={item.warehouse_num}>{item.warehouse_name}</Option>)
                            }
                        </Select>
                        <Select placeholder='选择库区' allowClear={true} style={{ width: 200, marginRight: 10 }} onChange={(value) => this.setState({ warea_type: value })}>
                            <Option key={1} value={0}>收货区</Option>
                            <Option key={2} value={1}>存储区</Option>
                            <Option key={3} value={2}>次品区</Option>
                            <Option key={4} value={3}>拣货区</Option>
                        </Select>
                        <Input value={this.state.search_key} prefix={<Icon type="search" style={{ color: '#b2b2b2' }} />} placeholder="搜索" onChange={(e) => this.setState({ search_key: e.target.value })} style={{ width: 200 }} />
                        <Button style={{ margin: '0 10px' }} icon='search' onClick={() => { this._getWarehouseAreaList() }}>搜索</Button>
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