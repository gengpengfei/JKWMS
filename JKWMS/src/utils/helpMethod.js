//-- 判断管理员权限
const isInAction = (actionValue) => {
    //-- 获取权限列表
    var actionList = JSON.parse(localStorage.getItem('ActionList'));
    return actionList && actionList.length > 0 ? actionList.find((action, index, arr) => (action === actionValue)) : false;
}
export {
    isInAction
}