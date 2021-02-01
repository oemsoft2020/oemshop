<?php

namespace app\shop\controller\data;

use app\shop\controller\Controller;
use app\shop\model\user\Grade;
use app\shop\model\user\User as UserModel;

/**
 * 用户数据控制器
 */
class User extends Controller
{
    /**
     * 商品列表
     */
    public function lists()
    {
        $model = new UserModel();
        $params = $this->postData();
//        $grade_id = $params['grade_id'];
//        $gender   = $params['sex'];
//        $nickName = $params['nickName'];
//        $mobile   = $params['mobile'];

        $list = $model->getLists($params);
        $GradeModel = new Grade();
        $grade = $GradeModel->getLists();
        return $this->renderSuccess('', compact('list', 'grade'));
    }

}
