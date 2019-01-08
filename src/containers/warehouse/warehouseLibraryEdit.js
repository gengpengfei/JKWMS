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
            data: []
        }
    }

    componentDidMount() {
        this._getWarehouseRowShelfDetail()
    }

    _getWarehouseRowShelfDetail = () => {
        const formData = {
            id: this.props.match.params.id
        }
        NetWork_Post('warehouseLibraryDetail', formData, (response) => {
            console.log('warehouseLibraryDetail', response)
            const { status, data, msg } = response
            if (status === '0000') {
                this.setState({
                    data: data
                }, () => this.props.form.setFieldsValue({
                    warehouse_num: data.warehouse_num,
                    warea_num: data.warea_num,
                    shelf_id: data.shelf_id,
                    wlibrary_num: data.wlibrary_num,
                    is_temporary: data.is_temporary,
                    is_th_storage: data.is_th_storage,
                    logistics_mode: data.logistics_mode,
                    unit: data.unit,
                    stock_num_down: data.stock_num_down,
                    stock_num_up: data.stock_num_up,
                    remark: data.remark,
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
            values.id = this.props.match.params.id
            NetWork_Post('warehouseLibraryEdit', values, (response) => {
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
                        <Select placeholder='请选择' disabled style={{ maxWidth: 300 }}>
                            <Option key={0} value={this.state.data.warehouse_num}>{this.state.data.warehouse_name}</Option>
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
                        <Select placeholder='请选择' disabled style={{ maxWidth: 300 }}>
                            <Option key={0} value={this.state.data.warea_num}>{this.state.data.warea_name}</Option>
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
                        <Select placeholder='请选择' disabled style={{ maxWidth: 300 }}>
                            <Option key={0} value={this.state.data.shelf_id}>{this.state.data.shelf_num + this.state.data.shelf_id}</Option>
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
                    label="是否临时库位:"
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
                    label="是否第三方:"
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
export default Form.create()(warehouseAreaEdit)