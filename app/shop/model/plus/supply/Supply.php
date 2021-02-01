<?php

namespace app\shop\model\plus\supply;

use app\common\model\plus\supply\Supply as SupplyModel;
use app\shop\model\auth\User as ShopUser;
use app\shop\model\auth\Role;
use app\shop\controller\Controller;
use app\shop\model\product\Product as ProductModel;
use app\shop\model\plus\supply\Grade as GradeModel;
use app\common\model\user\User as UserModel;
use app\common\model\user\Grade as UserGradeModel;
/**
 * 供应商
 */
class Supply extends SupplyModel
{
    /**
     * @param $id
     * 供应商列表
     * @return \think\Collection
     */
    public function getList($param)
    {
        $model = $this;
        if (isset($param['status']) && $param['status'] > -1) {
            $model = $model->where('status', '=', $param['status']);
        }
        if (isset($param['name']) && !empty($param['name'])) {
            $model = $model->where('name', 'like', '%' . trim($param['name']) . '%');
        }

        if (!empty(self::$supply_id)) {
            $model = $model->where('supply_id', '=', self::$supply_id);
        }
        $list = $model->with(['file','user','shop_user','kmd_grade'])
            ->where('is_delete', '=', 0)
            ->order('create_time', 'desc')
            ->paginate($param, false, [
                'query' => request()->request()
            ]);

        return $list;
    }

    /**
     *获取为开始的数据列表
     */
    public function getDatas()
    {
        return $this->where('end_time', '<', time())->select();
    }

    private function getUserId($mobile)
    {
        $userModel = new UserModel();
        $shop_user = $userModel->where('mobile', '=', $mobile)->find();
        if(!$shop_user){
            $userModel->save([
                'mobile' => $mobile,
                'reg_source' => 'h5',
                //默认等级
                'grade_id' => UserGradeModel::getDefaultGradeId(),
                'app_id' => self::$app_id
            ]);
            $user_id = $userModel['user_id'];
        }else{
            $user_id = $shop_user['user_id'];
        }
        return $user_id;
    }

    public function add($data)
    {
        if (empty($data['kmd_grade_id'])) {
            $this->error = '请选择等级';
            return false;
        }
        if (empty($data['password'])) {
            $this->error = '请输入密码';
            return false;
        }
        if (empty($data['user_id'])) {
            $this->error = '请选择绑定用户';
            return false;
        }
        $this->startTrans();
        try {
            $arr = $this->setData($data);

            $data['user_name']= trim($data['user_name'])."@".self::$app_id;
            //添加管理员
            $shopUser = [
                'user_name' => $data['user_name'],
                'password' => isset($data['password'])?$data['password']:'',
                'real_name' => trim($data['name']),
                'role_id' => [$arr['role_id']]
            ];
			if(isset($data['status'])&& $data['status']==0){
                $shopUser['is_delete']=1;
            }
            $shop_user_model = new ShopUser();

            $num = $shop_user_model->getUserName(['user_name' => $data['user_name']]);
            if ($num > 0) {
                $this->error = '用户名已存在';
                $this->rollback();
                return false;
            }
            $res = $shop_user_model->add($shopUser);
            // 事务提交
            if ($res) {
               $user = $shop_user_model->where('user_name', $data['user_name'])->find();
                $arr['shop_user_id'] = $user->shop_user_id;
                unset($arr['role_id']);
                $this->save($arr);
               $this->commit();
                return true;
            }else{
                $this->rollback();
                return false;
            }
           
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    public function edit($data)
    {
        if (empty($data['kmd_grade_id'])) {
            $this->error = '请选择等级';
            return false;
        }
        $this->startTrans();
        try {
            $data['user_name']= trim($data['user_name'])."@".self::$app_id;
            $arr = $this->setData($data);
            $shopUser = [
                'user_name' => trim($data['user_name']),
                'password' => isset($data['password'])?$data['password']:'',
                'real_name' => trim($data['name']),
                'access_id' => [$arr['role_id']],
                'shop_user_id'=>$data['shop_user_id'],
                'user_id'=>$data['user_id']
            ];
			 if(isset($data['status'])&& $data['status']==0){
                $shopUser['is_delete']=1;
            }
            $shop_user_model = new ShopUser();
            $num = $shop_user_model->getUserName(['user_name' => $data['user_name']],$data['shop_user_id']);
            if ($num > 0) {
                $this->error = '用户名已存在';
                $this->rollback();
                return false;
            }

            $res = $shop_user_model->edit($shopUser);
            // 事务提交
            if ($res) {
                unset($arr['role_id']);
                $this->save($arr);
               $this->commit();
                return true;
            }else{
                $this->rollback();
                return false;
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 供应商删除
     */
    public function del()
    {
        // 如果有商品不能删除
        $count = (new ProductModel())->where('supply_id', '=', $this->supply_id)
            ->where('is_delete', '=', 0)
            ->count();
        if($count > 0){
            $this->error = '该供应商有商品不能删除';
            return false;
        }
        $model = new ShopUser();
        $model->del(['shop_user_id' => $this->shop_user_id]);
        return $this->save([
            'is_delete' => 1
        ]);
    }

    /**
     * 验证并组装数据
     * @param $data array  添加/新增数据
     * @param $type  string 类型
     * @return array
     */
    private function setData($data)
    {
        $grade = GradeModel::detail($data['kmd_grade_id'])->toArray();
        $setting = $grade['setting'];

        // 格式化坐标信息
        if(isset($data['coordinate'])&&!empty($data['coordinate'])){
            $coordinate = explode(',', $data['coordinate']);
            $data['latitude'] = $coordinate[0];
            $data['longitude'] = $coordinate[1];
        }
  
        $arr = [
            'image_id' => isset($data['image_id'])?$data['image_id']:0,
            'name' => $data['name'],
            'status' => isset($data['status']) ? $data['status'] : 0,
            'self_manage' => isset($data['self_manage']) ? $data['self_manage'] : 0,
            'user_id' =>$data['user_id'],
            'kmd_grade_id' =>$grade['kmd_grade_id'],
            'role_id' =>$setting['role_id'],
            'introduce' =>isset($data['introduce']) ? $data['introduce'] : '',
            'remark' =>isset($data['remark']) ? $data['remark'] : '',
            'image_url' =>isset($data['file_path']) ? $data['file_path'] : '',
            'sort' => isset($data['sort']) ? $data['sort'] : 0,
            'app_id' => self::$app_id,
            'address'=>isset($data['address']) ? $data['address'] : '',
            'latitude'=>isset($data['latitude'])?$data['latitude']:'',
            'longitude'=>isset($data['longitude'])?$data['longitude']:'',
            'video'=>isset($data['video'])?$data['video']:'',
            'poster'=>isset($data['poster'])?$data['poster']:'',
            'wxqrcode'=>isset($data['wxqrcode'])?$data['wxqrcode']:'',
            'is_forbidden_buy'=>isset($data['is_forbidden_buy'])?$data['is_forbidden_buy']:0,
            'product_show'=>isset($data['product_show'])?$data['product_show']:'two_column'
        ];

        return $arr;
    }
}