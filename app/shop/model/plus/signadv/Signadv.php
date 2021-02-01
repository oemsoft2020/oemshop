<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/10
 * Time: 11:13
 */

namespace app\shop\model\plus\signadv;

use app\common\model\plus\signadv\Signadv as SignAdvModel;
use app\common\model\user\User as UserModel;
use app\common\model\plus\signadv\SignAdvArchives as SignAdvArchivesModel;
use app\shop\model\settings\Setting as SettingModel;
use app\shop\model\product\Product as ProductModel;
use app\shop\model\product\Category as CategoryModel;
use app\common\enum\settings\SettingEnum;
use think\facade\Cache;

/**
 * 高级签到模型模型
 */
class Signadv extends SignAdvModel
{

    private $key = 'signadv';

    /**
     * 签到记录
     * @param $data
     * @return mixed
     */
    public function getList($data){
        $model = $this;
        $app_id = self::$app_id;

        // 检索：状态
        if (isset($data["status"]) && $data["status"] > -1){
            $model = $model->where('status',$data["status"]);
        }
        //检索：打卡时间
        if (!empty($data["reg_date"][0])) {
            $model = $model->whereTime('created_at','between',[$data["reg_date"][0],$data["reg_date"][1]]);
        }
        //检索：用户名
        if (!empty($data["user"])) {
            $user = $data["user"];
            $model = $model->where('uid','in',function ($query) use ($user){
                $query->table("kmdshop_user")->where('nickName|mobile','like','%'.$user.'%')->field('user_id');
            });
        }

        $list =  $model->alias('signadv')
            ->join('kmdshop_user user','user.user_id = signadv.uid and user.app_id = '.$app_id.' and signadv.app_id = '.$app_id)
            ->field('signadv.*,user.nickName,user.mobile,user.avatarUrl')
            ->order('signadv.created_at desc')
            ->paginate($data, false, [
                'query' => request()->request()
            ]);

        foreach ($list as $k => $v) {
            $list[$k]["created_at"] = date('Y-m-d H:i:s',$v["created_at"]);
            $list[$k]["img"] = json_decode($v["img"]);
            switch ($list[$k]["status"]){
                case 0:
                    $list[$k]["status"] = "未审核";
                    break;
                case 2:
                    $list[$k]["status"] = "未通过";
                    break;
                case 3:
                    $list[$k]["status"] = "已审核";
                    break;
            }
        }
        return $list;
    }

    /**
     * 档案列表
     * @param $data
     * @return mixed
     */
    public function archivesList($data){
        $app_id = self::$app_id;
        $model = new SignAdvArchivesModel();

        //检索：用户名
        if (!empty($data["user"])) {
            $user = $data["user"];
            $model = $model->where('uid','in',function ($query) use ($user){
                $query->table("kmdshop_user")->where('nickName|mobile','like','%'.$user.'%')->field('user_id');
            });
        }
        // 检索：状态
        if (isset($data["status"]) && $data["status"] > -1){
            switch ($data["status"]){
                case 0:  // 未到期
                    $model = $model->where('archives.dated_at','>=',time());
                    break;
                case 1:
                   $model = $model->where('archives.dated_at','<',time());
                    break;
            }
        }

        $list = $model->alias('archives')
            ->join('kmdshop_user user','user.user_id = archives.uid and user.app_id = '.$app_id.' and archives.app_id = '.$app_id)
            ->field('archives.dated_at,user.nickName,user.mobile,user.avatarUrl,user.user_id')
            ->order('user.user_id desc')
            ->paginate($data, false, [
                'query' => request()->request()
            ]);
        foreach ($list as $k => $v) {
            $list[$k]["dated_at"] = date('Y-m-d H:i:s',$v["dated_at"]);
            $list[$k]["count"] = $this->where('uid',$v["user_id"])->count();
            $max =  $this->where('uid',$v["user_id"])->max('kmd_signin_adv_id');
            $list[$k]["recently"] = date('Y-m-d H:i:s',$this->where('kmd_signin_adv_id',$max)->value('created_at'));
        }
        return $list;
    }

    /**
     * 更新配置
     * @param $data
     * @return bool
     */
    public function putRules($data){
        try{
            $setting = SettingModel::getItem($this->key,self::$app_id);
            $data["product_id"] = $setting["product_id"];
        }catch (\Exception $e){

        }
        if((new SettingModel())->edit($this->key,$data)){
            return true;
        }
        return false;
    }


