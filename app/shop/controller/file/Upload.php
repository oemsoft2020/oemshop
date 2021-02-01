<?php

namespace app\shop\controller\file;

use app\KmdController;
use app\shop\model\file\UploadFile;
use app\common\library\storage\Driver as StorageDriver;
use app\shop\model\settings\Setting as SettingModel;

/**
 * 文件库管理
 */
class Upload extends KmdController
{
    /**
     * 图片上传接口
     */
    public function image($group_id = -1)
    {


        // 新增小程序图片上传代码
        $app_id = null;
        if($this->request->post('app_id')){
            $app_id = $this->request->post('app_id');
        }

        // 实例化存储驱动
        $config = SettingModel::getItem('storage',$app_id);

        // 新增 客满多上传
        $config = $this->kmdUpload($config);


        $StorageDriver = new StorageDriver($config);
        // 设置上传文件的信息
        $StorageDriver->setUploadFile('iFile');
        // 上传图片
        $saveName = $StorageDriver->upload();
        if ($saveName == '') {
            return json(['code' => 0, 'msg' => '图片上传失败' . $StorageDriver->getError()]);
        }

        $saveName = str_replace('\\','/',$saveName);
        // 图片上传路径
        $fileName = $StorageDriver->getFileName();
        // 图片信息
        $fileInfo = request()->file('iFile');
        $mimeType = $fileInfo->getOriginalMime();
        $type = explode('/',$mimeType);
        // 添加文件库记录
        $uploadFile = $this->addUploadFile($group_id, $fileName, $fileInfo, $type[0], $saveName,$app_id);
        // 图片上传成功
        return json(['code' => 1, 'msg' => '图片上传成功', 'data' => $uploadFile]);
    }

    /**
     * 添加文件库上传记录
     */
    private function addUploadFile($group_id, $fileName, $fileInfo, $fileType, $savename,$app_id=null)
    {
        // 存储引擎
        $config = SettingModel::getItem('storage',$app_id);

        $config = $this->kmdUpload($config);

        $storage = $config['default'];
        // 存储域名
        $fileUrl = isset($config['engine'][$storage]['domain'])
            ? $config['engine'][$storage]['domain'] : '';
        // 添加文件库记录
        $model = new UploadFile;
        $model->save([
            'group_id' => $group_id > 0 ? (int)$group_id : 0,
            'storage' => $storage,
            'file_url' => $fileUrl,
            'file_name' => $fileName,
            'save_name' => $savename,
            'file_size' => $fileInfo->getSize(),
            'file_type' => $fileType,
            'extension' => $fileInfo->getOriginalExtension(),
            'real_name' => $fileInfo->getOriginalName(),
            'app_id' => $model::$app_id,
            'supply_id' => $model::$supply_id
        ]);
        return $model;
    }
    /**
     * 批量移动文件分组
     */
    public function moveFiles($group_id, $fileIds)
    {
        $model = new UploadFile;
        if ($model->moveGroup($group_id, $fileIds) !== false) {
            return $this->renderSuccess('移动成功');
        }
        return $this->renderError($model->getError() ?: '移动失败');
    }

    /**
     * 客满多上传
     * @param $config
     * @return mixed
     */
    public function kmdUpload($config){
        if ($config["default"] == 'local'){
            $kmd_upload = env('KMD_UPLOAD.upload_type');
            switch ($kmd_upload){
                case "local":
                    break;
                case "qiniu":
                    $config["engine"]["qiniu"]["bucket"] = env("KMD_UPLOAD.upload_bucket");
                    $config["engine"]["qiniu"]["access_key"] = env("KMD_UPLOAD.upload_access_key");
                    $config["engine"]["qiniu"]["secret_key"] = env("KMD_UPLOAD.upload_secret_key");
                    $config["engine"]["qiniu"]["domain"] = env("KMD_UPLOAD.upload_domain");
                    $config["default"] = "qiniu";
                    break;
            }
        }
        return $config;
    }
}
