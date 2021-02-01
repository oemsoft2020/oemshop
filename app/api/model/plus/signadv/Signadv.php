<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/4
 * Time: 0:46
 */

namespace app\api\model\plus\signadv;

use app\api\model\plus\agent\Setting;
use app\common\model\plus\signadv\Signadv as SignAdvModel;
use app\common\model\order\Order as OrderModel;
use app\common\model\order\OrderProduct as OrderProductModel;
use app\common\model\file\UploadFile as UploadFileModel;
use app\common\model\plus\signadv\SignAdvArchives as SignAdvArchivesModel;
use app\common\model\product\Product as ProductModel;
use app\shop\model\settings\Setting as SettingModel;
use think\facade\Cache;

class Signadv extends SignAdvModel
{
    /**
     * 签到打卡
     * @param $user
     * @param $data
     * @return string
     */
    public function add($user,$data){
        try{
            $user_id = $user['user_id'];
            $add = [
                'app_id' => $data["app_id"],
                'uid' => $user_id,
                'status' => 3,
                "field_number_1" => $data["right_eye_vision"],
                "field_number_2" => $data["left_eye_vision"],
                "field_number_3" => $data["eyes_vision"],
                "img" => json_encode(explode(',',$data["img"])),
                "remark" => '',
                "created_at" => time(),
            ];
            //   var_dump($add);die();
            $this->save($add);
            return true;
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * 判断是否打卡
     * @param $user_id
     * @return array|null|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException]
     */
    public function trueSign($user_id){
        $start_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $end_time = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
        $condition = [
            ['uid','=',$user_id],
            ["status",'=','3'],
            ["created_at",'between',[$start_time,$end_time]]
        ];
        return $this->where($condition)->find();
    }

    /**
     * 获取用户打卡记录
     * @param $user
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSignAdvLog($user){
        $log = $this->where('uid',$user["user_id"])->order('created_at asc')->select();
        foreach ($log as $k => $v) {
            $log[$k]["created_at"] = date('Y-m-d H:i:s',$v["created_at"]);
            switch ($v["status"]){
                case 0:
                    $log[$k]["status"] = "待审核";
                    break;
                case 2:
                    $log[$k]["status"] = "未通过";
                    break;
                case 3:
                    $log[$k]["status"] = "已审核";
                    break;
            }
            $log[$k]["img"] = json_decode($v["img"]);
        }
        return $log;
    }

    /**
     *  获取签到的天数
     * @param $user_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getListByUserId($user_id)
    {
        $start_time = mktime(0,0,0,date('m'),1,date('Y'));
        $end_time = mktime(23,59,59,date('m'),date('t'),date('Y'));
        $condition = [
            ["uid",'=',$user_id],
            ["created_at","between",[$start_time,$end_time]]
        ];
        $list = $this->where($condition)->order(['created_at' => 'desc'])->select();
        $arr = [];
        foreach ($list as $k => $v){
            array_push($arr,(int)date('j',$v["created_at"]));
        }
        return $arr;
    }

    /**
     * 视力数据验证
     * @param $left_eye_vision
     * @param $right_eye_vision
     * @param $eyes_vision
     * @return bool
     */
    public function dataValidation($left_eye_vision,$right_eye_vision,$eyes_vision){
        $min_vision = 0.0;
        $max_vision = 20.0;
        if (($left_eye_vision > $max_vision || $left_eye_vision < $min_vision) || ($right_eye_vision > $max_vision || $right_eye_vision < $min_vision) || ($eyes_vision > $max_vision || $eyes_vision < $min_vision)){
            return false;
        }
        return true;
    }

    /**
     * @param $user_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function dataArchives($user_id){
        $where["uid"] = $user_id;
        $archives = new SignAdvArchivesModel();
        $db = $archives->where($where)->find();  // 查询档案
        if (empty($db)){  // 没有档案
            $order_list = $this->effectiveOrder($user_id);
            if (!$order_list){
                return $this->errorFault('您还未购买训练打卡商品，是否前去购买？');
            }
            return $this->successCorrect('您已有可开通训练打卡的订单，是否前去开通训练打卡？',$this->orderManage($order_list));
        }
        // 档案过期
        if ($db["dated_at"] < time()){
            $order_list = $this->effectiveOrder($user_id);
            if ($db["days"] == 0){
                if (!$order_list){
                    return $this->errorFault('您的训练打卡商品已过期，是否前往购买续期？');
                }
            }
            if ($db["days"] > 0){
                return $this->autoRenewal($db["id"],$db["dated_at"],$db["days"]);
            }
            return $this->successCorrect('您已有可开通训练打卡的订单，是否前去开通训练打卡？',$this->orderManage($order_list));
        }
        return $this->successCorrect('ok');
    }

    /**
     * 查询有效订单
     * @param $user_id
     * @return int|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function effectiveOrder($user_id){
        $orderModel = new OrderModel();
        $order_where = [
            "user_id" => $user_id,
            "is_delete" => 0,  // 是否删除
            "order_status" => 10,  // 订单状态
            "pay_status" => 20,  // 付款状态
            "delivery_status" => 10,  // 发货状态
            "receipt_status" => 10, // 收货状态
        ];
        // 查询有效订单（商品参与活动标签订单状态正常）
       /* $trueOrderList = $orderModel->where('order_id','in',function ($query) use ($user_id){
            $query->table("kmdshop_order_product")->where('product_id','in',function ($q) use ($user_id){
                $q->table("kmdshop_product")->where("product_days",'>',0)->field('product_id');
            })->where('user_id',$user_id)->field('order_id');
        })->where($order_where)->select();*/
       try{
           $setting = SettingModel::getItem('signadv',self::$app_id);
           if (isset($setting["product_id"]) && $setting["product_id"] != []){
                $arr = $setting["product_id"];
           }else{
               return false;
           }
       }catch (\Exception $e){
           return false;
       }
        $trueOrderList = $orderModel->where('order_id','in',function ($query) use ($user_id,$arr){
            $query->table("kmdshop_order_product")->where('product_id','in',function ($q) use ($user_id,$arr){
                $q->table("kmdshop_product")->where("product_id",'in',$arr)->field('product_id');
            })->where('user_id',$user_id)->field('order_id');
        })->where($order_where)->select();
        if (count($trueOrderList) == 0){
            return false; // 请前往购买
        }
      //  dd($trueOrderList);
        return $trueOrderList;
    }

