import React, { Component } from 'react';
import { Form, Icon, Input, Button, Checkbox } from 'antd';
import { NetWork_Post } from '../../network/netUtils'
const FormItem = Form.Item;
class Login extends Component {
    handleSubmit = (e) => {
        e.preventDefault();
        this.props.form.validateFields((err, values) => {
            if (err) return;
            NetWork_Post('login', values, (response) => {
                //-- 存储登录信息
                localStorage.setItem('token', 'Mr.Geng')
                window.location.href = '/home'
            });
        });
    }
    render() {
        const { getFieldDecorator } = this.props.form;
        return (
            <div style={{ flex: 1, display: 'flex', position: 'fixed', top: 0, bottom: 0, width: '100%', flexDirection: 'column', justifyContent: 'center', alignItems: 'center', backgroundImage: "url(" + require("./src/loginbg.png") + ")", backgroundSize: '100% 100%' }}>
                <div style={{ fontSize: 32, height: 80 }}>物流管理系统</div>
                <Form onSubmit={this.handleSubmit} style={{ width: 280 }}>
                    <FormItem>
                        {getFieldDecorator('userName', {
                            rules: [{ required: true, message: '请输入用户名!' }],
                        })(
                            <Input prefix={<Icon type="user" style={{ color: 'rgba(0,0,0,.25)' }} />} placeholder="用户名" />
                        )}
                    </FormItem>
                    <FormItem>
                        {getFieldDecorator('password', {
                            rules: [{ required: true, message: '请输入密码!' }],
                        })(
                            <Input prefix={<Icon type="lock" style={{ color: 'rgba(0,0,0,.25)' }} />} type="password" placeholder="密码" />
                        )}
                    </FormItem>
                    <FormItem>
                        {getFieldDecorator('remember', {
                            valuePropName: 'checked',
                            initialValue: true,
                        })(
                            <Checkbox>记住我</Checkbox>
                        )}

                        <Button type="primary" htmlType="submit" style={{ width: '100%' }}>
                            登录
                        </Button>
                    </FormItem>
                </Form>
            </div>
        )
    }
}
const Logins = Form.create()(Login);
export default Logins;