// let CryptoJS = require("crypto-js");
export const BASEURL = '/';
var reqUrl = {
    //-- 通用模块
    areaList: BASEURL + 'api/WmsArea/areaList',//-- 下级地址列表

    //-- 登录模块
    login: BASEURL + 'api/AdminUser/adminLogin',  //-- 用户登录
    getMenuList: BASEURL + 'api/AdminUser/getUserActionList/',  //-- 用户权限列表

    //-- 商品模块
    getGoodsList: BASEURL + 'api/Product/productList',  //-- 商品列表
    productExport: BASEURL + 'api/Product/productExport',//-- 商品导出  
    productDownload: BASEURL + 'api/Product/productDownload',//-- 商品导入模版下载
    productImport: BASEURL + 'api/Product/productImport',//-- 商品信息导入
    productVenDownload: BASEURL + 'api/Product/venProDownload',//-- 商品供应商关系模版下载
    productVenImport: BASEURL + 'api/Product/venProImport',//-- 商品供应商关系导入
    productDetail: BASEURL + 'api/Product/productDetail',  //-- 商品详情
    productVendorList: BASEURL + 'api/Product/vendor',//-- 供应商列表
    productBindVendor: BASEURL + 'api/Product/vendorGood',//-- 绑定供应商

    //-- 商品类型模块
    getGoodsTypeList: BASEURL + 'api/ProductType/index',  //-- 商品分类列表
    getParentCode: BASEURL + 'api/ProductType/parentCode', //-- 父类型列表
    productTypeAdd: BASEURL + 'api/ProductType/productTypeAdd', //-- 添加商品分类列表
    productTypeDel: BASEURL + 'api/ProductType/proTypeDel', //-- 删除商品分类
    proTypeDetail: BASEURL + 'api/ProductType/proTypeDetail',//-- 获取商品分类详情
    productTypeEdit: BASEURL + 'api/ProductType/productTypeEdit',//-- 编辑商品分类详情
    proTypeDownload: BASEURL + 'api/ProductType/proTypeDownload',//-- 商品分类导入模版下载
    proTypeImport: BASEURL + 'api/ProductType/proTypeImport', //-- 文件上传

    //-- 不推送库存管理模块
    proExcludeList: BASEURL + 'api/ProExclude/index', //-- 不推送库存商品列表
    proExcludeDel: BASEURL + 'api/ProExclude/proExcludeDel', //-- 不推送库存商品删除
    proExcludeAdd: BASEURL + 'api/ProExclude/proExcludeAdd', //-- 不推送库存商品添加
    proExcludeEdit: BASEURL + 'api/ProExclude/proExcludeEdit', //-- 不推送库存商品编辑

    //-- 商品转换管理模块
    proFruitList: BASEURL + 'api/ProFruit/index', //-- 商品转换管理
    proFruitDetail: BASEURL + 'api/ProFruit/proFruitDetail', //-- 商品转换详情
    proFruitDel: BASEURL + 'api/ProFruit/proFruitDel', //-- 商品转换管理删除
    proFruitAdd: BASEURL + 'api/ProFruit/proFruitAdd', //-- 商品转换管理添加
    proFruitEdit: BASEURL + 'api/ProFruit/proFruitEdit', //-- 商品转换管理编辑

    //-- 商品下架申请模块
    proOfflineList: BASEURL + 'api/ProOffline/index', //-- 商品下架申请列表
    proOfflineDel: BASEURL + 'api/ProOffline/delete', //-- 商品下架申请删除
    proApplyUnder: BASEURL + 'api/ProOffline/applyUnder', //-- 商品申请下架
    proOfflineReviewed: BASEURL + 'api/ProOffline/reviewed', //-- 商下架申请通过
    proOfflineRefuse: BASEURL + 'api/ProOffline/refuse', //-- 商下架申请拒绝

    //-- 厂商管理模块
    vendorList: BASEURL + 'api/Vendor/index',  //-- 厂商列表
    vendorAdd: BASEURL + 'api/Vendor/vendorAdd',//-- 厂商添加  
    vendorEdit: BASEURL + 'api/Vendor/vendorEdit',//-- 厂商编辑 
    vendorDel: BASEURL + 'api/Vendor/vendorDel',//-- 厂商删除  
    vendorDetail: BASEURL + 'api/Vendor/vendorDetail',  //-- 厂商详情
    vendorProductList: BASEURL + 'api/Vendor/Product',  //-- 商品列表
    vendorBindedProduct: BASEURL + 'api/Vendor/venProduct',  //-- 厂商绑定过商品列表
    vendorBindProduct: BASEURL + 'api/Vendor/vendorGood',//-- 厂商绑定商品
    vendorDownload: BASEURL + 'api/Vendor/vendorDownload',//-- 厂商信息导入模版下载
    vendorImport: BASEURL + 'api/Vendor/vendorImport',//-- 厂商信息导入
    vendorBindProductDownload: BASEURL + 'api/Vendor/venProDownload',//-- 厂商绑定商品模版下载
    VendorBindProductImport: BASEURL + 'api/Vendor/venProImport',//-- 厂商绑定商品导入

    //-- 仓库管理模块
    warehouseList: BASEURL + 'api/Warehouse/index',//-- 仓库列表
    warehouseDetail: BASEURL + 'api/Warehouse/warehouseDetail',//-- 仓库详情
    warehouseAdd: BASEURL + 'api/Warehouse/warehouseAdd',//-- 仓库添加
    warehouseEdit: BASEURL + 'api/Warehouse/warehouseEdit',//-- 仓库编辑
    warehouseDel: BASEURL + 'api/Warehouse/warehouseDel',//-- 仓库删除
    warehouseAreaList: BASEURL + 'api/WarehouseArea/index',//-- 库区列表
    warehouseAreaDetail: BASEURL + 'api/WarehouseArea/warehouseAreaDetail',//-- 库区详情
    warehouseAreaAdd: BASEURL + 'api/WarehouseArea/warehouseAreaAdd',//-- 库区添加
    warehouseAreaEdit: BASEURL + 'api/WarehouseArea/warehouseAreaEdit',//-- 库区编辑
    warehouseAreaDel: BASEURL + 'api/WarehouseArea/warehouseAreaDel',//-- 库区删除
    warehouseRowShelfList: BASEURL + 'api/RowShelf/index',//-- 货架列表
    warehouseRowShelfDetail: BASEURL + 'api/RowShelf/rowShelfDetail',//-- 货架详情
    warehouseRowShelfAdd: BASEURL + 'api/RowShelf/rowShelfAdd',//-- 货架添加
    warehouseRowShelfEdit: BASEURL + 'api/RowShelf/rowShelfEdit',//-- 货架编辑
    warehouseRowShelfDel: BASEURL + 'api/RowShelf/rowShelfDel',//-- 货架删除
    rowShelfDownload: BASEURL + 'api/RowShelf/rowShelfDownload',//-- 库区导入模版下载
    rowShelfImport: BASEURL + 'api/RowShelf/rowShelfImport',//-- 库区导入
    warehouseLibraryList: BASEURL + 'api/WLibrary/index',//-- 库位列表
    warehouseLibraryDetail: BASEURL + 'api/WLibrary/wLibraryDetail',//-- 库位详情
    warehouseLibraryAdd: BASEURL + 'api/WLibrary/wLibraryAdd',//-- 库位添加
    warehouseLibraryEdit: BASEURL + 'api/WLibrary/wLibraryEdit',//-- 库位编辑
    warehouseLibraryDel: BASEURL + 'api/WLibrary/wLibraryDel',//-- 库位删除
    libraryDownload: BASEURL + 'api/WLibrary/wLibraryDownload',//-- 库位导入模版下载
    libraryImport: BASEURL + 'api/WLibrary/wLibraryImport',//-- 库位导入

    //-- 大客户专项模块
    customerDemandOrderList: BASEURL + 'api/BigCustomer/demandOrderList',//-- 大客户需求订单列表
    customerDemandOrderAdd: BASEURL + 'api/BigCustomer/demandOrderSave',//-- 大客户需求订单添加(编辑)
    customerDemandOrderDetail: BASEURL + 'api/BigCustomer/demandOrderDetail',//-- 大客户需求订单详情
    submitReviewedOrder: BASEURL + 'api/BigCustomer/submitReviewed',//-- 大客户需求订单提交
    customerBindProduct: BASEURL + 'api/BigCustomer/Product',//-- 大客户需求订单搜索商品列表
    customerProgrammeOrderList: BASEURL + 'api/BigCustomer/programmeOrderList',//-- 大客户方案列表
    customerProgrammeOrderInfo: BASEURL + 'api/BigCustomer/programmeOrderDetail',//-- 大客户方案详情
    customerProgrammeAdd: BASEURL + 'api/BigCustomer/programmeAdd',//-- 客户方案添加
    customerProgrammeEdit: BASEURL + 'api/BigCustomer/programmeEdit',//-- 客户方案编辑
    customerProgrammeDel: BASEURL + 'api/BigCustomer/programmeDel',//-- 客户方案删除
    customerProgrammeMerge: BASEURL + 'api/BigCustomer/programmeMerge',//-- 客户方案合并
    programmeReviewedList: BASEURL + 'api/BigCustomer/programmeReviewedList',//-- 客户方案待审核列表
    programmeReviewed: BASEURL + 'api/BigCustomer/programmeReviewed',//-- 客户方案状态更新（审核）
    bigCustomerOrderShDetail: BASEURL + 'api/BigCustomer/bigCustomerOrderShDetail',//-- 大客户生成订单详情页
}

