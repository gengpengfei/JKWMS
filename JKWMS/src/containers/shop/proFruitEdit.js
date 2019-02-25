// -- 商品转换编辑
import React, { Component } from 'react';
import { Form, Button, Input, message } from 'antd'
import { NetWork_Post } from '../../network/netUtils'
const FormItem = Form.Item;
const { TextArea } = Input;
class proFruitEdit extends Component {
    constructor(props) {
        super(props)
        this.state = {
            proFruitDetail: []
        }
    }
    componentDidMount() {
        //-- 获取商品转换详情
        this._getProFruitDetail()
        this.props.form.setFieldsValue({
            product_num: this.props.match.params.product_num,
        })
    }
    _getProFruitDetail = () => {
        const formData = {
            id: this.props.match.params.id
        }
        NetWork_Post('proFruitDetail', formData, (response) => {
            const { status, data, msg } = response
            if (status === '0000') {
                this.setState({
                    proFruitDetail: data
                })
                this.props.form.setFieldsValue({
                    product_num: data.product_num,
                    product_name: data.product_name,
                    pro_code_mix: data.pro_code_mix,
                    pro_name_mix: data.pro_name_mix,
                    remark: data.remark,
                    times: data.times
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
            values.id = this.props.match.params.id
            NetWork_Post('proFruitEdit', values, (response) => {
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
    //-- 计算规格
    _checkTimes = () => {
        const { weight, number } = this.state;
        if (!weight || !number) {
            message.error('请输入正确的单果重量和数量')
        }
        const times = parseFloat(weight) * parseFloat(number)
        this.props.form.setFieldsValue({
            times: times
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
                    label="单一商品编码:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('product_num', {
                        rules: [{
                            required: true, message: '请输入商品编号！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="单一商品名称:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('product_name', {
                        rules: [{
                            required: true, message: '请输入商品名称！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="组合商品编码:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('pro_code_mix', {
                        rules: [{
                            required: true, message: '请输入组合商品编号！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="组合商品名称:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('pro_name_mix', {
                        rules: [{
                            required: true, message: '请输入组合商品名称！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="转换后的规格:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('times', {
                        rules: [{
                            required: true, message: '输入单位和规格后点击计算！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem wrapperCol={{ span: 8, offset: 3 }}>
                    单果：<Input type='number' style={{ maxWidth: 70 }} onChange={({ target }) => this.setState({ weight: target.value })} /> /kg  <Input style={{ maxWidth: 70 }} type='number' onChange={({ target }) => this.setState({ number: target.value })} /> 个 <Button onClick={this._checkTimes}>计算</Button>
                </FormItem>
                <FormItem
                    label="备注:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('remark', {
                        rules: [],
                    })(
                        <TextArea style={{ maxWidth: 300 }} rows={4} />
                    )}
                </FormItem>
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
export default Form.create()(proFruitEdit)