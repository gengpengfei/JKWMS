// -- 添加仓库
import React, { Component } from 'react';
import { Form, Select, Button, Input, message } from 'antd'
import { NetWork_Post } from '../../network/netUtils'
const Option = Select.Option;
const { TextArea } = Input;
const FormItem = Form.Item;
class warehouseAreaAdd extends Component {
    constructor(props) {
        super(props)
        this.state = {
            warehouseList: [],
            warehouseAreaList: [],
            warehouseRowShelfList: []
        }
    }
    componentDidMount() {
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
    _getWarehouseAreaList = (value) => {
        if (!value) return;
        this.props.form.setFieldsValue({
            warea_num: undefined,
            shelf_num: undefined
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
                    warehouseAreaList: data.warehouseAreaList,
                    warehouseRowShelfList: []
                })
            } else {
                if (status === '1003') return this.props.history.push('/');
                message.error(msg)
            }
        });
    }
    _getWarehouseRowShelfList = (value) => {
        if (!value) return;
        this.props.form.setFieldsValue({
            shelf_num: undefined
        })
        const formData = {
            warea_num: value,
            limit: 10000,
            page: 1
        }
        NetWork_Post('warehouseRowShelfList', formData, (response) => {
            const { status, data, msg } = response
            console.log('warehouseRowShelfList', response)
            if (status === '0000') {
                this.setState({
                    warehouseRowShelfList: data.rowShelfList
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
            NetWork_Post('warehouseLibraryAdd', values, (response) => {
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
                        <Select placeholder='请选择' allowClear={true} style={{ maxWidth: 300 }} onChange={this._getWarehouseRowShelfList}>
                            {
                                this.state.warehouseAreaList.map((item, index) => (
                                    <Option key={index} value={item.warea_num}>{item.warea_name}</Option>
                                ))
                            }
                        </Select>
                    )}
                </FormItem>
                <FormItem
                    {...formItemLayout}
                    label="货架选择:"
                >
                    {getFieldDecorator('shelf_id', {
                        rules: [{
                            required: true, message: '请选择货架！',
                        }],
                    })(
                        <Select placeholder='请选择' allowClear={true} style={{ maxWidth: 300 }}>
                            {
                                this.state.warehouseRowShelfList.map((item, index) => (
                                    <Option key={index} value={item.id}>{item.shelf_num}</Option>
                                ))
                            }
                        </Select>
                    )}
                </FormItem>
                <FormItem
                    label="库位编号:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('wlibrary_num', {
                        rules: [{
                            required: true, message: '请输入库位编号！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    {...formItemLayout}
                    label="是否为临时库位:"
                >
                    {getFieldDecorator('is_temporary', {
                        rules: [{
                            required: true, message: '请选择是否为临时库位！',
                        }],
                    })(
                        <Select placeholder='请选择' style={{ maxWidth: 300 }}>
                            <Option key={1} value={1}>是</Option>
                            <Option key={0} value={0}>否</Option>
                        </Select>
                    )}
                </FormItem>
                <FormItem
                    {...formItemLayout}
                    label="是否第三方仓储:"
                >
                    {getFieldDecorator('is_th_storage', {
                        rules: [{
                            required: true, message: '请选择是否第三方仓储！',
                        }],
                    })(
                        <Select placeholder='请选择' style={{ maxWidth: 300 }}>
                            <Option key={1} value={1}>是</Option>
                            <Option key={0} value={0}>否</Option>
                        </Select>
                    )}
                </FormItem>
                <FormItem
                    {...formItemLayout}
                    label="物流方式:"
                >
                    {getFieldDecorator('logistics_mode', {
                        rules: [{
                            required: true, message: '请选择物流方式！',
                        }],
                    })(
                        <Select placeholder='请选择' style={{ maxWidth: 300 }}>
                            <Option key={0} value={0}>常温</Option>
                            <Option key={1} value={1}>冷藏</Option>
                            <Option key={2} value={2}>冷冻</Option>
                        </Select>
                    )}
                </FormItem>
                <FormItem
                    label="库存上限:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('stock_num_up', {
                        rules: [{
                            required: true, message: '请输入库存上限！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="库存下限:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('stock_num_down', {
                        rules: [{
                            required: true, message: '请输入库存下限！',
                        }],
                    })(
                        <Input style={{ maxWidth: 300 }} />
                    )}
                </FormItem>
                <FormItem
                    label="计量单位:"
                    {...formItemLayout}
                >
                    {getFieldDecorator('unit', {
                        rules: [{
                            required: true, message: '请输入计量单位！',
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
export default Form.create()(warehouseAreaAdd)