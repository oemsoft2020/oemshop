<?php

namespace app\shop\controller\user;

use app\shop\controller\Controller;
use app\shop\model\user\User as UserModel;
use app\shop\model\user\Grade;

/**
 * 用户管理
 */
class User extends Controller
{
    /**
     * 商户列表
     */
    public function index($search = '', $gender = null, $reg_date = '', $grade_id = null)
    {
        $list = UserModel::getList($search, $grade_id, $reg_date, $gender = -1, $this->postData());
        $GradeModel = new Grade();
        $grade = $GradeModel->getLists();
        return $this->renderSuccess('', compact('list', 'grade'));
    }

    /**
     * 编辑用户信息
     * @return \think\response\Json
     */
    public function editAdd(){
        $model = new UserModel;
        if ($model->editAdd($this->request->param())) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }


    /**
     * 删除用户
     */
    public function delete($user_id)
    {
        // 用户详情
        $model = UserModel::detail($user_id);
        if ($model && $model->setDelete()) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?: '删除失败');
    }


    /**
     * 添加用户
     */
    public function add()
    {
        $model = new UserModel;
        // 新增记录
        if ($model->add($this->request->param())) {
            return $this->renderSuccess('添加成功');
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 用户充值
     */
    public function recharge($user_id, $source)
    {
        // 用户详情
        $model = UserModel::detail($user_id);
        if ($model->recharge($this->store['user']['user_name'], $source, $this->postData('params'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 等级改用户
     */
    public function edit($user_id)
    {
        // 用户详情
        $model = UserModel::detail($user_id);
        // 修改记录
        if ($model->updateGrade($this->postData())) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }

    /**
     * 批量修改用户等级
     */
    public function BatchChangegrade($params,$user_ids)
    {
        $model = new UserModel;
        $result = $model->BatchChangegrade($params,$user_ids);
        if (!$result) {
            return $this->renderError('操作失败，请检查是否选择原来的等级');
        }
        return $this->renderSuccess('操作成功');
    }


    /**
     * 批量禁用用户
     */
    public function UserForbidden($user_ids)
    {
        //var_dump($user_ids);die;
        $model = new UserModel;
        $result = $model->UserForbidden($user_ids);
        if (!$result) {
            return $this->renderError('操作失败，请检查当前选择是否全为禁用状态');
        }
        return $this->renderSuccess('操作成功');
    }
    /**
     * 批量解封用户
     */
    public function UserRelieve($user_ids)
    {

        $model = new UserModel;
        $result = $model->UserRelieve($user_ids);
        if (!$result) {
            return $this->renderError('操作失败，请检查当前选择是否全为启用状态');
        }
        return $this->renderSuccess('操作成功');
    }

    /**
     * 用户关系
     */
    public function Relation()
    {

        $data = $this->postData();
        if(!isset($data['referee_id'])||!isset($data['user_id'])){
            return $this->renderError('不可绑定');
        }

        if($data['referee_id']==$data['user_id']){
            return $this->renderError('不可绑定自己为自己的推荐人');
        }
        $user_model = new  UserModel();
        $res =  $user_model->bindRefereeByAdmin($data['user_id'],$data['referee_id']);
        
        if($res){
            return $this->renderSuccess('操作成功');
        }else{
            return $this->renderError($user_model->getError() ?: '绑定关系失败');
        }
        
    }



}
