// -- 厂商绑定商品
import React, { Component } from 'react';
import { Table, Select, Button, Input, Icon, message } from 'antd'
import { NetWork_Post } from '../../network/netUtils'
const Option = Select.Option;
export default class vendorBindProduct extends Component {
    constructor(props) {
        super(props)
        this.state = {
            vendorDetail: [],
            vendorProductList: [],
            vendorBindedProduct: [],
            selectedRow: [],
            saveLoading: false,
            search_key: ''
        }
    }
    componentDidMount() {
        new Promise(this._getVendorDetail)
            .then(this._getVendorBindedProduct)
    }
    //-- 获取厂商详情
    _getVendorDetail = (resolve, reject) => {
        const formData = {
            id: this.props.match.params.id
        }
        NetWork_Post('vendorDetail', formData, (response) => {
            const { status, data, msg } = response
            if (status === '0000') {
                this.setState({
                    vendorDetail: data
                }, resolve())
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    //-- 获取厂商已经绑定的商品
    _getVendorBindedProduct = () => {
        const formData = {
            vendor_num: this.state.vendorDetail.vendor_num
        }
        NetWork_Post('vendorBindedProduct', formData, (response) => {
            const { status, data, msg } = response
            console.log(data)
            if (status === '0000') {
                this.setState({
                    vendorBindedProduct: data
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    //-- 获取商品列表
    _getProductList = () => {
        const formData = {
            search_key: this.state.search_key
        }
        NetWork_Post('vendorProductList', formData, (response) => {
            const { status, data, msg } = response
            if (status === '0000') {
                this.setState({
                    vendorProductList: data
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    //-- 列表选择操作
    _clickRowSelection = (text) => {
        const check = this.state.vendorBindedProduct.findIndex((value) => {
            return value.product_num === text.product_num;
        })
        if (check !== -1) return;
        text.vendor_num = this.state.vendorDetail.vendor_num;
        this.setState({ vendorBindedProduct: [...this.state.vendorBindedProduct, text] })
    }
    //-- 多选提交选择
    _clickRowSelectionAll = () => {
        const arr = []
        this.state.selectedRow.forEach(e => {
            const check = this.state.vendorBindedProduct.findIndex((value) => {
                return value.product_num === e.product_num;
            })
            e.vendor_num = this.state.vendorDetail.vendor_num;
            if (check === -1) arr.push(e);
        });
        this.setState({ vendorBindedProduct: [...this.state.vendorBindedProduct, ...arr] })
    }
    //-- check按钮选择
    rowSelection = {
        onChange: (index, row) => {
            this.setState({
                selectedRow: row
            })
        }
    }
    //-- 删除已选择的供应商
    _delVendorBindedProductList = (index) => {
        const { vendorBindedProduct } = this.state
        vendorBindedProduct.splice(index, 1);
        this.setState({ vendorBindedProduct: vendorBindedProduct })
    }
    handleChange = (index, value, field) => {
        const data = this.state.vendorBindedProduct;
        data[index][field] = value
        this.setState({
            vendorBindedProduct: data
        })
    }
    _handleSubmit = () => {
        if (this.state.vendorBindedProduct.length === 0) {
            message.warning('请选择绑定的商品');
            return;
        }
        this.setState({
            saveLoading: true
        })
        const formData = {
            vendorGood: this.state.vendorBindedProduct
        }
        NetWork_Post('vendorBindProduct', formData, (response) => {
            const { status, msg } = response
            if (status === '0000') {
                message.success(msg)
                return this.props.history.push('/vendor');
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
            this.setState({
                saveLoading: false
            })
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
            title: '操作',
            key: 'action',
            render: (text, record) => (
                <span style={{ cursor: 'pointer', color: '#4490ff' }} onClick={() => { this._clickRowSelection(text) }}>
                    选择
                    </span>
            ),
        }
    ];
    render() {
        return (
            <div style={{ width: '100%' }}>
                <div style={{ width: '100%', height: 40, display: 'flex', justifyContent: 'flex-start', alignItems: 'center' }}>
                    <div style={{ width: '10%', textAlign: 'right' }}>供应商编号： </div>
                    <div>{this.state.vendorDetail.vendor_num}</div>
                </div>
                <div style={{ width: '100%', height: 40, display: 'flex', justifyContent: 'flex-start', alignItems: 'center' }}>
                    <div style={{ width: '10%', textAlign: 'right' }}>供应商名称： </div>
                    <div>{this.state.vendorDetail.vendor_name}</div>
                </div>
                <div style={{ width: '100%', display: 'flex', justifyContent: 'flex-start', alignItems: 'center' }}>
                    <div style={{ width: '10%', textAlign: 'right' }}>供应商选择： </div>
                    <div style={{ width: '90%' }}>
                        <div style={{ display: 'flex', justifyContent: 'flex-start', alignItems: 'center', margin: '10px 0' }}>
                            <Input size="small" prefix={<Icon type="search" style={{ color: '#b2b2b2' }} />} placeholder="搜索" style={{ width: 200 }} onChange={(e) => this.setState({ search_key: e.target.value })} />
                            <Button style={{ margin: '0 10px' }} size='small' onClick={() => { this._getProductList() }}>搜索</Button>
                            <Button style={{ marginRight: '10px' }} size='small' onClick={() => { this._clickRowSelectionAll() }}>选择</Button>
                            <Button size='small' onClick={() => { this._getVendorBindedProduct() }}>查看已绑定</Button>
                        </div>
                        <div style={{ maxHeight: 230, overflow: 'auto' }}>
                            <Table
                                bordered
                                loading={false}
                                pagination={false} //-- 不使用自带的分页器
                                rowSelection={this.rowSelection}
                                dataSource={this.state.vendorProductList}
                                columns={this.columns}
                                rowKey={(row) => row.product_num}
                                size='small'
                            />
                        </div>
                        <div style={{ maxHeight: 230, overflow: 'auto', width: '100%', marginTop: 20 }}>
                            <div style={{ width: '100%', height: 40, display: 'flex', justifyContent: 'space-around', alignItems: 'center', backgroundColor: '#efefef' }}>
                                <div style={{ flex: 1, textAlign: 'center' }}>商品编号</div>
                                <div style={{ flex: 2, textAlign: 'center' }}>商品名称</div>
                                <div style={{ flex: 1, textAlign: 'center' }}>是否默认供应商</div>
                                <div style={{ flex: 1, textAlign: 'center' }}>商品状态</div>
                                <div style={{ flex: 1, textAlign: 'center' }}>合同类型</div>
                                <div style={{ flex: 1, textAlign: 'center' }}>正常进价</div>
                                <div style={{ flex: 1, textAlign: 'center' }}>是否可退货</div>
                                <div style={{ flex: 1, textAlign: 'center' }}>操作</div>
                            </div>
                            {
                                this.state.vendorBindedProduct.map((e, i) => (
                                    < div style={{ width: '100%', height: 40, display: 'flex', justifyContent: 'space-around', alignItems: 'center', border: '1px solid #efefef', borderTop: '0px' }} key={e.product_num}>
                                        <div style={{ flex: 1, textAlign: 'center' }}>{e.product_num}</div>
                                        <div style={{ flex: 2, textAlign: 'center' }}>{e.product_name}</div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>
                                            <Select placeholder='请选择' defaultValue={e.is_deultveor ? String(e.is_deultveor) : undefined} style={{ width: '90%' }} size='small' onChange={(value) => this.handleChange(i, value, 'is_deultveor')}>
                                                <Option value="1">是</Option>
                                                <Option value="0">否</Option>
                                            </Select>
                                        </div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>
                                            <Input size="small" value={e.contract_type ? String(e.contract_type) : undefined} style={{ width: '90%' }} onChange={({ target }) => this.handleChange(i, target.value, 'contract_type')} />
                                        </div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>
                                            <Select placeholder='请选择' defaultValue={e.pro_state ? String(e.pro_state) : undefined} style={{ width: '90%' }} size='small' onChange={(value) => this.handleChange(i, value, 'pro_state')}>
                                                <Option value="0">经销</Option>
                                                <Option value="1">代销</Option>
                                                <Option value="2">联营</Option>
                                            </Select>
                                        </div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>
                                            <Input size="small" value={e.v_price ? String(e.v_price) : undefined} style={{ width: '90%' }} onChange={({ target }) => this.handleChange(i, target.value, 'v_price')} />
                                        </div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>
                                            <Select placeholder='请选择' defaultValue={e.is_refund ? String(e.is_refund) : undefined} style={{ width: '90%' }} size='small' onChange={(value) => this.handleChange(i, value, 'is_refund')}>
                                                <Option value="0">是</Option>
                                                <Option value="1">否</Option>
                                            </Select>
                                        </div>
                                        <span onClick={() => this._delVendorBindedProductList(i)} style={{ flex: 1, textAlign: 'center', cursor: 'pointer', color: '#4490ff' }}>删除</span>
                                    </div>))
                            }
                        </div>
                        <div style={{ marginTop: 10, height: 40 }}>
                            <Button icon='save' loading={this.state.saveLoading} onClick={this._handleSubmit}>提交</Button>
                            <Button style={{ marginLeft: 10 }} icon='rollback' onClick={() => { this.props.history.goBack() }}>返回</Button>
                        </div>
                    </div>
                </div>
            </div >
        )
    }
}