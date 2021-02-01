<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/19
 * Time: 16:26
 */

namespace app\api\model\plus\customer;

use app\common\model\plus\customer\Customer as CustomerModel;
use app\shop\model\settings\Setting as SettingModel;
use app\common\model\settings\Region as RegionModel;
use app\common\model\user\UserAddress as UserAddressModel;
use app\common\model\user\User as UserModel;
use app\common\model\user\UserRegion as UserRegionModel;

class Customer extends CustomerModel
{
    public function getmyList($user_id){
        $list["list"] = $this->where(['user_id' => $user_id,"app_id" => self::$app_id])->select();
        $list["count"] = $this->where(['user_id' => $user_id,"app_id" => self::$app_id])->count();
        $list["open"] = $this->getSetting('open');
        return $list;
    }

    public function getSetting($type){
        try{
            $list = SettingModel::getItem('customer',self::$app_id);
            return $list[$type];
        }catch (\Exception $e){
            return false;
        }
    }

    /**
     * 客户详细信息
     * @param $user_id
     * @param $data
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getQueryList($user_id,$data){
        $customer_id = $data["customer_id"];
        $list = $this->where('customer_id',$customer_id)->select();
        foreach ($list as $k => $v){
            $list[$k]["province_name"] = RegionModel::getNameById($v["province_id"]);
            $list[$k]["city_name"] = RegionModel::getNameById($v["city_id"]);
            $list[$k]["region_name"] = RegionModel::getNameById($v["region_id"]);
        }
        $list["setting"] = $this->getSetting('setting');
        return $list;
    }

    /**
     * 根据地名获取经纬度
     * @param $data
     * @return 成功返回数据，失败返回false
     */
    public function getAddress($data){
        $key = "7SJBZ-W5PCW-CZPRK-RDWZ6-FUPIE-IFFYA";
        $address_name = $data["address_name"];
        $url = "https://apis.map.qq.com/ws/geocoder/v1/?address=".$address_name."&key=".$key;
        $data = $this->curl_get($url);
        return json_decode($data,true);
    }

    /**
     * 修改客户信息
     * @param $data
     * @return bool
     */
    public function put_my_customer($data){
        try{
            $save = [
                "customer_note" => $data["customer_note"],
                "state" => $data["state"],
                "update_time" => time(),
            ];
            if ($data["state"] == 0){
                $save["user_id"] = 0;
            }
            $this->where('customer_id',$data["customer_id"])->save($save);
            return true;
        }catch (\Exception $e){
            return false;
        }
    }

    public function getPickerList($user_id){

        // 判断是否开启区域绑定
        $setting = $this->getSetting('setting');
        $where = ["user_id" => 0,"app_id" => self::$app_id,"state" => 0];
        if (isset($setting["is_bind"]) && $setting["is_bind"] == 0){  // 开启区域绑定
            $region_list = (new UserRegionModel())->where('user_id',$user_id)->find();
            if($region_list){
                $user_province_id = $region_list["province_id"];
                $user_city_id = $region_list["city_id"];
                $user_region_id = $region_list["region_id"];
            }else{
                $user_province_id = 0;
                $user_city_id = 0;
                $user_region_id = 0;
            }
            $where["province_id"] = $user_province_id;
            $where["city_id"] = $user_city_id;
            $where["region_id"] = $user_region_id;

            goto GO;
        }
        $list["is_bind"] = 1;



        $arr1 = $this->getProvinceList();
        $arr_1 = [];
        $arr_1_id = [];
        foreach ($arr1 as $k => $v){
            array_push($arr_1,$v["name"]);
            array_push($arr_1_id,$v["id"]);
        }
        $arr2 = $this->getCityList(['province_id' => 1]);
        $arr_2 = [];
        foreach ($arr2 as $k => $v){
            array_push($arr_2,$v["name"]);
        }

        // 全部市级
        $arr_2_id = [];
        $arr2_all = \think\facade\Db::table('kmdshop_region')->where('level',2)->select();
        foreach ($arr2_all as $k => $v){
            array_push($arr_2_id,$v["id"]);
        }

        $arr3 = $this->getRegionList(['region_id' => 2]);
        $arr_3 = [];
        foreach ($arr3 as $k => $v){
            array_push($arr_3,$v["name"]);
        }

        // 全部地区
        $arr_3_id = [];
        $arr3_all = \think\facade\Db::table('kmdshop_region')->where('level',3)->select();
        foreach ($arr3_all as $k => $v){
            array_push($arr_3_id,$v["id"]);
        }
        $list['arr1'] = $arr_1;
        $list["arr2"] = $arr_2;
        $list["arr3"] = $arr_3;
        $list["arr1_id"] = $arr_1_id;
        $list["arr2_id"] = $arr_2_id;
        $list["arr3_id"] = $arr_3_id;


        GO:
        $customer_list = $this->where($where)->select();
        $list["customer_list"] = $customer_list;
        return $list;
    }

