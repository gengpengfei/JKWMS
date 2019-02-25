<?php
namespace app\api\traits;

use think\Config;
use think\Db;

trait GetConfig{
    /*
     * explain:设置配置参数
     * params :@code
     * authors:Mr.Geng
     * addTime:2018/4/4 14:37
     */
    public function getConfig($code='')
    {
        if(Config::has($code)){
            return Config::get($code);
        }
        $system = Db::table('wms_system_config')->where('code',$code)->column('value','code');
        Config::set($system);
        return Config::get($code);
    }
}
