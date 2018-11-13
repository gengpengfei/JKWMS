// -- 不推送商品添加
import React, { Component } from 'react';
import { Form, Button, Input, message } from 'antd'
import { NetWork_Post } from '../../network/netUtils'
const FormItem = Form.Item;
class proExcludeAdd extends Component {
    handleSubmit = (e) => {
        e.preventDefault();
        this.props.form.validateFields((err, values) => {
            if (err) return;
            NetWork_Post('proExcludeAdd', values, (response) => {
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
                    label="不推送商品编码:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('pro_code', {
                        rules: [{
                            required: true, message: '请输入不推送商品编号！',
                        }],
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
export default Form.create()(proExcludeAdd)