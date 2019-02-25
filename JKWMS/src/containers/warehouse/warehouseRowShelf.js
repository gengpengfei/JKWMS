// -- 货架管理
import React, { Component } from 'react';
import { Table, Pagination, Button, Input, Icon, Divider, message, Modal, Select, Upload } from 'antd'
import { Link } from 'react-router-dom'
import { NetWork_Post, NetWork_File } from '../../network/netUtils'
const Option = Select.Option;
export default class warehouseRowShelf extends Component {
    constructor(props) {
        super(props)
        this.state = {
            data: [],
            warehouseList: [],
            warehouseAreaList: [],
            WarehouseRowShelfList: [],
            warehouse_num: undefined,
            warea_num: undefined,
            page: 1,
            limit: 10,
            search_key: '',
            selectedRowKeys: [],
            loading: true,
            fileList: [],
            uploading: false,
            fileName: null,
        }
    }
    componentDidMount() {
        //-- 获取货架列表
        this._getWarehouseRowShelfList()
        //-- 获取仓库列表
        this._getWarehouseList()
        //-- 获取信息导入模版下载地址
        this._getRowShelfDownload()
    }
    _getRowShelfDownload = () => {
        const formData = {
        }
        NetWork_Post('rowShelfDownload', formData, (response) => {
            const { status, data, msg } = response
            console.log('rowShelfDownload', data)
            if (status === '0000') {
                this.setState({
                    rowShelfDownload: data,
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    _getWarehouseRowShelfList = () => {
        const formData = {
            search_key: this.state.search_key,
            limit: this.state.limit,
            page: this.state.page,
        }
        if (this.state.warehouse_num) formData.warehouse_num = this.state.warehouse_num;
        if (this.state.warea_num) formData.warea_num = this.state.warea_num;
        NetWork_Post('warehouseRowShelfList', formData, (response) => {
            const { status, data, msg } = response
            console.log('warehouseRowShelfList', response)
            if (status === '0000') {
                this.setState({
                    data: data.rowShelfList,
                    total: data.rowShelfCount
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
    _getWarehouseAreaList = (value) => {
        const formData = {
            limit: 10000,
            page: 1,
            warehouse_num: value
        }
        NetWork_Post('warehouseAreaList', formData, (response) => {
            const { status, data, msg } = response
            console.log('warehouseAreaList', response)
            if (status === '0000') {
                this.setState({
                    warehouseAreaList: data.warehouseAreaList,
                    warea_num: undefined
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
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
            this._getWarehouseRowShelfList()
        })
    }
    onChangeLimit = (page, limit) => {
        this.setState({
            page: page,
            limit: limit
        }, () => {
            this._getWarehouseRowShelfList()
        })
    }
    //-- 删除
    _deleteWarehouseRowShelf = () => {
        const formData = {
            id: this.state.selectedRowKeys
        }
        NetWork_Post('warehouseRowShelfDel', formData, (response) => {
            const { status, msg } = response
            if (status === '0000') {
                message.success(msg)
                this._getWarehouseRowShelfList();
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
            onOk: this._deleteWarehouseRowShelf
        });
    }
    //-- 导入文件上传
    _handleUpload = () => {
        const { fileList } = this.state;
        const formData = new FormData();
        fileList.forEach((file) => {
            formData.append('file', file);
        });
        this.setState({
            uploading: true,
        });
        NetWork_File('rowShelfImport', formData, (response) => {
            const { status, msg } = response
            if (status === '0000') {
                message.success(msg)
                this.setState({
                    fileList: [],
                    fileName: '未选择文件'
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
            this.setState({
                uploading: false,
            });
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
            title: '货架编号',
            dataIndex: 'shelf_num',
            key: 'shelf_num',
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
                    <Link to={'/warehouseRowShelfEdit/' + text.id}>
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
        const { uploading, } = this.state;
        const uploadProps = {
            showUploadList: false,
            onRemove: (file) => {
                this.setState(({ fileList }) => {
                    const index = fileList.indexOf(file);
                    const newFileList = fileList.slice();
                    newFileList.splice(index, 1);
                    return {
                        fileList: newFileList,
                    };
                });
            },
            beforeUpload: (file) => {
                this.setState({
                    fileList: [file],
                    fileName: file.name
                });
                return false;
            },
            fileList: this.state.fileList,
        };
        return (
            <div style={{ width: '100%' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', width: '100%', height: 50 }}>
                    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                        <Button icon="plus" onClick={() => this.props.history.push('/warehouseRowShelfAdd')}>新建</Button>
                        <Button style={{ margin: '0 10px' }} icon='delete' onClick={this._showDeleteConfirm}>批量删除</Button>
                    </div>
                    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                        <Select placeholder='选择仓库' allowClear={true} style={{ width: 200, marginRight: 10 }} onChange={(value) => this.setState({ warehouse_num: value }, () => { this._getWarehouseAreaList(value) })}>
                            {
                                this.state.warehouseList.map((item, index) => <Option key={index} value={item.warehouse_num}>{item.warehouse_name}</Option>)
                            }
                        </Select>
                        <Select placeholder='选择库区' allowClear={true} style={{ width: 200, marginRight: 10 }} value={this.state.warea_num} onChange={(value) => this.setState({ warea_num: value })}>
                            {
                                this.state.warehouseAreaList.map((item, index) => <Option key={index} value={item.warea_num}>{item.warea_name}</Option>)
                            }
                        </Select>
                        <Input value={this.state.search_key} prefix={<Icon type="search" style={{ color: '#b2b2b2' }} />} placeholder="搜索" onChange={(e) => this.setState({ search_key: e.target.value })} style={{ width: 200 }} />
                        <Button style={{ margin: '0 10px' }} icon='search' onClick={() => { this._getWarehouseRowShelfList() }}>搜索</Button>
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
                <div style={{ width: '100%', borderTop: '1px solid #EFEFEF' }}>
                    <div style={{ margin: '10px 0', display: 'flex', justifyContent: 'flex-start', alignItems: 'center' }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                            <Upload {...uploadProps}>
                                请选择要导入的Excel: <Button size='small' icon='folder-open'>选取文件</Button>
                            </Upload>
                            <div style={{ margin: 10 }}>{this.state.fileName1 ? this.state.fileName1 : '未选择文件'}</div>
                        </div>
                        <Button
                            size='small'
                            onClick={this._handleUpload}
                            disabled={this.state.fileList.length === 0}
                            loading={uploading}
                            icon='upload'
                        >
                            货架信息导入
                        </Button>
                        <Divider type="vertical" />
                        <a href={this.state.rowShelfDownload}><Button size='small' icon='download'>货架信息导入模板下载</Button></a>
                    </div>
                </div>
            </div >
        )
    }
}