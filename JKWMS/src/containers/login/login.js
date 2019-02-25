import React, { Component } from 'react';
import { Form, Icon, Input, Button, Checkbox, message } from 'antd';
import { NetWork_Post } from '../../network/netUtils'
const FormItem = Form.Item;
class Login extends Component {
    handleSubmit = (e) => {
        e.preventDefault();
        new Promise((resolve, reject) => {
            this.props.form.validateFields((err, values) => {
                if (err) return;
                NetWork_Post('login', values, (response) => {
                    const { status, msg, data } = response
                    console.log('userInfo', response)
                    if (status === '0000') {
                        //-- 存储登录信息
                        localStorage.setItem('token', 'Mr.Geng')
                        localStorage.setItem('userInfo', JSON.stringify(data))
                        resolve(data.admin_id)
                    } else {
                        message.error(msg)
                    }
                });
            });
        }).then(this._getMenuList)
    }
    _getMenuList = (adminId) => {
        const formData = {
            admin_id: adminId
        }
        console.log(formData)
        NetWork_Post('getMenuList', formData, (response) => {
            console.log('getMenuList', response)
            const { status, data, msg } = response
            if (status === '0000') {
                //-- 存储权限信息
                localStorage.setItem('ActionList', JSON.stringify(data))
                this.props.history.push('/home')
            } else {
                message.error(msg)
            }
        });
    }
    render() {
        const { getFieldDecorator } = this.props.form;
        return (
            <div style={{ flex: 1, display: 'flex', position: 'fixed', top: 0, bottom: 0, width: '100%', flexDirection: 'column', justifyContent: 'center', alignItems: 'center', backgroundImage: "url(" + require("./src/loginbg.png") + ")", backgroundSize: '100% 100%' }}>
                <div style={{ fontSize: 32, height: 80 }}>物流管理系统</div>
                <Form onSubmit={this.handleSubmit} style={{ width: 280 }}>
                    <FormItem>
                        {getFieldDecorator('user_name', {
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