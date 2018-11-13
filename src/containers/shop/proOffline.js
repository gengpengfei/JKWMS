// -- 商品申请下架
import React, { Component } from 'react';
import { Table, Pagination, Button, Input, Icon, Divider, message, Modal } from 'antd'
import { NetWork_Post } from '../../network/netUtils'
export default class proOffline extends Component {
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
    componentWillMount() {
        //-- 根据路由判断当前显示的列表
        const arr = this.props.location.pathname.split('/')
        var apply_state;
        switch (arr[1]) {
            case 'proOffline':
                apply_state = null
                break;
            case 'proOfflineFirst':
                apply_state = 4
                break;
            case 'proOfflineSecond':
                apply_state = 3
                break;
            case 'proOfflineThird':
                apply_state = 2
                break;
            default:
                apply_state = -1
        }
        this.setState({
            apply_state: apply_state
        })
    }
    componentDidMount() {
        //-- 获取商品下架列表
        this._getProOfflineList()
    }
    _getProOfflineList = () => {
        const formData = {
            search_key: this.state.search_key,
            limit: this.state.limit,
            page: this.state.page,
            apply_state: this.state.apply_state
        }
        NetWork_Post('proOfflineList', formData, (response) => {
            const { status, data, msg } = response
            if (status === '0000') {
                console.log(data)
                this.setState({
                    data: data.proOfflineList,
                    total: data.proOfflineCount
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    //-- 删除申请
    _proOfflineDel = () => {
        const formData = {
            id: this.state.deleteRowKeys
        }
        NetWork_Post('proOfflineDel', formData, (response) => {
            const { status, msg } = response
            if (status === '0000') {
                message.success(msg)
                this._getProOfflineList();
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
            onOk: this._proOfflineDel
        });
    }
    //-- 申请下架
    _proOffline = (row) => {
        const formData = {
            product_num: row.product_num,
            product_name: row.product_name
        }
        NetWork_Post('proApplyUnder', formData, (response) => {
            const { status, msg } = response
            if (status === '0000') {
                message.success(msg)
                this._getProOfflineList();
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    //-- 申请通过
    _proOfflineReviewed = (row) => {
        const formData = {
            id: row.id,
            apply_state: parseInt(row.apply_state) - 1
        }
        NetWork_Post('proOfflineReviewed', formData, (response) => {
            const { status, msg } = response
            if (status === '0000') {
                message.success(msg)
                this._getProOfflineList();
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    //-- 申请拒绝
    _proOfflineRefuse = (id) => {
        const formData = {
            id: id
        }
        NetWork_Post('proOfflineRefuse', formData, (response) => {
            const { status, msg } = response
            if (status === '0000') {
                message.success(msg)
                this._getProOfflineList();
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
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
            title: '商品规格',
            dataIndex: 'specifications_num',
            key: 'specifications_num',
        }, {
            align: 'center',
            title: '申请人',
            dataIndex: 'apply_by',
            key: 'apply_by',
        }, {
            align: 'center',
            title: '申请时间',
            dataIndex: 'apply_date',
            key: 'apply_date',
        }, {
            align: 'center',
            title: '审核状态',
            dataIndex: 'apply_state',
            key: 'apply_state',
            render: (text, record, index) => {
                switch (record.apply_state) {
                    case 5:
                        return '已拒绝';
                    case 4:
                        return '初审中';
                    case 3:
                        return '二审中';
                    case 2:
                        return '终审中';
                    case 1:
                        return '终审通过';
                    default:
                        return '待申请';
                }
            }
        }, {
            align: 'center',
            title: '操作',
            key: 'action',
            render: (text, record) => {
                switch (text.apply_state) {
                    case 5:
                        return (
                            <span>
                                <a href='###' onClick={() => this.setState({ deleteRowKeys: text.id }, this._showDeleteConfirm)}>
                                    删除
                                </a>
                            </span >
                        )
                    case 1:
                        return;
                    case null:
                        return (
                            <span>
                                <a href='###' onClick={() => this._proOffline(text)}>
                                    申请下架
                                </a>
                            </span>
                        )
                    default:
                        return (this.state.apply_state === -1 || this.state.apply_state === null) ? null : (
                            <span>
                                <a href='###' onClick={() => this._proOfflineReviewed(text)}>
                                    申请通过
                                </a>
                                <Divider type="vertical" />
                                <a href='###' onClick={() => this._proOfflineRefuse(text.id)}>
                                    申请拒绝
                                </a>
                            </span >
                        )
                }
            }
        }
    ];
    onChangePage = (pageNumber) => {
        this.setState({
            page: pageNumber
        }, () => {
            this._getProOfflineList()
        })
    }
    onChangeLimit = (page, limit) => {
        this.setState({
            page: page,
            limit: limit
        }, () => {
            this._getProOfflineList()
        })
    }
    render() {
        return (
            <div style={{ width: '100%' }}>
                <div style={{ display: 'flex', justifyContent: 'flex-end', alignItems: 'center', width: '100%', height: 50 }}>
                    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                        <Input value={this.state.search_key} prefix={<Icon type="search" style={{ color: '#b2b2b2' }} />} placeholder="搜索" onChange={(e) => this.setState({ search_key: e.target.value })} />
                        <Button style={{ margin: '0 10px' }} icon='search' onClick={() => { this._getProOfflineList() }}>搜索</Button>
                    </div>
                </div>
                <Table
                    bordered
                    loading={false}
                    pagination={false} //-- 不使用自带的分页器
                    rowSelection={null} //-- 配置表格行是否可选
                    dataSource={this.state.data}
                    columns={this.columns}
                    rowKey={(row) => row.product_id}
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