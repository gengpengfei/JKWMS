// -- 厂商列表管理
import React, { Component } from 'react';
import { Table, Pagination, Button, Input, Icon, Divider, message, Upload, Modal } from 'antd'
import { Link } from 'react-router-dom'
import { NetWork_File, NetWork_Post } from '../../network/netUtils'
export default class vendor extends Component {
    constructor(props) {
        super(props)
        this.state = {
            data: [],
            page: 1,
            limit: 10,
            search_key: '',
            selectedRowKeys: [],
            productDownload: '',
            productVenDownload: '',
            fileList1: [],
            fileList2: [],
            uploading1: false,
            uploading2: false,
            fileName1: null,
            fileName2: null,
        }
    }
    componentDidMount() {
        //-- 获取厂商列表
        this._getVendorList()
        //-- 获取导入厂商模板下载地址
        this._getVendorDownload()
        //-- 获取厂商绑定商品导入模版下载地址
        this._getProductVenImport()
    }
    _getProductVenImport = () => {
        const formData = {
        }
        NetWork_Post('productVenDownload', formData, (response) => {
            const { status, data, msg } = response
            if (status === '0000') {
                this.setState({
                    productVenDownload: data,
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    _getVendorDownload = () => {
        const formData = {
        }
        NetWork_Post('vendorDownload', formData, (response) => {
            const { status, data, msg } = response
            if (status === '0000') {
                this.setState({
                    vendorDownload: data,
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    _getVendorList = () => {
        const formData = {
            search_key: this.state.search_key,
            limit: this.state.limit,
            page: this.state.page
        }
        NetWork_Post('vendorList', formData, (response) => {
            const { status, data, msg } = response
            if (status === '0000') {
                this.setState({
                    data: data.vendorList,
                    total: data.vendorCount
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
            this._getVendorList()
        })
    }
    onChangeLimit = (page, limit) => {
        this.setState({
            page: page,
            limit: limit
        }, () => {
            this._getVendorList()
        })
    }
    //-- 厂商信息导入文件上传
    _handleUpload1 = () => {
        const { fileList1 } = this.state;
        const formData = new FormData();
        fileList1.forEach((file) => {
            formData.append('file', file);
        });
        this.setState({
            uploading1: true,
        });
        NetWork_File('vendorImport', formData, (response) => {
            console.log(response)
            const { status, msg } = response
            if (status === '0000') {
                message.success(msg)
                this.setState({
                    fileList1: [],
                    fileName1: '未选择文件'
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
            this.setState({
                uploading1: false,
            });
        });
    }
    //-- 厂商绑定商品导入文件上传
    _handleUpload2 = () => {
        const { fileList2 } = this.state;
        const formData = new FormData();
        fileList2.forEach((file) => {
            formData.append('file', file);
        });
        this.setState({
            uploading2: true,
        });
        NetWork_File('VendorBindProductImport', formData, (response) => {
            const { status, msg } = response
            if (status === '0000') {
                message.success(msg)
                this.setState({
                    fileList2: [],
                    fileName2: '未选择文件'
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
            this.setState({
                uploading2: false,
            });
        });
    }
    //-- 厂商信息导出
    _productExport = () => {
        const formData = {
            data: this.state.selectedRowKeys
        }
        NetWork_Post('productExport', formData, (response) => {
            const { status, msg } = response
            if (status === '0000') {
                message.success(msg)
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    //-- 删除厂商
    _deleteVendor = () => {
        const formData = {
            id: this.state.selectedRowKeys
        }
        NetWork_Post('vendorDel', formData, (response) => {
            const { status, msg } = response
            if (status === '0000') {
                message.success(msg)
                this._getVendorList();
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
            onOk: this._deleteVendor
        });
    }
    columns = [
        {
            align: 'center',
            title: '厂商编号',
            dataIndex: 'vendor_num',
            key: 'vendor_num',
        }, {
            align: 'center',
            title: '厂商名称',
            dataIndex: 'vendor_name',
            key: 'vendor_name',
        }, {
            align: 'center',
            title: '联系人',
            dataIndex: 'contact_name',
            key: 'contact_name',
        }, {
            align: 'center',
            title: '联系人电话',
            dataIndex: 'contact_tel',
            key: 'contact_tel',
        }, {
            align: 'center',
            title: '厂商标记',
            dataIndex: 'tag',
            key: 'tag',
            render: (text, record, index) => {
                if (record.tag === 1) {
                    return '供应商';
                }
                return '厂商';
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
                    <Link to={'/vendorEdit/' + text.id}>
                        编辑
                        </Link>
                    <Divider type="vertical" />
                    <Link to={'/vendorBindProduct/' + text.id}>
                        绑定商品
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
        const { uploading1, uploading2 } = this.state;
        const uploadProps1 = {
            showUploadList: false,
            onRemove: (file) => {
                this.setState(({ fileList1 }) => {
                    const index = fileList1.indexOf(file);
                    const newFileList1 = fileList1.slice();
                    newFileList1.splice(index, 1);
                    return {
                        fileList: newFileList1,
                    };
                });
            },
            beforeUpload: (file) => {
                this.setState({
                    fileList1: [file],
                    fileName1: file.name
                });
                return false;
            },
            fileList1: this.state.fileList1,
        };
        const uploadProps2 = {
            showUploadList: false,
            onRemove: (file) => {
                this.setState(({ fileList2 }) => {
                    const index = fileList2.indexOf(file);
                    const newFileList2 = fileList2.slice();
                    newFileList2.splice(index, 1);
                    return {
                        fileList: newFileList2,
                    };
                });
            },
            beforeUpload: (file) => {
                this.setState({
                    fileList2: [file],
                    fileName2: file.name
                });
                return false;
            },
            fileList2: this.state.fileList2,
        };
        return (
            <div style={{ width: '100%' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', width: '100%', height: 50 }}>
                    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                        <Button icon="plus" onClick={() => this.props.history.push('/vendorAdd')}>新建</Button>
                        <Button style={{ margin: '0 10px' }} icon='delete' onClick={this._showDeleteConfirm}>批量删除</Button>
                    </div>
                    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                        <Input value={this.state.search_key} prefix={<Icon type="search" style={{ color: '#b2b2b2' }} />} placeholder="搜索" onChange={(e) => this.setState({ search_key: e.target.value })} />
                        <Button style={{ margin: '0 10px' }} icon='search' onClick={() => { this._getVendorList() }}>搜索</Button>
                        <Button icon='file-done' onClick={() => { this._productExport() }}>导出</Button>
                    </div>
                </div>
                <Table
                    bordered
                    loading={false}
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
                            <Upload {...uploadProps1}>
                                请选择要导入的Excel: <Button size='small' icon='folder-open'>选取文件</Button>
                            </Upload>
                            <div style={{ margin: 10 }}>{this.state.fileName1 ? this.state.fileName1 : '未选择文件'}</div>
                        </div>
                        <Button
                            size='small'
                            onClick={this._handleUpload1}
                            disabled={this.state.fileList1.length === 0}
                            loading={uploading1}
                            icon='upload'
                        >
                            厂商信息导入
                        </Button>
                        <Divider type="vertical" />
                        <a href={this.state.vendorDownload}><Button size='small' icon='download'>导入厂商模板下载</Button></a>
                    </div>
                    <div style={{ margin: '10px 0', display: 'flex', justifyContent: 'flex-start', alignItems: 'center' }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                            <Upload {...uploadProps2}>
                                请选择要导入的Excel: <Button size='small' icon='folder-open'>选取文件</Button>
                            </Upload>
                            <div style={{ margin: 10 }}>{this.state.fileName2 ? this.state.fileName2 : '未选择文件'}</div>
                        </div>
                        <Button
                            size='small'
                            onClick={this._handleUpload2}
                            disabled={this.state.fileList2.length === 0}
                            loading={uploading2}
                            icon='upload'
                        >
                            厂商绑定商品导入
                        </Button>
                        <Divider type="vertical" />
                        <a href={this.state.productVenDownload}><Button size='small' icon='download'>厂商绑定商品导入模板下载</Button></a>
                    </div>
                </div>
            </div >
        )
    }
}