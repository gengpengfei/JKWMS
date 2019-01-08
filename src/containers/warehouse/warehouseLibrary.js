// -- 货架管理
import React, { Component } from 'react';
import { Table, Pagination, Button, Input, Icon, Divider, message, Modal, Select, Upload } from 'antd'
import { Link } from 'react-router-dom'
import { NetWork_Post, NetWork_File } from '../../network/netUtils'
const Option = Select.Option;
export default class warehouseLibrary extends Component {
    constructor(props) {
        super(props)
        this.state = {
            data: [],
            warehouseList: [],
            warehouseAreaList: [],
            warehouseRowShelfList: [],
            warehouse_num: undefined,
            warea_num: undefined,
            shelf_id: undefined,
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
        //-- 获取仓库列表
        this._getWarehouseList()
        //-- 获取库位列表
        this._getWarehouseLibraryList()
        //-- 获取导入模版下载地址
        this._getLibraryDownload()
    }
    _getWarehouseLibraryList = () => {
        const formData = {
            search_key: this.state.search_key,
            limit: this.state.limit,
            page: this.state.page,
        }
        if (this.state.warehouse_num) formData.warehouse_num = this.state.warehouse_num;
        if (this.state.warea_num) formData.warea_num = this.state.warea_num;
        if (this.state.shelf_id) formData.shelf_id = this.state.shelf_id;

        NetWork_Post('warehouseLibraryList', formData, (response) => {
            const { status, data, msg } = response
            console.log('warehouseLibraryList', response)
            if (status === '0000') {
                this.setState({
                    data: data.wLibraryList,
                    total: data.wLibraryCount
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
    _getLibraryDownload = () => {
        const formData = {
        }
        NetWork_Post('libraryDownload', formData, (response) => {
            const { status, data, msg } = response
            console.log('libraryDownload', data)
            if (status === '0000') {
                this.setState({
                    libraryDownload: data,
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
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
    _getWarehouseRowShelfList = (value) => {
        const formData = {
            warea_num: value,
            limit: 10000,
            page: 1,
        }
        NetWork_Post('warehouseRowShelfList', formData, (response) => {
            const { status, data, msg } = response
            console.log('warehouseRowShelfList', response)
            if (status === '0000') {
                this.setState({
                    warehouseRowShelfList: data.rowShelfList,
                    shelf_id: undefined
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
            this._getWarehouseLibraryList()
        })
    }
    onChangeLimit = (page, limit) => {
        this.setState({
            page: page,
            limit: limit
        }, () => {
            this._getWarehouseLibraryList()
        })
    }
    //-- 删除
    _deleteWarehouseLibrary = () => {
        const formData = {
            id: this.state.selectedRowKeys
        }
        NetWork_Post('warehouseLibraryDel', formData, (response) => {
            const { status, msg } = response
            if (status === '0000') {
                message.success(msg)
                this._getWarehouseLibraryList();
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
            onOk: this._deleteWarehouseLibrary
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
        NetWork_File('libraryImport', formData, (response) => {
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
            title: '库位编号',
            dataIndex: 'wlibrary_num',
            key: 'wlibrary_num',
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
                    <Link to={'/warehouseLibraryEdit/' + text.id}>
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
                        <Button icon="plus" onClick={() => this.props.history.push('/warehouseLibraryAdd')}>新建</Button>
                        <Button style={{ margin: '0 10px' }} icon='delete' onClick={this._showDeleteConfirm}>批量删除</Button>
                    </div>
                    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                        <Select placeholder='选择仓库' allowClear={true} style={{ width: 200, marginRight: 10 }} onChange={(value) => this.setState({ warehouse_num: value }, () => { this._getWarehouseAreaList(value) })}>
                            {
                                this.state.warehouseList.map((item, index) => <Option key={index} value={item.warehouse_num}>{item.warehouse_name}</Option>)
                            }
                        </Select>
                        <Select placeholder='选择库区' allowClear={true} style={{ width: 200, marginRight: 10 }} value={this.state.warea_num} onChange={(value) => this.setState({ warea_num: value }, () => { this._getWarehouseRowShelfList(value) })}>
                            {
                                this.state.warehouseAreaList.map((item, index) => <Option key={index} value={item.warea_num}>{item.warea_name}</Option>)
                            }
                        </Select>
                        <Select placeholder='选择货架' allowClear={true} style={{ width: 200, marginRight: 10 }} value={this.state.shelf_id} onChange={(value) => this.setState({ shelf_id: value })}>
                            {
                                this.state.warehouseRowShelfList.map((item, index) => <Option key={index} value={item.id}>{item.shelf_num}</Option>)
                            }
                        </Select>
                        <Input value={this.state.search_key} prefix={<Icon type="search" style={{ color: '#b2b2b2' }} />} placeholder="搜索" onChange={(e) => this.setState({ search_key: e.target.value })} style={{ width: 200 }} />
                        <Button style={{ margin: '0 10px' }} icon='search' onClick={() => { this._getWarehouseLibraryList() }}>搜索</Button>
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
                            库位信息导入
                        </Button>
                        <Divider type="vertical" />
                        <a href={this.state.libraryDownload}><Button size='small' icon='download'>库位信息导入模板下载</Button></a>
                    </div>
                </div>
            </div >
        )
    }
}