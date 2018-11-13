let CryptoJS = require("crypto-js");
export const BASEURL = 'http://10.20.10.81:8877/';
var reqUrl = {
    //-- 登录模块
    login: BASEURL + 'api/Product/productList',  //-- 用户登录
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

}
/*** 对请求数据进行加密*/
function serviceRequestEncryption(bodyData) {
    let preKey = '87749CECEA24B1C314CC27CF7952EBC3'; //Md5加密（32位大写）
    let objKeys = Object.keys(bodyData);
    objKeys.sort(); //排序

    let signStr = '';
    objKeys.forEach(item => {
        signStr = signStr + bodyData[item];
    });
    signStr = signStr + preKey;

    let md51 = CryptoJS.MD5(signStr).toString();
    let md51Super = md51.toUpperCase();

    md51Super = md51Super.substring(2, 18);
    let md52 = CryptoJS.MD5(md51Super).toString();

    bodyData['sign'] = md52.toUpperCase();
    return bodyData;
}

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
        // body: bodyData,
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