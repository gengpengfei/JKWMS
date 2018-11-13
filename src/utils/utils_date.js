
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

/**
 * @author hulinhua
 * @param {* inputDate } date对象
 * @return {* t2 } 上一个月，date对象
 */
function getPreMonth(inputDate) {

    /*******获取年月日********/
    var year = inputDate.getFullYear(); //获取当前日期的年份  
    var month = inputDate.getMonth() + 1; //获取当前日期的月份  
    var day = inputDate.getDate(); //获取当前日期的日  

    /*******计算上一个月的年月日********/
    var days = new Date(year, month, 0);
    days = days.getDate(); //获取当前日期中月的天数  
    var year2 = year;
    var month2 = parseInt(month) - 1;
    if (month2 == 0) {
        year2 = parseInt(year2) - 1;
        month2 = 12;
    }
    var day2 = day;
    var days2 = new Date(year2, month2, 0);
    days2 = days2.getDate();
    if (day2 > days2) {
        day2 = days2;
    }
    if (month2 < 10) {
        month2 = '0' + month2;
    }

    /*******将年月日转换成date 对象********/
    var t2 = new Date(year2, month2 - 1, day2);
    return t2;
}


/**
 * @author hulinhua
 * @param {* inputDate } date对象
 * @return {* t2 } 上一个月，date对象
 */
function getNextMonth(inputDate) {


    /*******获取年月日********/
    var year = inputDate.getFullYear(); //获取当前日期的年份  
    var month = inputDate.getMonth() + 1; //获取当前日期的月份  
    var day = inputDate.getDate(); //获取当前日期的日  

    /*******计算下一个月的年月日********/
    var days = new Date(year, month, 0);
    days = days.getDate(); //获取当前日期中的月的天数  
    var year2 = year;
    var month2 = parseInt(month) + 1;
    if (month2 == 13) {
        year2 = parseInt(year2) + 1;
        month2 = 1;
    }
    var day2 = day;
    var days2 = new Date(year2, month2, 0);
    days2 = days2.getDate();
    if (day2 > days2) {
        day2 = days2;
    }
    if (month2 < 10) {
        month2 = '0' + month2;
    }

    /*******将年月日转换成date 对象********/
    var t2 = new Date(year2, month2 - 1, day2);
    return t2;
}


/**
 * @author hulinhua
 * @param {* inputDate } date对象
 * @return {* t2 } 下一天,date对象
 */
function getNextDay(inputDate) {


    /*******获取年月日********/
    var year = inputDate.getFullYear(); //获取当前日期的年份  
    var month = inputDate.getMonth(); //获取当前日期的月份  
    var day = inputDate.getDate(); //获取当前日期的日  

    /*******将年月日转换成date 对象********/
    var t2 = new Date(year, month, day + 1);
    return t2;
}


/**
 * @author hulinhua
 * @description 获取星期几
 * @param {* inputDate } date对象
 * @return {* t2 } 下一天,date对象
 */
function getWeekDay(inputDate) {
    var day = inputDate.getDay();

    var dayName = '';
    switch (day) {
        case 0:
            dayName = "星期天";
            break;
        case 1:
            dayName = "星期一";
            break;
        case 2:
            dayName = "星期二";
            break;
        case 3:
            dayName = "星期三";
            break;
        case 4:
            dayName = "星期四";
            break;
        case 5:
            dayName = "星期五";
            break;

        case 6:
            dayName = "星期六";
            break;

        default:
            break;
    }

    return dayName;

}

/**
 * @description 是否是同一年
 * @param {* 日期1 } date1 
 * @param {* 日期1 } date2 
 * @return bool
 */
function yearIsEquire(date1, date2) {

    var year1 = date1.getFullYear();
    var year2 = date2.getFullYear();

    return year1 === year2 ? true : false;
}

/**
 * @description 是否是同一月
 * @param {* 日期1 } date1 
 * @param {* 日期1 } date2 
 * @return bool
 */
function monthIsEquire(date1, date2) {
    if (yearIsEquire(date1, date2)) {
        var month1 = date1.getMonth();
        var month2 = date2.getMonth();
        return month1 === month2 ? true : false;
    } else {
        return false;
    }
}

/**
 * @description 是否是同一天
 * @param {* 日期1 } date1 
 * @param {* 日期1 } date2 
 * @return bool
 */
function dayIsEquire(date1, date2) {

    if (monthIsEquire(date1, date2)) {
        var day1 = date1.getDate();
        var day2 = date2.getDate();
        return day1 === day2 ? true : false;
    } else {
        return false;
    }
}

export {
    dateTransform,
    getPreMonth,
    getNextMonth,
    getNextDay,
    getWeekDay,
    yearIsEquire,
    monthIsEquire,
    dayIsEquire,

}

