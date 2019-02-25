import React, { PureComponent } from 'react';
import { Table, Spin, Button, Icon } from 'antd'

export default class Home extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            loading: true,
            back: true
        };
    }
    stateChangeFirefox = _frame => {
        this.setState({ loading: false, back: false });
    };
    render() {
        const data = [{
            'key': 0,
            'name': "团体健康",
            'name2': "上海",
            'name3': '2019-02-11',
            'street': "Lake Park",
            'building': "C",
            'number': 2035,
            'companyAddress': "Lake Street 42",
            'companyName': "SoftLake Co",
            'gender': "M"
        }, {
            'key': 1,
            'name': "团体健康a",
            'name2': "上海",
            'name3': '2019-02-12',
            'street': "Lake Park",
            'building': "C",
            'number': 2035,
            'companyAddress': "Lake Street 42",
            'companyName': "SoftLake Co",
            'gender': "M"
        }, {
            key: 2,
            name: "团体健康a",
            name2: '上海',
            name3: '2019-02-12',
            street: "Lake Park",
            building: "C",
            number: 2035,
            companyAddress: "Lake Street 42",
            companyName: "SoftLake Co",
            gender: "M"
        }, {
            key: 3,
            name: "团体健康2",
            name2: '合肥',
            name3: '2019-02-12',
            street: "Lake Park",
            building: "C",
            number: 2035,
            companyAddress: "Lake Street 42",
            companyName: "SoftLake Co",
            gender: "M"
        }, {
            key: 4,
            name: "团体健康2",
            name2: '上海',
            name3: '2019-02-12',
            street: "Lake Park",
            building: "C",
            number: 2035,
            companyAddress: "Lake Street 42",
            companyName: "SoftLake Co",
            gender: "M"
        }, {
            key: 5,
            name: "团体健康",
            name2: '合肥',
            name3: '合计',
            street: "Lake Park",
            building: "C",
            number: 2035,
            companyAddress: "Lake Street 42",
            companyName: "SoftLake Co",
            gender: "M"
        }, {
            'key': 6,
            'name': "团体健康",
            'name2': "上海",
            'name3': '2019-02-11',
            'street': "Lake Park",
            'building': "C",
            'number': 2035,
            'companyAddress': "Lake Street 42",
            'companyName': "SoftLake Co",
            'gender': "M"
        }, {
            'key': 7,
            'name': "团体健康",
            'name2': "上海",
            'name3': '2019-02-11',
            'street': "Lake Park",
            'building': "C",
            'number': 2035,
            'companyAddress': "Lake Street 42",
            'companyName': "SoftLake Co",
            'gender': "M"
        }];
        const { loading, back } = this.state;
        const renderContent = (value, row, index) => {
            const obj = {
                children: value,
                props: {}
            };
            if (index === 4) {
                obj.props.colSpan = 0;
            }
            return obj;
        };
        const columns = [
            {
                title: "队列",
                dataIndex: "name",
                key: "name",
                width: 50,
                render: (value, row, index) => {
                    var obj = {
                        children: value,
                        props: {}
                    }
                    var lg = 0;
                    if (data[index - 1] && value === data[index - 1].name) {
                        //-- 下面有重复的就合并
                        obj.props.rowSpan = 0
                    } else {
                        //-- 开始合并位置-计算合并数量
                        for (var i = index; i < data.length; i++) {
                            if (value === data[i].name) {
                                lg = lg + 1
                            } else {
                                break;
                            }
                        }
                    }
                    obj.props.rowSpan = lg;
                    return obj;
                }
            },
            {
                title: "团队",
                dataIndex: "name2",
                key: "name2",
                width: 50,
                render: (value, row, index) => {
                    var obj = {
                        children: value,
                        props: {}
                    }
                    var lg = 0;
                    //-- 这里判断前面所有层数的字段都一样才不显示
                    if (data[index - 1] && value === data[index - 1].name2 && data[index].name === data[index - 1].name) {
                        //-- 下面有重复的就合并
                        obj.props.rowSpan = 0
                    } else {
                        //-- 开始合并位置-计算合并数量
                        for (var i = index; i < data.length; i++) {
                            //-- 这里判断前面所有层数的字段都一样才合并
                            if (value === data[i].name2 && data[index].name === data[i].name) {
                                lg = lg + 1
                            } else {
                                break;
                            }
                        }
                    }
                    obj.props.rowSpan = lg;
                    return obj;
                }
            },
            {
                title: "服务周期",
                dataIndex: "name3",
                key: "name3",
                width: 50,
                render: (value, row, index) => {
                    var obj = {
                        children: value,
                        props: {}
                    }
                    var lg = 0;
                    //-- 这里判断前面所有层数的字段都一样才不显示
                    if (data[index - 1] && value === data[index - 1].name3 && data[index].name === data[index - 1].name && data[index].name2 === data[index - 1].name2) {
                        //-- 下面有重复的就合并
                        obj.props.rowSpan = 0
                    } else {
                        //-- 开始合并位置-计算合并数量
                        for (var i = index; i < data.length; i++) {
                            //-- 这里判断前面所有层数的字段都一样才合并
                            if (value === data[i].name3 && data[index].name === data[i].name && data[index].name2 === data[i].name2) {
                                lg = lg + 1
                            } else {
                                break;
                            }
                        }
                    }
                    obj.props.rowSpan = lg;
                    return obj;
                }
            },
            {
                title: "坐席",
                dataIndex: "name31",
                key: "name31",
                width: 50
            },
            {
                title: "上线时间",
                dataIndex: "name4",
                key: "name4",
                width: 50
            },
            {
                title: "质检量",
                dataIndex: "name5",
                key: "name5",
                width: 50
            },
            {
                title: "质检平均分",
                dataIndex: "name6",
                key: "name6",
                width: 50
            },
            {
                title: "数据类型",
                dataIndex: "name7",
                key: "name7",
                width: 50,
                render: (value, row, index) => { }
            },
            {
                title: "服务流程",
                children: [
                    {
                        title: "开场白",
                        dataIndex: "age",
                        key: "age",
                        width: 100,
                        sorter: (a, b) => a.age - b.age
                    },
                    {
                        title: "结束语",
                        dataIndex: "age1",
                        key: "age2",
                        width: 100,
                        sorter: (a, b) => a.age - b.age
                    }
                ]
            },
            {
                title: "服务意识",
                children: [
                    {
                        title: "开场白",
                        dataIndex: "companyAddress",
                        key: "companyAddress",
                        width: 100,
                        sorter: (a, b) => a.age - b.age
                    },
                    {
                        title: "结束语",
                        dataIndex: "companyName",
                        key: "companyName",
                        width: 100,
                        sorter: (a, b) => a.age - b.age
                    }
                ]
            }
        ];
        return (
            <div style={{ border: '1px solid red', height: '100%', width: '100%' }}>
                <div style={{ width: '100%', display: 'flex', justifyContent: 'space-around', alignItems: 'center', backgroundColor: '#efefef' }}>
                    <Button>提价</Button>
                    <Icon type='search' />
                    <div>商品编号</div>
                </div>
            </div>
        );
    }
}