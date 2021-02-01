<?php

namespace app\shop\controller\user;

use app\shop\controller\Controller;
use app\shop\model\settings\Setting as SettingModel;
use app\shop\model\user\Grade as GradeModel;
use think\facade\Db;

class Setting extends Controller
{
    /* 
    * 获取配置
    */
    public function index()
    {
        if ($this->request->isGet()) {
            $values = SettingModel::getItem('memberSetting');
            $model = new GradeModel;
            $grade_list = $model->getLists();
            return $this->renderSuccess('', compact('values','grade_list'));
        }else{
            $model = new SettingModel;
            if ($model->edit('memberSetting', $this->postData())) {
                return $this->renderSuccess('操作成功');
            }
            return $this->renderError($model->getError() ?: '操作失败');
        }
       
    }

}