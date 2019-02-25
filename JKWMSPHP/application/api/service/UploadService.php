<?php

namespace app\api\service;

class UploadService extends CommonService
{
    /*
     * explain:创建文件夹
     * params :
     * authors:Mr.Geng
     * addTime:2018/4/9 17:55
     */
    public function createFile($file)
    {
        $fileArr = explode ( "/", $file );
        $file_add = "";
        for($i = 1; $i < count ($fileArr) - 1; $i ++) {
            $file_add = empty ( $file_add ) ? $fileArr[$i] : $file_add . "/" . $fileArr[$i];
            if (! file_exists ( $file_add )) {
                mkdir ( $file_add, 0777 );
            }
        }
    }

}