    /**
     * 有效订单信息处理
     * @param $order_list
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderManage($order_list){
        $orderProductModel = new OrderProductModel();
        $uploadFileModel = new UploadFileModel();
        $productModel = new ProductModel();
        foreach ($order_list as $k => $v){
            $order = $orderProductModel->where('order_id',$v["order_id"])->field('product_name,image_id,total_num,product_id')->select();
            $i = 0;
            $arr = [];
            $days_count = 0;
            foreach ($order as $key => $value){
                $days = $productModel->where('product_id',$value["product_id"])->value("product_days");
                if ($days > 0){
                    $img = $uploadFileModel->where('file_id',$value["image_id"])->field('file_url,file_name')->find();
                    $arr[$i]["product_img"] = $img["file_url"].'/'.$img["file_name"];
                    $arr[$i]["product_name"] = $value["product_name"];
                    $arr[$i]["product_num"] = $value["total_num"];
                    $order_list[$k]["product_list"] = $arr;
                    $i++;
                    $days_count = $days_count+$value["total_num"]*$days;
                }
            }
            $order_list[$k]["product_count"] = count($order_list[$k]["product_list"]);
            $order_list[$k]["days_count"] = $days_count;
            $order_list[$k]["checked"] = false;
        }
    //    dd($order_list);
        return $order_list;
    }

    public function orderCheck($order_list){

    }

    /**
     * 打卡规则说明按钮
     * @param $user_id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function isRules($user_id){
        $list = (new SignAdvArchivesModel())->where('uid',$user_id)->select()->count();
        if ($list == 0){
            return false;
        }
        return true;
    }

    /**
     * 获取配置项指定内容
     * @param $key
     * @return string
     */
    public function getSetting($key){
        $app_id = self::$app_id;
        // 删除系统设置缓存
        Cache::delete('setting_' . $app_id);
        try{
            $setting = SettingModel::getItem('signadv',$app_id);
            if (isset($setting[$key['key']])){
                return $setting[$key['key']];
            }
        }catch (\Exception $e){

        }
        return '';
    }

    /**
     * 自动续费档案
     * @param $id
     * @param $dated_at
     * @param $days
     * @return array
     */
    public function autoRenewal($id,$dated_at,$days){
        $archives = new SignAdvArchivesModel();
        $dated_at = $dated_at + strtotime("+".$days."day");
        try{
            $archives->where('id',$id)->save(["dated_at" => $dated_at,"days" => 0]);
        }catch (\Exception $e){
            return $this->errorFault('系统繁忙，请稍后再试');
        }
    }

    /**
     * 订单绑定
     * @param $data
     * @param $user_id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderChange($data,$user_id){
        $orderModel = new OrderModel();
        $archives = new SignAdvArchivesModel();
        $save = [
            "express_id" => 10002,
            "express_no" => 88888888,
            "delivery_status" => 20,
            "delivery_time" => time(),
            "update_time" => time(),
            "receipt_status" => 20,
            "receipt_time" => time(),
            "order_status" => 30
        ];
        $archivesDetails = $archives->where('uid',$user_id)->field('days,dated_at,id')->find();
        $order_ids = explode(',',$data["order_ids"]);
        array_shift($order_ids);
        $pop = ["app_id" => $data["app_id"], "days" => 0,"uid" => $user_id];
        if (isset($archivesDetails)){
            $days = $archivesDetails["days"] + $data["days"];
            $pop = ["app_id" => $data["app_id"], "days" => 0,"uid" => $user_id];
            $pop["id"] = $archivesDetails["id"];
            $pop["dated_at"] = strtotime("+".$days."day");
        }else{
            $pop["created_at"] = time();
            $pop["dated_at"] = strtotime("+".$data["days"]."day");
        }
        \think\facade\Db::startTrans();
        try{
            $orderModel->where('order_id','in',$order_ids)->save($save);
            if (isset($pop["id"])){
                $archives->update($pop);
            }else{
                $archives->save($pop);
            }
            \think\facade\Db::commit();
            return true;
        }catch (\Exception $e){
            \think\facade\Db::rollback();
            return false;
        }
    }
}