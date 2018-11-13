// -- 绑定供应商
import React, { Component } from 'react';
import { Table, Select, Button, Input, Icon, message } from 'antd'
import { NetWork_Post } from '../../network/netUtils'
const Option = Select.Option;
export default class goodsBindSupplier extends Component {
    constructor(props) {
        super(props)
        this.state = {
            productDetail: [],
            productVendorList: [],
            selectedVendorList: [],
            selectedRowKeys: [],
            saveLoading: false,
            search_key: ''
        }
    }
    componentDidMount() {
        //-- 获取商品详情
        this._getProductDetail()
    }
    _getProductDetail = () => {
        const formData = {
            product_id: this.props.match.params.id
        }
        NetWork_Post('productDetail', formData, (response) => {
            const { status, data, msg } = response
            if (status === '0000') {
                this.setState({
                    productDetail: data
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    //-- 获取供应商列表
    _getProductVendorList = () => {
        const formData = {
            search_key: this.state.search_key
        }
        NetWork_Post('productVendorList', formData, (response) => {
            const { status, data, msg } = response
            if (status === '0000') {
                this.setState({
                    productVendorList: data
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    columns = [
        {
            align: 'center',
            title: '供应商编号',
            dataIndex: 'vendor_num',
            key: 'vendor_num',
        }, {
            align: 'center',
            title: '供应商名称',
            dataIndex: 'vendor_name',
            key: 'vendor_name',
        }, {
            align: 'center',
            title: '操作',
            key: 'action',
            render: (text, record) => (
                <span>
                    <a href='###' onClick={() => { this._clickRowSelection(text) }}>
                        选择
                    </a>
                </span>
            ),
        }
    ];
    //-- 列表选择操作
    _clickRowSelection = (text) => {
        const check = this.state.selectedVendorList.findIndex((value) => {
            return value.vendor_num === text.vendor_num;
        })
        if (check !== -1) return;
        text.pro_code = this.state.productDetail.product_num;
        this.setState({ selectedVendorList: [...this.state.selectedVendorList, text] })
    }
    //-- 多选提交选择
    _clickRowSelectionAll = () => {
        const arr = []
        this.state.selectedRowKeys.forEach(e => {
            const check = this.state.selectedVendorList.findIndex((value) => {
                return value.vendor_num === e.vendor_num;
            })
            if (check === -1) arr.push(e);
        });
        this.setState({ selectedVendorList: [...this.state.selectedVendorList, ...arr] })
    }
    //-- check按钮选择
    rowSelection = {
        onChange: (index, row) => {
            this.setState({
                selectedRowKeys: row
            })
        }
    }
    handleChange = (index, value, field) => {
        const data = this.state.selectedVendorList;
        data[index][field] = value
        data[index]['pro_code'] = this.state.productDetail.product_num
        this.setState({
            selectedVendorList: data
        })
    }
    _handleSubmit = () => {
        if (this.state.selectedVendorList.length === 0) {
            message.warning('请选择绑定的供应商');
            return;
        }
        this.setState({
            saveLoading: true
        })
        const formData = {
            vendorGood: this.state.selectedVendorList
        }
        NetWork_Post('productBindVendor', formData, (response) => {
            const { status, msg } = response
            if (status === '0000') {
                message.success(msg)
                return this.props.history.push('/goods');
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
            this.setState({
                saveLoading: false
            })
        });
    }
    render() {
        return (
            <div style={{ width: '100%' }}>
                <div style={{ width: '100%', height: 40, display: 'flex', justifyContent: 'flex-start', alignItems: 'center' }}>
                    <div style={{ width: '10%', textAlign: 'right' }}>商品编号： </div><div>{this.state.productDetail.product_num}</div>
                </div>
                <div style={{ width: '100%', height: 40, display: 'flex', justifyContent: 'flex-start', alignItems: 'center' }}>
                    <div style={{ width: '10%', textAlign: 'right' }}>商品名称： </div><div>{this.state.productDetail.product_name}</div>
                </div>
                <div style={{ width: '100%', minHeight: 40, display: 'flex', justifyContent: 'flex-start', alignItems: 'center' }}>
                    <div style={{ width: '10%', textAlign: 'right' }}>供应商选择： </div>
                    <div style={{ width: '90%' }}>
                        <div style={{ display: 'flex', justifyContent: 'flex-start', alignItems: 'center', marginBottom: 10 }}>
                            <Input size="small" prefix={<Icon type="search" style={{ color: '#b2b2b2' }} />} placeholder="搜索" style={{ width: 200 }} onChange={(e) => this.setState({ search_key: e.target.value })} />
                            <Button style={{ margin: '0 10px' }} size='small' onClick={() => { this._getProductVendorList() }}>搜索</Button>
                            <Button size='small' onClick={() => { this._clickRowSelectionAll() }}>选择</Button>
                        </div>
                        <div style={{ maxHeight: 230, overflow: 'auto' }}>
                            <Table
                                bordered
                                loading={false}
                                pagination={false} //-- 不使用自带的分页器
                                rowSelection={this.rowSelection}
                                dataSource={this.state.productVendorList}
                                columns={this.columns}
                                rowKey={(row) => row.vendor_num}
                                size='small'
                            />
                        </div>
                        <div style={{ maxHeight: 230, overflow: 'auto', width: '100%', marginTop: 20 }}>
                            <div style={{ width: '100%', height: 40, display: 'flex', justifyContent: 'space-around', alignItems: 'center', backgroundColor: '#efefef' }}>
                                <div style={{ flex: 1, textAlign: 'center' }}>供应商编号</div>
                                <div style={{ flex: 2, textAlign: 'center' }}>供应商名称</div>
                                <div style={{ flex: 1, textAlign: 'center' }}>是否默认供应商</div>
                                <div style={{ flex: 1, textAlign: 'center' }}>商品状态</div>
                                <div style={{ flex: 1, textAlign: 'center' }}>合同类型</div>
                                <div style={{ flex: 1, textAlign: 'center' }}>正常进价</div>
                                <div style={{ flex: 1, textAlign: 'center' }}>是否可退货</div>
                                <div style={{ flex: 1, textAlign: 'center' }}>操作</div>
                            </div>
                            {
                                this.state.selectedVendorList.map((e, i) => (
                                    <div style={{ width: '100%', height: 40, display: 'flex', justifyContent: 'space-around', alignItems: 'center', border: '1px solid #efefef', borderTop: '0px' }} key={i}>
                                        <div style={{ flex: 1, textAlign: 'center' }}>{e.vendor_num}</div>
                                        <div style={{ flex: 2, textAlign: 'center' }}>{e.vendor_name}</div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>
                                            <Select placeholder='请选择' style={{ width: '90%' }} size='small' onChange={(value) => this.handleChange(i, value, 'is_deultveor')}>
                                                <Option value="1">是</Option>
                                                <Option value="0">否</Option>
                                            </Select>
                                        </div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>
                                            <Input size="small" style={{ width: '90%' }} onChange={({ target }) => this.handleChange(i, target.value, 'contract_type')} />
                                        </div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>
                                            <Select placeholder='请选择' style={{ width: '90%' }} size='small' onChange={(value) => this.handleChange(i, value, 'pro_state')}>
                                                <Option value="0">经销</Option>
                                                <Option value="1">代销</Option>
                                                <Option value="2">联营</Option>
                                            </Select>
                                        </div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>
                                            <Input size="small" style={{ width: '90%' }} onChange={({ target }) => this.handleChange(i, target.value, 'v_price')} />
                                        </div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>
                                            <Select placeholder='请选择' style={{ width: '90%' }} size='small' onChange={(value) => this.handleChange(i, value, 'is_refund')}>
                                                <Option value="0">是</Option>
                                                <Option value="1">否</Option>
                                            </Select>
                                        </div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>删除</div>
                                    </div>))
                            }
                        </div>
                        <div style={{ marginTop: 10, height: 40 }}>
                            <Button icon='save' loading={this.state.saveLoading} onClick={this._handleSubmit}>提交</Button>
                            <Button style={{ marginLeft: 10 }} icon='rollback'>返回</Button>
                        </div>
                    </div>
                </div>
            </div >
        )
    }
}