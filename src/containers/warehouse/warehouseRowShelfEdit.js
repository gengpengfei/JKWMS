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
            warehouseList: [],
            warehouseAreaList: []
        }
    }

    componentDidMount() {
        this._getWarehouseRowShelfDetail()
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
    _getWarehouseRowShelfDetail = () => {
        const formData = {
            id: this.props.match.params.id
        }
        NetWork_Post('warehouseRowShelfDetail', formData, (response) => {
            console.log('warehouseRowShelfDetail', response)
            const { status, data, msg } = response
            if (status === '0000') {
                this._getWarehouseAreaList(data.warehouse_num)
                this.props.form.setFieldsValue({
                    warehouse_num: data.warehouse_num,
                    warea_num: data.warea_num,
                    shelf_num: data.shelf_num,
                    remark: data.remark,
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    _getWarehouseAreaList = (value) => {
        if (!value) return;
        this.props.form.setFieldsValue({
            warea_num: undefined
        })
        const formData = {
            warehouse_num: value,
            limit: 10000,
            page: 1
        }
        NetWork_Post('warehouseAreaList', formData, (response) => {
            const { status, data, msg } = response
            console.log('warehouseAreaList', response)
            if (status === '0000') {
                this.setState({
                    warehouseAreaList: data.warehouseAreaList
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
            NetWork_Post('warehouseRowShelfEdit', values, (response) => {
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
                        <Select placeholder='请选择' allowClear={true} style={{ maxWidth: 300 }} onChange={this._getWarehouseAreaList}>
                            {
                                this.state.warehouseList.map((item, index) => (
                                    <Option key={index} value={item.warehouse_num}>{item.warehouse_name}</Option>
                                ))
                            }
                        </Select>
                    )}
                </FormItem>
                <FormItem
                    {...formItemLayout}
                    label="库区选择:"
                >
                    {getFieldDecorator('warea_num', {
                        rules: [{
                            required: true, message: '请选择库区！',
                        }],
                    })(
                        <Select placeholder='请选择' allowClear={true} style={{ maxWidth: 300 }}>
                            {
                                this.state.warehouseAreaList.map((item, index) => (
                                    <Option key={index} value={item.warea_num}>{item.warea_name}</Option>
                                ))
                            }
                        </Select>
                    )}
                </FormItem>
                <FormItem
                    label="货架编号:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('shelf_num', {
                        rules: [{
                            required: true, message: '请输入货架编号！',
                        }],
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
export default Form.create()(warehouseAreaEdit)