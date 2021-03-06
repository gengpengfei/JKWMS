// -- 大客户订单方案审核列表
import React, { Component } from 'react';
import { Table, Pagination, Button, Input, Icon, DatePicker, message, Modal, Divider, Select } from 'antd'
import { Link } from 'react-router-dom'
import { NetWork_Post } from '../../network/netUtils'
const { RangePicker } = DatePicker;
const Option = Select.Option
export default class CustomerProgrammeReviewed extends Component {
    constructor(props) {
        super(props)
        this.state = {
            data: [],
            page: 1,
            limit: 10,
            start_date: '',
            end_date: '',
            loading: false,
            search_key: '',
            selectedRowKeys: []
        }
    }
    componentDidMount() {
        //-- 获取待审核列表
        this._getDemandOrderList()
    }
    _getDemandOrderList = () => {
        const formData = {
            search_key: this.state.search_key,
            limit: this.state.limit,
            page: this.state.page
        }
        if (this.state.start_date && this.state.end_date) {
            formData.start_date = this.state.start_date
            formData.end_date = this.state.end_date
        }
        if (this.state.t_type) {
            formData.t_type = this.state.t_type
        }
        this.setState({
            loading: true
        })
        NetWork_Post('programmeReviewedList', formData, (response) => {
            const { status, data, msg } = response
            console.log('CustomerDemandOrder', response)
            if (status === '0000') {
                this.setState({
                    data: data.demandOrderList,
                    total: data.demandOrderCount
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
    onChangePage = (pageNumber) => {
        this.setState({
            page: pageNumber
        }, () => {
            this._getDemandOrderList()
        })
    }
    onChangeLimit = (page, limit) => {
        this.setState({
            page: page,
            limit: limit
        }, () => {
            this._getDemandOrderList()
        })
    }
    _showSubmitReviewedConfirm = () => {
        Modal.confirm({
            title: '系统提示',
            content: '您确定要一键审核？',
            okType: 'danger',
            onOk: this._submitReviewedOrder
        });
    }
    _submitReviewedOrder = () => {
        const formData = {
            id: this.state.selectedRowKeys[0],
            in_flag: 8
        }
        NetWork_Post('programmeReviewed', formData, (response) => {
            const { status, msg } = response
            if (status === '0000') {
                message.success(msg)
                this._getDemandOrderList();
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    _onChangePicker = (date, dateString) => {
        this.setState({
            start_date: dateString[0],
            end_date: dateString[1]
        })
    }
    _onChangeTType = (value) => {
        this.setState({
            t_type: value
        })
    }
    columns = [
        {
            align: 'center',
            title: '订单编号',
            dataIndex: 'order_num',
            key: 'order_num',
        }, {
            align: 'center',
            title: '订单类型',
            dataIndex: 't_type',
            key: 't_type',
            render: (text, record, index) => {
                return text === 1 ? '普通订单' : text === 2 ? '三农订单' : null
            }
        }, {
            align: 'center',
            title: '订单状态',
            dataIndex: 'in_flag',
            key: 'in_flag',
            render: (text, record, index) => {
                return text === 0 ? '未提交' : text === 1 ? '采购提交方案中' : text === 2 ? '采购方案初审中' : text === 3 ? '确认方案中' : text === 4 ? '方案未通过' : text === 5 ? '渠道经理审核中' : text === 6 ? '徐总审核中' : text === 7 ? '钟总审核中' : text === 8 ? '方案已通过' : text === 9 ? '已生成订单' : null
            }
        }, {
            align: 'center',
            title: '客户名称',
            dataIndex: 'member_name',
            key: 'member_name',
        }, {
            align: 'center',
            title: '收件人',
            dataIndex: 'receive_name',
            key: 'receive_name',
        }, {
            align: 'center',
            title: '收件手机',
            dataIndex: 'receive_tel',
            key: 'receive_tel',
        }, {
            align: 'center',
            title: '市',
            dataIndex: 'receive_city',
            key: 'receive_city',
        }, {
            align: 'center',
            title: '创建人',
            dataIndex: 'create_by',
            key: 'create_by',
        }, {
            align: 'center',
            title: '过期时间',
            dataIndex: 'order_end_date',
            key: 'order_end_date',
        }, {
            align: 'center',
            title: '操作',
            key: 'action',
            render: (text, record) => {
                return <span>
                    {
                        record.in_flag === 2 ? <Link to={'/CustomerProgrammeReviewedInfo/' + text.id}>方案初审</Link> : null
                    }
                    {
                        record.in_flag === 3 ? <Link to={'/CustomerProgrammeReviewedInfo/' + text.id}>确认方案</Link> : null
                    }
                    {
                        record.in_flag === 5 ? <Link to={'/CustomerProgrammeReviewedInfo/' + text.id}>确认方案初审</Link> : null
                    }
                    {
                        record.in_flag === 6 ? <div>
                            <span style={{ cursor: 'pointer', color: '#4490ff' }} onClick={() => this.setState({ selectedRowKeys: [text.id] }, this._showSubmitReviewedConfirm)}>一键审核</span>
                            <Divider type="vertical" />
                            <Link to={'/CustomerProgrammeReviewedInfo/' + text.id}>确认方案二审</Link>
                        </div> : null
                    }
                    {
                        record.in_flag === 7 ? <div>
                            <span style={{ cursor: 'pointer', color: '#4490ff' }} onClick={() => this.setState({ selectedRowKeys: [text.id] }, this._showSubmitReviewedConfirm)}>一键审核</span>
                            <Divider type="vertical" />
                            <Link to={'/CustomerProgrammeReviewedInfo/' + text.id}>确认方案终审</Link>
                        </div> : null
                    }
                </span>
            },
        }
    ];
    render() {
        return (
            <div style={{ width: '100%' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', width: '100%', height: 50 }}>
                    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                        <Button icon="plus" onClick={() => this.props.history.push('/customerDemandOrderAdd')}>新建</Button>
                    </div>
                    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                        <RangePicker onChange={this._onChangePicker} style={{ width: 300, marginRight: 10 }} />
                        <Select onChange={this._onChangeTType} allowClear={true} placeholder='选择订单类型' style={{ width: 200, marginRight: 10 }}>
                            <Option value={1}>普通订单</Option>
                            <Option value={2}>三农订单</Option>
                        </Select>
                        <Input value={this.state.search_key} prefix={<Icon type="search" style={{ color: '#b2b2b2' }} />} placeholder="搜索" onChange={(e) => this.setState({ search_key: e.target.value })} style={{ width: 200 }} />
                        <Button style={{ margin: '0 10px' }} icon='search' onClick={() => { this._getDemandOrderList() }}>搜索</Button>
                    </div>
                </div>
                <Table
                    bordered
                    loading={this.state.loading}
                    pagination={false} //-- 不使用自带的分页器
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