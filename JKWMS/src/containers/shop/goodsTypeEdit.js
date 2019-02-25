// -- 编辑商品类型
import React, { Component } from 'react';
import { Form, Select, Button, Input, message } from 'antd'
import { NetWork_Post } from '../../network/netUtils'
const Option = Select.Option;
const { TextArea } = Input;
const FormItem = Form.Item;
class goodsTypeEdit extends Component {
    constructor(props) {
        super(props)
        this.state = {
            goodTypeList: []
        }
    }
    componentDidMount() {
        this._getParentCodeList()
        this._getProTypeDetail()
    }
    _getProTypeDetail = () => {
        const formData = {
            pro_type_id: this.props.match.params.id
        }
        NetWork_Post('proTypeDetail', formData, (response) => {
            const { status, data, msg } = response
            console.log('proTypeDetail', data)
            if (status === '0000') {
                this.props.form.setFieldsValue({
                    parent_code: data.parent_code,
                    pro_type_code: data.pro_type_code,
                    pro_type_name: data.pro_type_name,
                    remark: data.remark,
                    user_name: data.user_name
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    _getParentCodeList = () => {
        const formData = {
        }
        NetWork_Post('getParentCode', formData, (response) => {
            const { status, data, msg } = response
            console.log('getParentCode', response)
            if (status === '0000') {
                this.setState({
                    goodTypeList: data.goodTypeList
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
            values.pro_type_id = this.props.match.params.id
            NetWork_Post('productTypeEdit', values, (response) => {
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
                    {...formItemLayout}
                    label="父类型编号:"
                >
                    {getFieldDecorator('parent_code', {
                        rules: [{
                            required: true, message: '请选择父类型！',
                        }],
                    })(
                        <Select placeholder='请选择' disabled style={{ maxWidth: 300 }}>
                            {
                                this.state.goodTypeList.map((e, i) => {
                                    return <Option key={i} value={e.pro_type_code}>{e.pro_type_name}</Option>
                                })
                            }
                        </Select>
                    )}
                </FormItem>
                <FormItem
                    label="产品类型编号:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('pro_type_code', {
                        rules: [{
                            required: true, message: '请输入产品类型编号！',
                        }],
                    })(
                        <Input disabled style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="产品类型名称:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('pro_type_name', {
                        rules: [{
                            required: true, message: '请输入产品类型名称！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="产品类型备注:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('remark', {
                        rules: [],
                    })(
                        <TextArea style={{ maxWidth: 300 }} rows={4} />
                    )}
                </FormItem>
                <FormItem
                    label="采购人员姓名:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('user_name', {
                        rules: [],
                    })(
                        <Input style={{ maxWidth: 300 }} />
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
const goodsTypeAdds = Form.create()(goodsTypeEdit)
export default goodsTypeAdds