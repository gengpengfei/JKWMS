// -- 编辑厂商
import React, { Component } from 'react';
import { Form, Select, Button, Input, message } from 'antd'
import { NetWork_Post } from '../../network/netUtils'
const Option = Select.Option;
const { TextArea } = Input;
const FormItem = Form.Item;
class warehouseAreaEdit extends Component {
    constructor(props) {
        super(props)
        this.state = {
            warehouseList: []
        }
    }
    componentDidMount() {
        this._getWarehouseAreaDetail()
        //-- 获取仓库列表
        this._getWarehouseList()
    }
    _getWarehouseList = () => {
        const formData = {
            limit: 10000,
            page: 1
        }
        NetWork_Post('warehouseList', formData, (response) => {
            const { status, data, msg } = response
            console.log('warehouseList', response)
            if (status === '0000') {
                this.setState({
                    warehouseList: data.warehouseList
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    _getWarehouseAreaDetail = () => {
        const formData = {
            id: this.props.match.params.id
        }
        NetWork_Post('warehouseAreaDetail', formData, (response) => {
            console.log('warehouseAreaDetail', response)
            const { status, data, msg } = response
            if (status === '0000') {
                this.props.form.setFieldsValue({
                    warehouse_num: data.warehouse_num,
                    warea_name: data.warea_name,
                    warea_num: data.warea_num,
                    warea_type: data.warea_type === 0 ? '收货区' : data.warea_type === 1 ? '存储区' : data.warea_type === 2 ? '次品区' : data.warea_type === 3 ? '拣货区' : null,
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
            NetWork_Post('warehouseAreaEdit', values, (response) => {
                const { status, msg } = response
                if (status === '0000') {
                    message.success(msg)
                    return this.props.history.goBack();
                } else {
                    if (status === '1003') return this.props.history.push('/');
                    message.error(msg)
                }
            })
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
                    {...formItemLayout}
                    label="仓库选择:"
                >
                    {getFieldDecorator('warehouse_num', {
                        rules: [{
                            required: true, message: '请选择仓库！',
                        }],
                    })(
                        <Select placeholder='请选择' style={{ maxWidth: 300 }}>
                            {
                                this.state.warehouseList.map((item, index) => (
                                    <Option key={index} value={item.warehouse_num}>{item.warehouse_name}</Option>
                                ))
                            }
                        </Select>
                    )}
                </FormItem>
                <FormItem
                    label="库区编号:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('warea_num', {
                        rules: [{
                            required: true, message: '请输入库区编号！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="库区名称:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('warea_name', {
                        rules: [{
                            required: true, message: '请输入库区名称！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    {...formItemLayout}
                    label="库区类型:"
                >
                    {getFieldDecorator('warea_type', {
                        rules: [{
                            required: true, message: '请选择库区类型！',
                        }],
                    })(
                        <Select placeholder='请选择' style={{ maxWidth: 300 }}>
                            <Option key={1} value={0}>收货区</Option>
                            <Option key={2} value={1}>存储区</Option>
                            <Option key={3} value={2}>次品区</Option>
                            <Option key={4} value={3}>拣货区</Option>
                        </Select>
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
export default Form.create()(warehouseAreaEdit)