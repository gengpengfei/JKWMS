// -- 商品列表
import React, { Component } from 'react';
import { Table, Pagination, Button, Input, Icon, Divider, message, Upload } from 'antd'
import { Link } from 'react-router-dom'
import { NetWork_File, NetWork_Post } from '../../network/netUtils'
export default class goods extends Component {
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
        //-- 获取商品列表
        this._getGoodsList()
        //-- 获取商品信息导入模版下载地址
        this._getProductDownload()
        //-- 获取商品供应商关系导入模版下载地址
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
    _getProductDownload = () => {
        const formData = {
        }
        NetWork_Post('productDownload', formData, (response) => {
            const { status, data, msg } = response
            if (status === '0000') {
                this.setState({
                    productDownload: data,
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    _getGoodsList = () => {
        const formData = {
            search_key: this.state.search_key,
            limit: this.state.limit,
            page: this.state.page
        }
        NetWork_Post('getGoodsList', formData, (response) => {
            const { status, data, msg } = response
            if (status === '0000') {
                this.setState({
                    data: data.productList,
                    total: data.productCount
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
            this._getGoodsList()
        })
    }
    onChangeLimit = (page, limit) => {
        this.setState({
            page: page,
            limit: limit
        }, () => {
            this._getGoodsList()
        })
    }
    columns = [
        {
            align: 'center',
            title: '商品编号',
            dataIndex: 'product_num',
            key: 'product_num',
        }, {
            align: 'center',
            title: '商品名称',
            dataIndex: 'product_name',
            key: 'product_name',
        }, {
            align: 'center',
            title: '商品类型',
            dataIndex: 'product_type',
            key: 'product_type',
        }, {
            align: 'center',
            title: '进价',
            dataIndex: 'price',
            key: 'price',
        }, {
            align: 'center',
            title: '销售价',
            dataIndex: 'sale_price',
            key: 'sale_price',
        }, {
            align: 'center',
            title: '保质期',
            dataIndex: 'shelf_month',
            key: 'shelf_month',
        }, {
            align: 'center',
            title: '商品规格编码',
            dataIndex: 'specifications_num',
            key: 'specifications_num',
        }, {
            align: 'center',
            title: '计量单位',
            dataIndex: 'unit',
            key: 'unit',
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
                    <Link to={'/goodsBindSupplier/' + text.id}>
                        绑定供应商
                    </Link>
                </span>
            ),
        }
    ];
    //-- 商品信息导入文件上传
    _handleUpload1 = () => {
        const { fileList1 } = this.state;
        const formData = new FormData();
        fileList1.forEach((file) => {
            formData.append('file', file);
        });
        this.setState({
            uploading1: true,
        });
        NetWork_File('productImport', formData, (response) => {
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
    //-- 商品供应商关系信息导入文件上传
    _handleUpload2 = () => {
        const { fileList2 } = this.state;
        const formData = new FormData();
        fileList2.forEach((file) => {
            formData.append('file', file);
        });
        this.setState({
            uploading2: true,
        });
        NetWork_File('productVenImport', formData, (response) => {
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
    //-- 商品信息导出
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
                        <Button icon="cloud-download">商品同步</Button>
                        <Button style={{ margin: '0 10px' }} icon="cloud-download">类别同步</Button>
                        <Button icon="cloud-download">供应商同步</Button>
                    </div>
                    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                        <Input value={this.state.search_key} prefix={<Icon type="search" style={{ color: '#b2b2b2' }} />} placeholder="搜索" onChange={(e) => this.setState({ search_key: e.target.value })} />
                        <Button style={{ margin: '0 10px' }} icon='search' onClick={() => { this._getGoodsList() }}>搜索</Button>
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
                            商品信息导入
                        </Button>
                        <Divider type="vertical" />
                        <a href={this.state.productDownload}><Button size='small' icon='download'>商品信息导入模板下载</Button></a>
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
                            商品供应商关系信息导入
                        </Button>
                        <Divider type="vertical" />
                        <a href={this.state.productVenDownload}><Button size='small' icon='download'>商品供应商关系信息模板下载</Button></a>
                    </div>
                </div>
            </div >
        )
    }
}