/*** 对请求数据进行加密*/
// function serviceRequestEncryption(bodyData) {
//     let preKey = '87749CECEA24B1C314CC27CF7952EBC3'; //Md5加密（32位大写）
//     let objKeys = Object.keys(bodyData);
//     objKeys.sort(); //排序

//     let signStr = '';
//     objKeys.forEach(item => {
//         signStr = signStr + bodyData[item];
//     });
//     signStr = signStr + preKey;

//     let md51 = CryptoJS.MD5(signStr).toString();
//     let md51Super = md51.toUpperCase();

//     md51Super = md51Super.substring(2, 18);
//     let md52 = CryptoJS.MD5(md51Super).toString();

//     bodyData['sign'] = md52.toUpperCase();
//     return bodyData;
// }

export function NetWork_Post(net_api, bodyData, callback, netOptions) {
    // 加密
    // bodyData = serviceRequestEncryption(bodyData);
    let opt_headers, opt_error;
    if (typeof netOptions === 'object') {
        opt_headers = netOptions['headers'];
        opt_error = netOptions['error'];
    };

    let post_header = opt_headers ? opt_headers : {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    };

    let post_error = opt_error ? opt_error : {
        status: '1004',
        msg: '网络延时，请稍后重试！',
    };
    let url = reqUrl[net_api];
    let fetchOptions = {
        method: 'POST',
        mode: 'cors',
        headers: post_header,
        body: JSON.stringify(bodyData),
    }
    fetch(url, fetchOptions)
        .then((response) => response.text())
        .then((responseText) => {
            let responseData = JSON.parse(responseText);
            callback(responseData);//回调
        })
        .catch(error => {
            callback(post_error);
        })
}

export function NetWork_Get(net_api, callback, netOptions) {

    let url = reqUrl[net_api];
    let opt_error;

    if (typeof netOptions === 'object') {
        opt_error = netOptions['error'];
    };

    let get_error = opt_error ? opt_error : {
        status: '-1',
        msg: '请求失败',
    };

    fetch(url, {
        method: 'GET',
        credentials: 'include'
    })
        .then((response) => response.text())
        .then((responseText) => {
            let responseData = JSON.parse(responseText);
            callback(responseData);
        })
        .catch((error) => {
            callback(get_error);
        });
};

export function NetWork_File(net_api, bodyData, callback) {
    // 加密
    // bodyData = serviceRequestEncryption(bodyData);
    let post_header = {};
    let post_error = {
        status: '1004',
        msg: '网络延时，请稍后重试！',
    };
    let url = reqUrl[net_api];
    let fetchOptions = {
        method: 'POST',
        mode: 'cors',
        headers: post_header,
        body: bodyData,
    }
    fetch(url, fetchOptions)
        .then((response) => response.text())
        .then((responseText) => {
            let responseData = JSON.parse(responseText);
            callback(responseData);//回调
        })
        .catch(error => {
            callback(post_error);
        });
}