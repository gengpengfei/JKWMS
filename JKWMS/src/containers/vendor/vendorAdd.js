// -- 添加厂商
import React, { Component } from 'react';
import { Form, Select, Button, Input, message } from 'antd'
import { NetWork_Post } from '../../network/netUtils'
const Option = Select.Option;
const { TextArea } = Input;
const FormItem = Form.Item;
class vendorAdd extends Component {
    handleSubmit = (e) => {
        e.preventDefault();
        this.props.form.validateFields((err, values) => {
            if (err) return;
            NetWork_Post('vendorAdd', values, (response) => {
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
                    label="厂商编号:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('vendor_num', {
                        rules: [{
                            required: true, message: '请输入厂商编号！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="厂商名称:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('vendor_name', {
                        rules: [{
                            required: true, message: '请输入厂商名称！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="联系人姓名:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('contact_name', {
                        rules: [{
                            required: true, message: '请输入联系人姓名！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="联系人电话:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('contact_tel', {
                        rules: [{
                            required: true, message: '请输入联系人电话！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="联系人手机:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('contact_mobile', {
                        rules: [{
                            required: true, message: '请输入联系人手机！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="联系人传真:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('contact_fax', {
                        rules: [],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="联系人qq:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('contact_qq', {
                        rules: [],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="联系人微信:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('contact_wechat', {
                        rules: [],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="联系人邮箱:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('contact_email', {
                        rules: [],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    {...formItemLayout}
                    label="厂商标记:"
                >
                    {getFieldDecorator('tag', {
                        rules: [{
                            required: true, message: '请选择厂商标记！',
                        }],
                    })(
                        <Select placeholder='请选择' style={{ maxWidth: 300 }}>
                            <Option key={0} value={0}>厂商</Option>
                            <Option key={1} value={1}>供应商</Option>
                        </Select>
                    )}
                </FormItem>
                <FormItem
                    label="结算方式:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('settlement', {
                        rules: [],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="地址:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('address', {
                        rules: [],
                    })(
                        <TextArea style={{ maxWidth: 300 }} rows={4} />
                    )}
                </FormItem>
                <FormItem
                    label="厂商备注:"
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
export default Form.create()(vendorAdd)