    public function getPickerList_1($data){
        $arr2 = $this->getCityList($data);
        $arr_2 = [];
        $arr2_id = [];
        foreach ($arr2 as $k => $v){
            array_push($arr_2,$v["name"]);
            array_push($arr2_id,$v["id"]);
        }

        if (count($arr2_id) < $data["detail_2"] + 1){
            $data["detail_2"] = 0;
        }
        $arr3 = $this->getRegionList(["region_id" => $arr2_id[$data["detail_2"]]]);
        $arr_3 = [];
        foreach ($arr3 as $k => $v){
            array_push($arr_3,$v["name"]);
        }

        $list["arr2"] = $arr_2;
        $list["arr3"] = $arr_3;
        return $list;
    }

    public function getPickerList_2($data){
        $arr3 = $this->getRegionList($data);
        $arr_3 = [];
        foreach ($arr3 as $k => $v){
            array_push($arr_3,$v["name"]);
        }
        $list["arr3"] = $arr_3;
        return $list;
    }

    /**
     * 查询用户
     * @param $user_id
     * @param $data
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function serchList($user_id,$data){
        $model = $this;
        $where = ["user_id" => 0,"app_id" => self::$app_id,"state" => 0];
        if($data["is_bind"] == 'true'){  // 未开启
            if ($data["addres"] != "省-市-区"){
                $arr = explode('-',$data["addres"]);
                $where["province_id"] = $arr[0];
                $where["city_id"] = $arr[1];
                $where["region_id"] = $arr[2];
            }
        }else{
            // 获取用户省市区
            $region_list = (new UserRegionModel())->where('user_id',$user_id)->find();
            if($region_list){
                $user_province_id = $region_list["province_id"];
                $user_city_id = $region_list["city_id"];
                $user_region_id = $region_list["region_id"];
            }else{
                $user_province_id = 0;
                $user_city_id = 0;
                $user_region_id = 0;
            }
            $where["province_id"] = $user_province_id;
            $where["city_id"] = $user_city_id;
            $where["region_id"] = $user_region_id;
       }
       if ($data["customer_name"] != ""){
           return  $model->where($where)->where('customer_name','like','%'.$data["customer_name"].'%')->select();

       }else{
           return $model->where($where)->select();
       }
    }

    /**
     * 绑定客户
     * @param $user_id
     * @param $data
     * @return int
     */
    public function getBind($user_id,$data){
        $setting = $this->getSetting('setting');
        if (isset($setting["is_max"])){
            $count = $this->where(["user_id" => $user_id,"app_id" => self::$app_id])->count();
            if ($count + 1 > $setting["is_max"]){
                return 1;  // 绑定上限
            }
            try{
                $this->where('customer_id',$data["customer_id"])->save([
                    "user_id" => $user_id,
                    "state" => 1
                ]);
                return 2;  // 绑定成功
            }catch (\Exception $e){
                return 3;  // 绑定失败
            }
        }

    }

    /**
     * 取消绑定
     * @param $user_id
     * @param $data
     * @return bool
     */
    public function putBind($user_id,$data){
        try{
            $this->where(["customer_id" => $data["customer_id"],'user_id' => $user_id,'app_id' => self::$app_id])->save([
                "user_id" => 0,
                "state" => 0,
                "update_time" => time()
            ]);
            return true;
        }catch (\Exception $e){
            return false;
        }
    }

    /**
     * 判断是否开启客户资源功能
     * @param $user_id
     * @return bool
     */
    public function is_open($user_id){
        $setting = $this->getSetting('setting');
        if ($setting !== false){
            if ($setting["is_open"] == 0){ // 开启
                if (in_array((new UserModel())->where('user_id',$user_id)->value('grade_id'),$setting["grade_id"])){
                    return true;
                }
                return false;
            }
        }
        return false;
    }


   /**
    * 省级信息
    * @return \think\Collection
    * @throws \think\db\exception\DataNotFoundException
    * @throws \think\db\exception\DbException
    * @throws \think\db\exception\ModelNotFoundException
    */
    public function getProvinceList(){
        $list = \think\facade\Db::table('kmdshop_region')->where('level',1)->field('id,name')->select();
        return $list;
    }

    /**
     * 市级信息
     * @param $data
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCityList($data){
        $list = \think\facade\Db::table('kmdshop_region')->where('pid',$data["province_id"])->field('id,name')->select();
        return $list;
    }


    /**
     * 区级信息
     * @param $data
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRegionList($data){
        $list = \think\facade\Db::table('kmdshop_region')->where('pid',$data["region_id"])->field('id,name')->select();
        return $list;
    }
    /**
     * curl接口调用【get】
     * @param $api , 接口地址
     * @param  ,是否验证ssl
     * @return 成功返回数据，失败返回false
     */
    public function curl_get($url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        return $data;

    }

}