    /**
     * 档案详情列表
     * @param $data
     * @return \think\Paginator
     * @throws \think\db\exception\DbException
     */
    public function archivesDetailed($data){
        $model = $this;
        // 检索：状态
        if (isset($data["status"]) && $data["status"] > -1){
            $model = $model->where('status',$data["status"]);
        }

        //检索：打卡时间
        if (!empty($data["reg_date"][0])) {
            $model = $model->whereTime('created_at','between',[$data["reg_date"][0],$data["reg_date"][1]]);
        }

        $list = $model->where('uid',$data["user_id"])->order('created_at desc')->paginate($data, false, [
            'query' => request()->request()
        ]);
        foreach ($list as $k => $v){
            $list[$k]["created_at"] = date("Y-m-d H:i:s",$v["created_at"]);
            $list[$k]["img"] = json_decode($v["img"]);
            switch ($list[$k]["status"]){
                case 0:
                    $list[$k]["status"] = "未审核";
                    break;
                case 2:
                    $list[$k]["status"] = "未通过";
                    break;
                case 3:
                    $list[$k]["status"] = "已审核";
                    break;
            }
        }
        return $list;
    }

    /**
     * 获取用户信息
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userInfo($data){
        if (!$this->getValidation($data["user_id"])){
            return false;
        }
        $user = (new UserModel())
            ->where('user_id',$data["user_id"])
            ->alias('user')
            ->join('kmdshop_user_grade grade','grade.grade_id = user.grade_id')
            ->field('user.user_id,user.nickName,user.grade_id,user.referee_id,grade.name')
            ->find();
        if ($user["referee_id"] > 0){
            $user["referee_name"] = UserModel::where('user_id',$user["referee_id"])->value('nickName');
        }
        $user["count"] = SignAdvModel::where(['uid' => $user["user_id"],"status" => 3])->count();
        $user["dated_at"] = date('Y-m-d H:i:s',SignAdvArchivesModel::where('uid',$user["user_id"])->value('dated_at'));
        return $user;
    }

    /**
     * 用户验证
     * @param $user_id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getValidation($user_id){
        $app_id = self::$app_id;
        if(UserModel::where(['app_id' => $app_id,"user_id" => $user_id])->find()){
            return true;
        }
        return false;
    }

    /**
     * 商品列表
     * @param $data
     * @return \think\Paginator
     * @throws \think\db\exception\DbException
     */
    public function getProductList($data){
        $app_id = self::$app_id;
        $model =  new ProductModel();

        //检索：商品分类
        if (!empty($data["categoryID"])) {
            $model = $model->where('product.category_id',$data["categoryID"]);
        }
        //检索：商品名称
        if (!empty($data["productName"])) {
            $model = $model->where('product.product_name','like','%'.$data["productName"].'%');
        }

        $list = $model->where('product.app_id',$app_id)
            ->alias('product')
            ->join('kmdshop_product_image img','product.product_id = img.product_id')
            ->join('kmdshop_category category','product.category_id = category.category_id')
            ->join('kmdshop_upload_file file','img.image_id = file.file_id')
            ->field('file.file_url,file.file_name,product.product_name,category.name,product.create_time,product.sales_initial,product.sales_actual')
            ->paginate($data, false, [
                'query' => request()->request()
            ]);
        return $list;
    }

    /**
     * 商品分类信息
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCategoryList(){
        return (new CategoryModel())->where('app_id',self::$app_id)->field('name')->select();
    }



    /**
     * @param $product_id
     * @return int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function putProductValidation($data){
        $app_id = self::$app_id;
        $product = (new ProductModel())->where(["app_id" => $app_id,"product_id" => $data["product_id"],"is_delete" => 0])->find();
        if (!$product){
            return $this->errorFault('该商品不存在');
        }
        if ($product["product_days"] == 0){
            return $this->errorFault('该商品没填写有效期');
        }
        // 删除系统设置缓存
        Cache::delete('setting_' . $app_id);
        try{  // 编辑
            $setting = SettingModel::getItem($this->key,$app_id);
            if (in_array($data["product_id"],$setting["product_id"])){
                return $this->errorFault('该商品已经绑定高级签到');
            }
            array_push($setting["product_id"],$data["product_id"]);
            (new SettingModel())->edit($this->key,$setting);
        }catch (\Exception $e){
            $data["product_id"] = [$data["product_id"]];
            (new SettingModel())->edit($this->key,$data);
        }
       return $this->successCorrect("绑定成功");
    }

    /**
     * 配置信息
     * @return array
     */
    public function getSettingList(){
        try{
            $list = SettingModel::getItem($this->key,self::$app_id);
            return $list;
        }catch (\Exception $e){
        }
    }

    /**
     * 绑定商品列表
     * @param $data
     * @return array|\think\Paginator
     */
    public function putProductList($data){
        $app_id = self::$app_id;
        try{
            $setting = SettingModel::getItem($this->key,$app_id);
            return (new ProductModel())->where("product_id",'in',$setting["product_id"])
                ->field('*')
                ->paginate($data, false, [
                'query' => request()->request()
                ]);
        }catch (\Exception $e){
            return [];
        }
    }

    public function goRemove($data){
        $app_id = self::$app_id;
        try{
            $setting = SettingModel::getItem($this->key,$app_id);
            unset($setting["product_id"][array_search($data["product_id"],$setting["product_id"])]);
            (new SettingModel())->edit($this->key,$setting);
            return $this->successCorrect("解除绑定成功");
        }catch (\Exception $e){
            return $this->errorFault('解除绑定失败');
        }
    }

}