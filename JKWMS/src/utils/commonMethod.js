/**
 * 日期格式化方法
 * date 要格式的date（必填）
 * fmt dateformat 日期格式（必填）
 */
import {
    Platform
} from 'react-native';


// import { Platform } from 'react-native'
import {isNotEmptyString, isNotEmptyArray} from "./structureJudgment";
import RegExpTool from './RegExpTool';

/**

 * @param {* date } date时间对象
 * @param {* fmt，想要的时间格式} sizeStr
 * @return {* 所需的字符串}
 */

function dateTransform(date, fmt) {
    var o = {
        "M+": date.getMonth() + 1,                 //月份
        "d+": date.getDate(),                    //日
        "h+": date.getHours(),                   //小时
        "m+": date.getMinutes(),                 //分
        "s+": date.getSeconds(),                 //秒
        "q+": Math.floor((date.getMonth() + 3) / 3), //季度
        "S": date.getMilliseconds()             //毫秒
    };
    if (/(y+)/.test(fmt))
        fmt = fmt.replace(RegExp.$1, (date.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt))
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
}




function filtrateEffectivityImgPath(pathStr,replacePath) {
    if (isNotEmptyString(pathStr)){
        return pathStr;
    }else {
        return replacePath?replacePath:'http:1';
    }
    
}


function arrayRemoveItem(arr,b) {

    var a = arr.indexOf(b);

    if (a >= 0) {
        arr.splice(a, 1);
        return true;
    }
    return false;
}

function isUrlStr(uriStr) {

    if (isNotEmptyString(uriStr)){

        let isUrl = RegExpTool.urlByReg(uriStr);
        return isUrl.check;
    }else {
        return false;
    }
}

// 仅适用与网络图片
function checkImageUrl(imagPath) {
    return isUrlStr(imagPath)?imagPath:'https://static.oschina.net/uploads/img/201712/06161126_E45W.png';
}


/** 
 * @method callOnceInInterval 单位时间内functionTobeCalled相应一次，防止过快点击响应多次，
 * @param functionTobeCalled 被包装的方法 
 * @param interval 时间间隔，可省略，默认600毫秒 
 */  
let isCalled = false, timer;
function callOnceInInterval(functionTobeCalled, interval = 600){
  
  if (!isCalled) {
    isCalled = true;  
    clearTimeout(timer);

    timer = setTimeout(() => {
      isCalled = false;  
    }, interval);  

    return functionTobeCalled;  
  }
   
}

/**
 *
 *
 * @param {* 图片数组 } imgPathArr 
 * @param {* 图片大小类型，默认为空，'/small' 为最小图，"/thum" 为中图,空为默认} sizeStr 
 * @return {* 图片路径} resultImagePath
 */
function configServerImagePath(imgPathArr,sizeStr='/small'){
    // small,thum
    let resultImagePath="";
    if(isNotEmptyArray(imgPathArr)){
        let reslut = '';
        for (let index = 0; index < imgPathArr.length; index++) {
            const element = imgPathArr[index];
            if(index === 1){
                reslut =reslut+sizeStr;    
            }
            reslut =reslut+element;
        }
        resultImagePath = reslut;
    }

    return resultImagePath;
    
}



export {
    dateTransform,
    filtrateEffectivityImgPath,
    arrayRemoveItem,
    isUrlStr,
    checkImageUrl,
    callOnceInInterval,
    configServerImagePath,
}


