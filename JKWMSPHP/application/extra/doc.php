<?php
return [
    'title' => "APi接口文档",  //文档title
    'version'=>'1.0.0', //文档版本
    'copyright'=>'Powered By gyl', //版权信息
    'password' => 'shike2018', //访问密码，为空不需要密码
    //静态资源路径--默认为云上路径，解决很多人nginx配置问题
    //可将assets目录拷贝到public下面，具体路径课自行配置
    'static_path' => '/static/assets',
    'controller' => [
        //需要生成文档的类
        'app\api\controller\Index',
        'app\api\controller\System',
        'app\api\controller\AdminUser',
        'app\api\controller\Product',
        'app\api\controller\ProductType',
        'app\api\controller\ProExclude',
        'app\api\controller\ProFruit',
        'app\api\controller\ProOffline',
        'app\api\controller\Vendor',
        'app\api\controller\Warehouse',
        'app\api\controller\WarehouseArea',
        'app\api\controller\RowShelf',
        'app\api\controller\WLibrary',
        'app\api\controller\BigCustomer',
        'app\api\controller\Purchase',
        'app\api\controller\Inventory',
        'app\api\controller\Receive',
        'app\api\controller\ConfirmWait',
        'app\api\controller\QualityCheck',
        'app\api\controller\MoveLibrary',
        'app\api\controller\WarehousingIn',
    ],
    'filter_method' => [
        //过滤 不解析的方法名称
        '_empty'
    ],
    'return_format' => [
        //数据格式
        //0000	请求成功; -1001	您暂时没有此权限; -1002	缺少参数; -1003	token不存在或已过期; -1004 操作失败; -1005 查询失败
        'status' => "0000/-1001/-1002/-1003/-1004/-1005",
        'msg' => "提示信息",
    ],
    'public_header' => [
        //全局公共头部参数
        //如：['name'=>'version', 'require'=>1, 'default'=>'', 'desc'=>'版本号(全局)']
    ],
    'public_param' => [
        //全局公共请求参数，设置了所以的接口会自动增加次参数
        //如：['name'=>'token', 'type'=>'string', 'require'=>1, 'default'=>'', 'other'=>'' ,'desc'=>'验证（全局）')']
    ],
];
