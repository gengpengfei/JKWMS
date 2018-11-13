// -- 商品类型管理
import React, { Component } from 'react';
import { Table, Pagination, Button, Input, Icon, Divider, message, Modal, Upload } from 'antd'
import { Link } from 'react-router-dom'
import { NetWork_File, NetWork_Post } from '../../network/netUtils'
export default class goodsType extends Component {
    constructor(props) {
        super(props)
        this.state = {
            data: [],
            page: 1,
            limit: 10,
            search_key: '',
            selectedRowKeys: [],
            fileList: [],
            fileName: '',
            uploading: false,
        }
    }
    componentDidMount() {
        //-- 获取商品类型列表
        this._getGoodsTypeList()
        //-- 获取模版下载地址
        this._getProTypeDownload()
    }
    _getProTypeDownload = () => {
        const formData = {
        }
        NetWork_Post('proTypeDownload', formData, (response) => {
            const { status, data, msg } = response
            if (status === '0000') {
                this.setState({
                    proTypeDownload: data,
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    _getGoodsTypeList = () => {
        const formData = {
            search_key: this.state.search_key,
            limit: this.state.limit,
            page: this.state.page
        }
        NetWork_Post('getGoodsTypeList', formData, (response) => {
            const { status, data, msg } = response
            if (status === '0000') {
                this.setState({
                    data: data.goodTypeList,
                    total: data.goodTypeCount
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    _deleteGoodsType = () => {
        const formData = {
            pro_type_id: this.state.selectedRowKeys
        }
        NetWork_Post('productTypeDel', formData, (response) => {
            const { status, msg } = response
            if (status === '0000') {
                message.success(msg)
                this._getGoodsTypeList();
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
            onOk: this._deleteGoodsType
        });
    }
    //-- 文件上传
    _handleUpload = () => {
        const { fileList } = this.state;
        const formData = new FormData();
        fileList.forEach((file) => {
            formData.append('file', file);
        });
        this.setState({
            uploading: true,
        });
        NetWork_File('proTypeImport', formData, (response) => {
            const { status, msg } = response
            if (status === '0000') {
                message.success(msg)
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
            title: '商品类型编码',
            dataIndex: 'id',
            key: 'id',
        }, {
            align: 'center',
            title: '商品类型名称',
            dataIndex: 'pro_type_name',
            key: 'pro_type_name',
        }, {
            align: 'center',
            title: '父类型编码',
            dataIndex: 'parent_code',
            key: 'parent_code',
        }, {
            align: 'center',
            title: '采购人员',
            dataIndex: 'user_name',
            key: 'user_name',
        }, {
            align: 'center',
            title: '备注',
            dataIndex: 'remark',
            key: 'remark',
        }, {
            align: 'center',
            title: '修改人',
            dataIndex: 'modify_by',
            key: 'modify_by',
        }, {
            align: 'center',
            title: '修改时间',
            dataIndex: 'modify_date',
            key: 'modify_date',
        }, {
            align: 'center',
            title: '操作',
            key: 'action',
            render: (text, record) => {
                return (
                    <span>
                        <Link to={'/goodsTypeEdit/' + text.id}>
                            编辑
                        </Link>
                        <Divider type="vertical" />
                        <Link to={'/goodsTypeAddChild/' + text.id}>
                            添加子类
                        </Link>
                        <Divider type="vertical" />
                        <a href='###' onClick={() => this.setState({ selectedRowKeys: [text.id] }, this._showDeleteConfirm)}>
                            删除
                        </a>
                    </span >
                )
            },
        }
    ];
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
            this._getGoodsTypeList()
        })
    }
    onChangeLimit = (page, limit) => {
        this.setState({
            page: page,
            limit: limit
        }, () => {
            this._getGoodsTypeList()
        })
    }
    render() {
        const { uploading } = this.state;
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
                        <Button icon="plus" onClick={() => this.props.history.push('/goodsTypeAdd')}>新建</Button>
                        <Button style={{ margin: '0 10px' }} icon='delete' onClick={this._showDeleteConfirm}>批量删除</Button>
                    </div>
                    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                        <Input value={this.state.search_key} prefix={<Icon type="search" style={{ color: '#b2b2b2' }} />} placeholder="搜索" onChange={(e) => this.setState({ search_key: e.target.value })} />
                        <Button style={{ margin: '0 10px' }} icon='search' onClick={() => { this._getGoodsTypeList() }}>搜索</Button>
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
                            <Upload {...uploadProps}>
                                请选择要导入的Excel: <Button size='small' icon='folder-open'>选取文件</Button>
                            </Upload>
                            <div style={{ margin: 10 }}>{this.state.fileName ? this.state.fileName : '未选择文件'}</div>
                        </div>
                        <Button
                            size='small'
                            onClick={this._handleUpload}
                            disabled={this.state.fileList.length === 0}
                            loading={uploading}
                            icon='upload'
                        >
                            商品类型信息导入
                        </Button>
                        <Divider type="vertical" />
                        <a href={this.state.proTypeDownload}><Button size='small' icon='download'>商品类型信息导入模板下载</Button></a>
                    </div>
                </div>
            </div>
        )
    }
}