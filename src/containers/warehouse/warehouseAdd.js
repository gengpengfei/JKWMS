// -- 添加仓库
import React, { Component } from 'react';
import { Form, Select, Button, Input, message } from 'antd'
import { NetWork_Post } from '../../network/netUtils'
const Option = Select.Option;
const { TextArea } = Input;
const FormItem = Form.Item;
class warehouseAdd extends Component {
    constructor(props) {
        super(props)
        this.state = {
            provinceList: [],
            cityList: [],
            areaList: []
        }
    }
    componentDidMount() {
        //-- 获取顶级地址
        this._getProvinceList()
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
            NetWork_Post('warehouseAdd', values, (response) => {
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
                    label="仓库编号:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('warehouse_num', {
                        rules: [{
                            required: true, message: '请输入仓库编号！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="仓库名称:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('warehouse_name', {
                        rules: [{
                            required: true, message: '请输入仓库名称！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="负责人姓名:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('pic_name', {
                        rules: [{
                            required: true, message: '请输入负责人姓名！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="负责人电话:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('pic_tel', {
                        rules: [],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="负责人手机:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('pic_mobile', {
                        rules: [{
                            required: true, message: '请输入负责人手机！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="负责人传真:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('pic_fax', {
                        rules: [],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="负责人qq:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('pic_qq', {
                        rules: [],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="负责人微信:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('pic_wechat', {
                        rules: [],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="负责人邮箱:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('pic_email', {
                        rules: [],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    {...formItemLayout}
                    label="仓库所在省:"
                >
                    {getFieldDecorator('province', {
                        rules: [{
                            required: true, message: '请选择仓库所在省！',
                        }],
                    })(
                        <Select placeholder='请选择' style={{ maxWidth: 300 }} onChange={this._getCityList} >
                            {
                                this.state.provinceList ? this.state.provinceList.map((item, index) => (
                                    <Option key={index} value={item.area_name}>{item.area_name}</Option>
                                )) : null
                            }
                        </Select>
                    )}
                </FormItem>
                <FormItem
                    {...formItemLayout}
                    label="仓库所在市:"
                >
                    {getFieldDecorator('city', {
                        rules: [{
                            required: true, message: '请选择仓库所在市！',
                        }],
                    })(
                        <Select placeholder='请选择' style={{ maxWidth: 300 }} onChange={this._getAreaList}>
                            {
                                this.state.cityList ? this.state.cityList.map((item, index) => (
                                    <Option key={index} value={item.area_name} >{item.area_name}</Option>
                                )) : null
                            }
                        </Select>
                    )}
                </FormItem>
                <FormItem
                    {...formItemLayout}
                    label="仓库所在区:"
                >
                    {getFieldDecorator('area', {
                        rules: [{
                            required: true, message: '请选择仓库所在区！',
                        }],
                    })(
                        <Select placeholder='请选择' style={{ maxWidth: 300 }}>
                            {
                                this.state.areaList ? this.state.areaList.map((item, index) => (
                                    <Option key={index} value={item.area_name}>{item.area_name}</Option>
                                )) : null
                            }
                        </Select>
                    )}
                </FormItem>
                <FormItem
                    label="具体地址:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('address', {
                        rules: [{
                            required: true, message: '请输入具体地址！',
                        }],
                    })(
                        <TextArea style={{ maxWidth: 300 }} rows={3} />
                    )}
                </FormItem>
                <FormItem
                    label="接收日期:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('receiving_date', {
                        rules: [],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="备注:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('remark', {
                        rules: [],
                    })(
                        <TextArea style={{ maxWidth: 300 }} rows={3} />
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
export default Form.create()(warehouseAdd)