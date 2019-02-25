// -- 大客户方案生成订单
import React, { Component } from 'react';
import { Form, Button, Input, message, DatePicker, Radio, Row, Col, Select } from 'antd'
import { Link } from 'react-router-dom'
import { NetWork_Post } from '../../network/netUtils'
import moment from 'moment';
const { TextArea } = Input;
const FormItem = Form.Item;
const RadioGroup = Radio.Group;
const Option = Select.Option;
class customerCreateOrder extends Component {
    constructor(props) {
        super(props)
        this.state = {
            orderDetail: [],
            productList: [],
            SHOrderList: [],
            provinceList: [],
            cityList: [],
            areaList: [],
            proType: [],
            val: 0
        }
    }

    componentDidMount() {
        //--获取待生成订单详情
        this._getCustomerProgrammeOrderDetail()
        //-- 获取顶级地址
        this._getProvinceList()
    }
    _getCustomerProgrammeOrderDetail = () => {
        const formData = {
            id: this.props.match.params.id
        }
        NetWork_Post('bigCustomerOrderShDetail', formData, (response) => {
            const { status, data, msg } = response
            console.log('bigCustomerOrderShDetail', response)
            if (status === '0000') {
                this.setState({
                    order_num: data.order_num,
                    productList: data.productList,
                    order_end_date: data.order_end_date,
                    orderDetail: data,
                    SHOrderList: data.orderList
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
    _getProvinceList = () => {
        const formData = {

        }
        NetWork_Post('areaList', formData, (response) => {
            console.log('areaList', response)
            const { status, msg, data } = response
            if (status === '0000') {
                this.setState({
                    provinceList: data
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    _getCityList = (area_name) => {
        const formData = {
            area_name: area_name,
            area_level: 1
        }
        NetWork_Post('areaList', formData, (response) => {
            console.log('areaList', response)
            const { status, msg, data } = response
            if (status === '0000') {
                this.setState(
                    {
                        cityList: data,
                        areaList: []
                    },
                    () => this.props.form.setFieldsValue({
                        city: undefined,
                        area: undefined
                    })
                )

            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    _getAreaList = (area_name) => {
        const formData = {
            area_name: area_name,
            area_level: 2
        }
        NetWork_Post('areaList', formData, (response) => {
            console.log('areaList', response)
            const { status, msg, data } = response
            if (status === '0000') {
                this.setState(
                    {
                        areaList: data
                    },
                    () => this.props.form.setFieldsValue({
                        area: undefined
                    })
                )
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
            values.id = parseInt(this.props.match.params.id)
            NetWork_Post('customerProgrammeOrderAdd', values, (response) => {
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

    _changeOrderType = (order_type) => {
        if (order_type === 1) {
            this.setState(
                {
                    proType: [{ type: 1, name: '集团内客户' }, { type: 2, name: '集团外客户' }]
                },
                () => this.props.form.setFieldsValue({
                    pro_type: undefined
                })
            )
        }
        if (order_type === 2) {
            this.setState(
                {
                    proType: [{ type: 3, name: '吸粉' }, { type: 4, name: '试吃' }, { type: 5, name: '客情' }]
                },
                () => this.props.form.setFieldsValue({
                    pro_type: undefined
                })
            )
        }
    }
    _changeProduceNum = (i, text) => {
        let productList = this.state.productList
        this.setState({
            productList: []
        })
    }
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
                        <TextArea style={{ maxWidth: 300 }} rows={3} disabled />
                    )}
                </FormItem>
                <FormItem
                    label="订单号:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('order_num', {
                        rules: [{
                            required: true, message: '请输入厂商编号！',
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
                        <RadioGroup disabled>
                            <Radio value={1}>普通订单</Radio>
                            <Radio value={2}>三农订单</Radio>
                        </RadioGroup>
                    )}
                </FormItem>
                <FormItem
                    label="收件人名称:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('receive_name', {
                        rules: [{
                            required: true, message: '请输入收件人名称！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="收件人手机:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('receive_mobile', {
                        rules: [{
                            required: true, message: '请输入收件人手机！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="收件人座机:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('receive_tel', {
                        rules: [],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="收件人省/市/区:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('province', {
                        rules: [{
                            required: true, message: '请选择仓库所在省！',
                        }],
                    })(
                        <Select placeholder='请选择' style={{ maxWidth: 100 }} onChange={this._getCityList} >
                            {
                                this.state.provinceList ? this.state.provinceList.map((item, index) => (
                                    <Option key={index} value={item.area_name}>{item.area_name}</Option>
                                )) : null
                            }
                        </Select>
                    )}
                    {getFieldDecorator('city', {
                        rules: [{
                            required: true, message: '请选择仓库所在市！',
                        }],
                    })(
                        <Select placeholder='请选择' style={{ maxWidth: 100 }} onChange={this._getAreaList}>
                            {
                                this.state.cityList ? this.state.cityList.map((item, index) => (
                                    <Option key={index} value={item.area_name} >{item.area_name}</Option>
                                )) : null
                            }
                        </Select>
                    )}
                    {getFieldDecorator('area', {
                        rules: [{
                            required: true, message: '请选择仓库所在区！',
                        }],
                    })(
                        <Select placeholder='请选择' style={{ maxWidth: 100 }}>
                            {
                                this.state.areaList ? this.state.areaList.map((item, index) => (
                                    <Option key={index} value={item.area_name}>{item.area_name}</Option>
                                )) : null
                            }
                        </Select>
                    )}
                </FormItem>
                <FormItem
                    label="收件人地址:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('receive_address', {
                        rules: [{ required: true, message: '请输入收件人地址！' }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="发票抬头:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('invoice_header', {
                        rules: [],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="配送时间:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('order_send_date', {
                        rules: [],
                    })(
                        <DatePicker showToday={false} />
                    )}
                </FormItem>

                <FormItem
                    label="订单类型:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('order_type', {
                        rules: [{ required: true, message: '请选择订单类型！' }],
                    })(
                        <Select placeholder='请选择' style={{ maxWidth: 140 }} onChange={this._changeOrderType}>
                            <Option key={1} value={1} >销售订单</Option>
                            <Option key={2} value={2} >非销售订单</Option>
                        </Select>
                    )}
                    {getFieldDecorator('pro_type', {
                        rules: [{ required: true, message: '请选择订单使用原因！' }],
                    })(
                        <Select placeholder='请选择' style={{ maxWidth: 140, marginLeft: 20 }}>
                            {
                                this.state.proType.map((item, index) => (
                                    <Option key={item.type} value={item.type} >{item.name}</Option>
                                ))
                            }
                        </Select>
                    )}
                </FormItem>
                <FormItem
                    label="订单备注:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('order_remark', {
                        rules: [],
                    })(
                        <TextArea style={{ maxWidth: 300 }} rows={3} />
                    )}
                </FormItem>
                <FormItem
                    label="审核状态:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('in_flag', {
                        rules: [],
                    })(
                        <div style={{ color: 'green' }}>{this.state.orderDetail.in_flag === 8 ? '方案已通过' : '已生成订单'}</div>
                    )}
                </FormItem>
                {
                    this.state.SHOrderList.length > 0 ? <Row type='flex' align='center' style={{ width: '100%', marginBottom: 20 }}>
                        <Col xs={{ span: 12 }} sm={{ span: 3 }} style={{ textAlign: 'right', color: '#000' }}>已生成订单列表： </Col>
                        <Col xs={{ span: 12 }} sm={{ span: 21 }} style={{ marginTop: 0 }}>111</Col>
                    </Row> : null
                }
                <Row type='flex' align='center' style={{ width: '100%', marginBottom: 20 }}>
                    <Col xs={{ span: 12 }} sm={{ span: 3 }} style={{ textAlign: 'right', color: '#000' }}>方案列表： </Col>
                    <Col xs={{ span: 12 }} sm={{ span: 21 }} style={{ marginTop: -15 }}>
                        {
                            this.state.productList.map((item, index) => (
                                <div style={{ maxHeight: 230, overflow: 'auto', width: '100%', marginTop: 20 }} key={index}>
                                    <div style={{ width: '100%', height: 40, display: 'flex', justifyContent: 'space-around', alignItems: 'center', border: '1px solid #efefef' }}>
                                        <span style={{ flex: 4, fontWeight: 500, marginLeft: 20 }}>方案{index + 1}（ 总利润:{item.allProfit},  销售总额:{item.allSale},  总数量:{item.allAmount} ）（单位：元）</span>
                                    </div>
                                    <div style={{ width: '100%', height: 40, display: 'flex', justifyContent: 'space-around', alignItems: 'center', backgroundColor: '#efefef' }}>
                                        <div style={{ flex: 1, textAlign: 'center' }}>商品编号</div>
                                        <div style={{ flex: 2, textAlign: 'center' }}>商品名称</div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>规格</div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>进价</div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>门店价</div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>折扣率</div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>最终销售价</div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>原始数量</div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>剩余数量</div>
                                        <div style={{ flex: 1, textAlign: 'center' }}>使用数量</div>
                                    </div>
                                    {
                                        item.product.map((e, i) => (
                                            < div style={{ width: '100%', height: 40, display: 'flex', justifyContent: 'space-around', alignItems: 'center', border: '1px solid #efefef', borderTop: '0px' }} key={i}>
                                                <div style={{ flex: 1, textAlign: 'center' }}>{e.product_num}</div>
                                                <div style={{ flex: 2, textAlign: 'center' }}>{e.product_name}</div>
                                                <div style={{ flex: 1, textAlign: 'center' }}>
                                                    {e.unit}
                                                </div>
                                                <div style={{ flex: 1, textAlign: 'center' }}>
                                                    {e.price}
                                                </div>
                                                <div style={{ flex: 1, textAlign: 'center' }}>
                                                    {e.md_price}
                                                </div>
                                                <div style={{ flex: 1, textAlign: 'center' }}>
                                                    {e.zhe_kou}
                                                </div>
                                                <div style={{ flex: 1, textAlign: 'center' }}>
                                                    {e.final_price}
                                                </div>
                                                <div style={{ flex: 1, textAlign: 'center' }}>
                                                    {e.buy_num}
                                                </div>
                                                <div style={{ flex: 1, textAlign: 'center' }}>
                                                    {e.buy_num - e.send_num}
                                                </div>
                                                <div style={{ flex: 1, textAlign: 'center' }}>
                                                    <Input value={e.buy_num - e.send_num} style={{ maxWidth: 60 }} onChange={({ target }) => this._changeProduceNum(index, i, target.value)} />
                                                </div>
                                            </div>))
                                    }
                                </div>
                            ))
                        }
                    </Col>
                </Row>
                <FormItem wrapperCol={{ span: 8, offset: 3 }}>
                    <div style={{ maxWidth: 300 }}>
                        <Link to={'/customerProgrammeAdd/' + this.props.match.params.id}>
                            <Button icon='save' style={{ float: "left" }}>生成订单</Button>
                        </Link>
                        <Button onClick={() => { this.props.history.goBack() }} style={{ marginLeft: 10, float: "right" }} icon='rollback'>
                            返回列表
                        </Button>
                    </div>
                </FormItem>
            </Form >
        )
    }
}
export default Form.create()(customerCreateOrder)