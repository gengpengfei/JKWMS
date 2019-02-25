// -- 编辑厂商
import React, { Component } from 'react';
import { Form, Select, Button, Input, message } from 'antd'
import { NetWork_Post } from '../../network/netUtils'
const Option = Select.Option;
const { TextArea } = Input;
const FormItem = Form.Item;
class vendorEdit extends Component {
    componentDidMount() {
        this._getVendorDetail()
    }
    _getVendorDetail = () => {
        const formData = {
            id: this.props.match.params.id
        }
        NetWork_Post('vendorDetail', formData, (response) => {
            const { status, data, msg } = response
            console.log('vendorDetail', response)
            if (status === '0000') {
                this.props.form.setFieldsValue({
                    vendor_num: data.vendor_num,
                    vendor_name: data.vendor_name,
                    contact_name: data.contact_name,
                    contact_tel: data.contact_tel,
                    contact_mobile: data.contact_mobile,
                    contact_fax: data.contact_fax,
                    contact_qq: data.contact_qq,
                    contact_wechat: data.contact_wechat,
                    contact_email: data.contact_email,
                    tag: data.tag,
                    address: data.address,
                    settlement: data.settlement,
                    remark: data.remark,
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
            NetWork_Post('vendorEdit', values, (response) => {
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
export default Form.create()(vendorEdit)