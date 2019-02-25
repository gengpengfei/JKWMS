// -- 编辑大客户方案
import React, { Component } from 'react';
import { Form, Button, Input, message, DatePicker, Radio, Table, Icon, Row, Col } from 'antd'
import { NetWork_Post } from '../../network/netUtils'
import moment from 'moment';
const { TextArea } = Input;
const FormItem = Form.Item;
const RadioGroup = Radio.Group;
class customerDemandOrderEdit extends Component {
    constructor(props) {
        super(props)
        this.state = {
            select_fa: this.props.match.params.select_fa,
            orderDetail: [],
            productList: [],
            bindProductList: [],
            selectedRow: [],
        }
    }
    componentDidMount() {
        //--获取订单详情
        this._getCustomerOrderDetail()
    }
    _getCustomerOrderDetail = () => {
        const formData = {
            id: this.props.match.params.id,
            select_fa: this.props.match.params.select_fa
        }
        console.log(formData);
        NetWork_Post('customerProgrammeOrderInfo', formData, (response) => {
            const { status, data, msg } = response
            console.log('customerProgrammeOrderInfo', response)
            if (status === '0000') {
                this.setState({
                    order_num: data.order_num,
                    bindProductList: data.productList[0].product,
                    order_end_date: data.order_end_date
                })
                this.props.form.setFieldsValue({
                    remark: data.remark,
                    order_num: data.order_num,
                    member_name: data.member_name,
                    order_end_date: moment(data.order_end_date, 'YYYY-MM-DD'),
                    t_type: data.t_type,
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        })
    }
    //-- 获取商品列表
    _getProductList = () => {
        const formData = {
            search_key: this.state.search_key
        }
        NetWork_Post('customerBindProduct', formData, (response) => {
            const { status, data, msg } = response
            console.log('customerBindProduct', response)
            if (status === '0000') {
                this.setState({
                    productList: data
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    handleSubmit = (e) => {
        e.preventDefault();
        this.props.form.validateFields((err, values) => {
            if (err) return;
            values.order_end_date = values.order_end_date.format('YYYY-MM-DD')
            values.product_info = this.state.bindProductList
            values.id = parseInt(this.props.match.params.id)
            values.select_fa = this.props.match.params.select_fa
            NetWork_Post('customerProgrammeEdit', values, (response) => {
                const { status, msg } = response
                if (status === '0000') {
                    message.success(msg)
                    return this.props.history.goBack();
                } else {
                    if (status === '1003') return this.props.history.push('/');
                    message.error(msg)
                }
            });
        });
    }
    //-- 商品check按钮选择
    rowSelection = {
        onChange: (index, row) => {
            this.setState({
                selectedRow: row
            })
        }
    }
    //-- 列表选择操作
    _clickRowSelection = (text) => {
        const check = this.state.bindProductList.findIndex((value) => {
            return value.product_num === text.product_num;
        })
        if (check !== -1) return;
        this.setState({ bindProductList: [...this.state.bindProductList, text] })
    }
    //-- 多选提交选择
    _clickRowSelectionAll = () => {
        const arr = []
        this.state.selectedRow.forEach(e => {
            const check = this.state.bindProductList.findIndex((value) => {
                return value.product_num === e.product_num;
            })
            if (check === -1) arr.push(e);
        });
        this.setState({ bindProductList: [...this.state.bindProductList, ...arr] })
    }
    handleChange = (index, value, field) => {
        const data = this.state.bindProductList;
        data[index][field] = value
        this.setState({
            bindProductList: data
        })
    }
    //-- 删除已选择的商品
    _delbindedProductList = (index) => {
        const { bindProductList } = this.state
        bindProductList.splice(index, 1);
        this.setState({ bindProductList: bindProductList })
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
            title: '规格',
            dataIndex: 'specifications_num',
            key: 'specifications_num',
        }, {
            align: 'center',
            title: '计量单位',
            dataIndex: 'unit',
            key: 'unit',
        }, {
            align: 'center',
            title: '保质期',
            dataIndex: 'shelf_month',
            key: 'shelf_month',
        }, {
            align: 'center',
            title: '箱规',
            dataIndex: 'carton',
            key: 'carton',
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
        const { getFieldDecorator } = this.props.form;
        const formItemLayout = {
            labelCol: {
                xs: { span: 12 },
                sm: { span: 3 },
            },
            wrapperCol: {
                xs: { span: 12 },
                sm: { span: 21 },
            },
        };
        return (
            <Form onSubmit={this.handleSubmit} style={{ width: '100%' }}>
                <FormItem
                    label="大客户需求说明:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('remark', {
                        rules: [{
                            required: true, message: '请输入大客户需求说明！',
                        }],
                    })(
                        <TextArea style={{ maxWidth: 300 }} rows={5} disabled />
                    )}
                </FormItem>
                <FormItem
                    label="订单号:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('order_num', {
                        rules: [{
                            required: true, message: '请输入订单号！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} disabled />
                    )}
                </FormItem>
                <FormItem
                    label="客户名称:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('member_name', {
                        rules: [{
                            required: true, message: '请输入客户名称！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} disabled />
                    )}
                </FormItem>
                <FormItem
                    label="过期时间:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('order_end_date', {
                        rules: [{
                            required: true, message: '请选择过期时间！',
                        }],
                    })(
                        <DatePicker showToday={false} disabled />
                    )}
                </FormItem>
                <FormItem
                    {...formItemLayout}
                    label="订单类型:"
                >
                    {getFieldDecorator('t_type', {
                        rules: [{
                            required: true, message: '请选择订单类型！',
                        }],
                    })(
                        <RadioGroup>
                            <Radio value={1}>普通订单</Radio>
                            <Radio value={2}>三农订单</Radio>
                        </RadioGroup>
                    )}
                </FormItem>
                <Row type='flex' align='center' style={{ width: '100%', marginBottom: 20 }}>
                    <Col xs={{ span: 12 }} sm={{ span: 3 }} style={{ textAlign: 'right' }}>供应商选择： </Col>
                    <Col xs={{ span: 12 }} sm={{ span: 21 }}>
                        <div style={{ display: 'flex', justifyContent: 'flex-start', alignItems: 'center', margin: '10px 0' }}>
                            <Input prefix={<Icon type="search" style={{ color: '#b2b2b2' }} />} placeholder="搜索" style={{ width: 200 }} onChange={(e) => this.setState({ search_key: e.target.value })} />
                            <Button style={{ margin: '0 10px' }} size='small' onClick={() => { this._getProductList() }}>搜索</Button>
                            <Button style={{ marginRight: '10px' }} size='small' onClick={() => { this._clickRowSelectionAll() }}>选择</Button>
                        </div>
                        <div style={{ maxHeight: 230, overflow: 'auto' }}>
                            <Table
                                bordered
                                loading={false}
                                pagination={false} //-- 不使用自带的分页器
                                rowSelection={this.rowSelection}
                                dataSource={this.state.productList}
                                columns={this.columns}
                                rowKey={(row) => row.product_num}
                                size='small'
                            />
                        </div>
                        <div style={{ maxHeight: 230, overflow: 'auto', width: '100%', marginTop: 20 }}>
                            <div style={{ width: '100%', height: 40, display: 'flex', justifyContent: 'space-around', alignItems: 'center', backgroundColor: '#efefef' }}>
                                <div style={{ flex: 1, textAlign: 'center' }}>商品编号</div>
                                <div style={{ flex: 2, textAlign: 'center' }}>商品名称</div>
                                <div style={{ flex: 1, textAlign: 'center' }}>进价</div>
                                <div style={{ flex: 1, textAlign: 'center' }}>门店价</div>
                                <div style={{ flex: 1, textAlign: 'center' }}>销售价</div>
                                <div style={{ flex: 1, textAlign: 'center' }}>数量</div>
                                <div style={{ flex: 1, textAlign: 'center' }}>操作</div>
                            </div>
                            {
                                this.state.bindProductList.map((e, i) => (
                                    < div style={{ width: '100%', height: 40, display: 'flex', justifyContent: 'space-around', alignItems: 'center', border: '1px solid #efefef', borderTop: '0px' }} key={e.product_num}>
                                        <div style={{ flex: 1, textAlign: 'center' }}>{e.product_num}</div>
                                        <div style={{ flex: 2, textAlign: 'center' }}>{e.product_name}</div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>
                                            <Input size="small" value={e.price ? String(e.price) : 0} style={{ width: '90%' }} onChange={({ target }) => this.handleChange(i, target.value, 'price')} />
                                        </div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>
                                            <Input size="small" value={e.md_price ? String(e.md_price) : 0} style={{ width: '90%' }} onChange={({ target }) => this.handleChange(i, target.value, 'md_price')} />
                                        </div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>
                                            <Input size="small" value={e.final_price ? String(e.final_price) : 0} style={{ width: '90%' }} onChange={({ target }) => this.handleChange(i, target.value, 'final_price')} />
                                        </div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>
                                            <Input size="small" value={e.buy_num ? String(e.buy_num) : 1} style={{ width: '90%' }} onChange={({ target }) => this.handleChange(i, target.value, 'buy_num')} />
                                        </div>
                                        <span onClick={() => this._delbindedProductList(i)} style={{ flex: 1, textAlign: 'center', cursor: 'pointer', color: '#4490ff' }}>删除</span>
                                    </div>))
                            }
                        </div>
                    </Col>
                </Row>
                <FormItem wrapperCol={{ span: 8, offset: 3 }}>
                    <div style={{ maxWidth: 300 }}>
                        <Button icon='save' htmlType="submit" style={{ float: "left" }}>
                            提交
                        </Button>
                        <Button onClick={() => { this.props.history.goBack() }} style={{ marginLeft: 10, float: "right" }} icon='rollback'>
                            返回
                        </Button>
                    </div>
                </FormItem>
            </Form>
        )
    }
}
export default Form.create()(customerDemandOrderEdit)