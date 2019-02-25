// -- 大客户订单方案审核详情
import React, { Component } from 'react';
import { Form, Button, Input, message, DatePicker, Radio, Row, Col } from 'antd'
import { Link } from 'react-router-dom'
import { NetWork_Post } from '../../network/netUtils'
import moment from 'moment';
const { TextArea } = Input;
const FormItem = Form.Item;
const RadioGroup = Radio.Group;
class CustomerProgrammeReviewedInfo extends Component {
    constructor(props) {
        super(props)
        this.state = {
            orderDetail: [],
            productList: []
        }
    }
    componentDidMount() {
        //--获取订单详情
        this._getCustomerProgrammeOrderDetail()
    }
    _getCustomerProgrammeOrderDetail = () => {
        const formData = {
            id: this.props.match.params.id
        }
        NetWork_Post('customerProgrammeOrderInfo', formData, (response) => {
            const { status, data, msg } = response
            console.log('customerProgrammeOrderInfo', response)
            if (status === '0000') {
                this.setState({
                    order_num: data.order_num,
                    productList: data.productList,
                    order_end_date: data.order_end_date,
                    orderDetail: data
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

    handleSubmit = (e) => {
        e.preventDefault();
        this.props.form.validateFields((err, values) => {
            if (err) return;
            if (values.flag === 2) {
                values.in_flag = 4
                values.select_fa = 0
            } else {
                switch (this.state.orderDetail.in_flag) {
                    case 2:
                        values.in_flag = 3;
                        break;
                    case 3:
                        values.in_flag = 5;
                        break;
                    case 5:
                        values.in_flag = 6;
                        break;
                    case 6:
                        values.in_flag = 7;
                        break;
                    case 7:
                        values.in_flag = 8;
                        break;
                    default:
                        break;
                }
            }
            values.id = parseInt(this.props.match.params.id)
            NetWork_Post('programmeReviewed', values, (response) => {
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
                <Row type='flex' align='center' style={{ width: '100%', marginBottom: 20 }}>
                    <Col xs={{ span: 12 }} sm={{ span: 3 }} style={{ textAlign: 'right' }}>方案列表： </Col>
                    <Col xs={{ span: 12 }} sm={{ span: 21 }} style={{ marginTop: -15 }}>
                        {
                            this.state.productList.map((item, index) => (
                                this.state.orderDetail.select_fa > 0 && this.state.orderDetail.select_fa !== item.select_fa ?
                                    null : <div style={{ maxHeight: 230, overflow: 'auto', width: '100%', marginTop: 20 }} key={index}>
                                        <div style={{ width: '100%', height: 40, display: 'flex', justifyContent: 'space-around', alignItems: 'center', border: '1px solid #efefef' }}>
                                            <span style={{ flex: 4, fontWeight: 500, marginLeft: 20 }}>方案{index + 1}（ 总利润:{item.allProfit},  销售总额:{item.allSale},  总数量:{item.allAmount} ）（单位：元）</span>
                                        </div>
                                        <div style={{ width: '100%', height: 40, display: 'flex', justifyContent: 'space-around', alignItems: 'center', backgroundColor: '#efefef' }}>
                                            <div style={{ flex: 1, textAlign: 'center' }}>商品编号</div>
                                            <div style={{ flex: 2, textAlign: 'center' }}>商品名称</div>
                                            <div style={{ flex: 1, textAlign: 'center' }}>规格</div>
                                            <div style={{ flex: 1, textAlign: 'center' }}>进价</div>
                                            <div style={{ flex: 1, textAlign: 'center' }}>门店价</div>
                                            <div style={{ flex: 1, textAlign: 'center' }}>建议销售价</div>
                                            <div style={{ flex: 1, textAlign: 'center' }}>数量</div>
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
                                                        {e.final_price}
                                                    </div>
                                                    <div style={{ flex: 1, textAlign: 'center' }}>
                                                        {e.buy_num}
                                                    </div>
                                                </div>))
                                        }
                                    </div>
                            ))
                        }
                    </Col>
                </Row>
                {
                    this.state.orderDetail.in_flag == 3 ? <FormItem
                        {...formItemLayout}
                        label="方案选择:"
                    >
                        {getFieldDecorator('select_fa', {
                            rules: [{
                                required: true, message: '请选择方案！',
                            }],
                        })(
                            <RadioGroup>
                                {
                                    this.state.productList.map((item, index) => (
                                        <Radio value={item.select_fa}>方案{index + 1}</Radio>
                                    ))
                                }
                            </RadioGroup>
                        )}
                    </FormItem> : null
                }
                <FormItem
                    {...formItemLayout}
                    label="审核状态:"
                >
                    {getFieldDecorator('flag', {
                        rules: [{
                            required: true, message: '请选择审核状态！',
                        }],
                    })(
                        <RadioGroup>
                            <Radio value={1}>审核通过</Radio>
                            <Radio value={2}>审核不通过</Radio>
                        </RadioGroup>
                    )}
                </FormItem>
                <FormItem
                    label="审核说明:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('sh_remark', {
                        rules: [],
                    })(
                        <TextArea style={{ maxWidth: 300 }} rows={5} />
                    )}
                </FormItem>
                <FormItem wrapperCol={{ span: 8, offset: 3 }}>
                    <div style={{ maxWidth: 300 }}>
                        <Button icon='save' htmlType="submit" style={{ float: "left" }}>审核提交</Button>
                        <Button onClick={() => { this.props.history.goBack() }} style={{ marginLeft: 10, float: "right" }} icon='rollback'>
                            返回列表
                        </Button>
                    </div>
                </FormItem>
            </Form >
        )
    }
}
export default Form.create()(CustomerProgrammeReviewedInfo)