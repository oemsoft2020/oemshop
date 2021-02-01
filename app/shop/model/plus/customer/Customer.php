<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/18
 * Time: 15:53
 */

namespace app\shop\model\plus\customer;

use app\common\model\plus\customer\Customer as CustomerModel;
use app\common\model\settings\Region as RegionModel;
use app\shop\model\settings\Setting as SettingModel;
use think\facade\Cache;
use app\common\model\user\Grade as GradeModel;
use app\shop\model\user\User as UserModel;
use think\facade\Config;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class Customer extends CustomerModel
{
    private $key = 'customer';


    /**
     * 客户列表
     * @param $data
     * @return \think\Paginator
     * @throws \think\db\exception\DbException
     */
    public function getCustomerList($data){
        $model = $this;
        $app_id = self::$app_id;
        /* 检索地区 */
    /*    if (!empty($data["province_name"]) && $data["province_name"] != ""){
            $province_id = RegionModel::getIdByName($data["province_name"],1);
            $model = $model->where('province_id',$province_id);
            if (!empty($data["city_name"]) && $data["city_name"] != ""){
                $city_id = RegionModel::getIdByName($data["city_name"],2,$province_id);
                $model = $model->where('city_id',$city_id);
                if (!empty($data["region_name"]) && $data["region_name"] != ""){
                    $region_id = RegionModel::getIdByName($data["region_name"],3,$city_id);
                    $model = $model->where('region_id',$region_id);
                }
            }
        }*/
        if (!empty($data["province_id"]) && $data["province_id"] != ''){
            $model = $model->where('province_id',$data["province_id"]);
            if (!empty($data["city_id"]) && $data["city_id"] != ''){
                $model = $model->where('city_id',$data["city_id"]);
                if (!empty($data["region_id"]) && $data["region_id"] != ''){
                    $model = $model->where('region_id',$data["region_id"]);
                }
            }
        }
        /* 检索状态 */
        if (isset($data["status"]) && $data["status"] > -1){
            $model = $model->where('state',$data["status"]);
        }
        /* 检索客户姓名 */
        if (!empty($data["customer_name"])){
            $model = $model->where('customer_name','like','%'.$data["customer_name"].'%');
        }


        $list = $model->where('app_id',$app_id)
            ->order('update_time desc')
            ->paginate($data, false, [
            'query' => request()->request()
        ]);
        foreach ($list as $k => $v){
            $list[$k]["province_name"] = RegionModel::getNameById($v["province_id"]);
            $list[$k]["city_name"] = RegionModel::getNameById($v["city_id"]);
            $list[$k]["region_name"] = RegionModel::getNameById($v["region_id"]);
            if ($v["user_id"] != 0){
                $list[$k]["user_name"] = (new UserModel())->where(["user_id" => $v["user_id"]])->value('nickName');
            }else{
                $list[$k]["user_name"] = "无";
            }
        }
        return $list;
    }


    /**
     * 删除指定客户信息
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delCustomer($data){
        $list = $this->where(["customer_id" => $data["customer_id"],"app_id" => self::$app_id])->select()->count();
        if ($list == 0){
            return false;
        }
        try{
            $this->where('customer_id',$data["customer_id"])->delete();
            return true;
        }catch (\Exception $e){
            return false;
        }
    }



    /**
     * 客户详细信息
     * @param $data
     * @return array|null|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCustomerData($data){
        $model = $this;
        $app_id = self::$app_id;
        $list = $model->where(["app_id" => $app_id,"customer_id" => $data["customer_id"]])->find();
        if (!empty($list["province_id"])){
            $list["province_name"] = RegionModel::getNameById($list["province_id"]);
            if (!empty($list["city_id"])){
                $list["city_name"] = RegionModel::getNameById($list["city_id"]);
                if (!empty($list["region_id"])){
                    $list["region_name"] = RegionModel::getNameById($list["region_id"]);
                }
            }
        }
        return $list;
    }

    /**
     * 保存编辑的客户信息
     * @param $data
     * @return bool
     */
    public function getEdit($data){
        if (isset($data["customer_id"])){
            $customer_id = $data["customer_id"];
        }
        $data["app_id"] = self::$app_id;

        if ($data["state"] == 0){
            $data["user_id"] = 0;
        }
        unset($data["province_name"],$data["city_name"],$data["region_name"],$data["customer_id"]);
        try{
            if (isset($customer_id)){
                $this->where('customer_id',$customer_id)->save($data);
            }else{
                $this->save($data);
            }
            return true;
        }catch (\Exception $e){
            return false;
        }
    }

    /**
     * 更新配置信息
     * @param $data
     * @return bool
     */
    public function putSetting($data){
        // 删除系统设置缓存
        Cache::delete('setting_' . self::$app_id);
        try{
            $setting = SettingModel::getItem($this->key,self::$app_id);
            if (isset($setting["open"])){
                $data["open"] = $setting["open"];
            }
            unset($setting["setting"]);
        }catch (\Exception $e){

        }
        if (isset($data["grade_id"])){
            $grade_id = explode(',',$data["grade_id"]);
            array_pop($grade_id);
            $data["grade_id"] = $grade_id;
        }

        $data["setting"] = $data;
        if (isset($data["grade_id"])){
            unset($data["grade_id"]);
        }
        unset($data["is_bind"],$data["is_phone"],$data["is_navigation"],$data["is_open"],$data["is_max"]);
        if (isset($data["setting"]["open"])){
            unset($data["setting"]["open"]);
        }
        // 去重
        $data["setting"]["grade_id"] = array_unique($data["setting"]["grade_id"]);
        if((new SettingModel())->edit($this->key,$data)){
            return true;
        }
        return false;
    }

    /**
     * 会员等级
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserList(){
        return (new GradeModel())->where(["is_delete" => 0,"app_id" => self::$app_id])->field('name,grade_id')->select();
    }

    /**
     * 自定义配置
     * @param $data
     * @return bool
     */
    public function putOpen($data){
        try{
            $setting = SettingModel::getItem($this->key,self::$app_id);
            unset($data["open"]);
        }catch (\Exception $e){
        }
        $data["open"] = $data;
        if (isset($setting["setting"])){
            $data["setting"] = $setting["setting"];
        }
        unset($data["my_page_title"],$data["my_customer_count"],$data["public_page_title"],$data["bind_button"],$data["binding_button"],$data["true_bind_msg"],$data["false_bind_msg"]);
        if((new SettingModel())->edit($this->key,$data)){
            return true;
        }
        return false;
    }

    /**
     * 配置信息
     * @return array
     */
    public function getSetting($type){
        try{
            $list = SettingModel::getItem($this->key,self::$app_id);
            return $list[$type];
        }catch (\Exception $e){
        }
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
     * 绑定用户
     * @param $data
     * @return bool|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getBind($data){
        $list = $this->where(["customer_id" => $data["customer_id"]])->find();
        if ($list["user_id"] != $data["user_id"]){
            $setting = $this->getSetting('setting');
            if ($setting !== false){
                $count = $this->where('user_id',$data["user_id"])->count();
                if ($setting["is_max"] < ($count + 1)){  // 数量限制
                    return 2;
                }
            }else{
                return 3;
            }
        }
        if ($list["state"] == 0){
            $data["state"] = 1;
        }
        $customer_id = $data["customer_id"];
        unset($data["customer_id"]);
        $data["update_time"] = time();
        try{
            $this->where(["customer_id" => $customer_id])->save($data);
            return true;
        }catch (\Exception $e){
            return false;
        }
    }

    /**
     * 验证user_id
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function validation_user_id($data){
        $list = (new UserModel())->where(["app_id" => self::$app_id,"user_id" => $data["user_id"]])->select()->count();
        if ($list > 0){
            return true;
        }
        return false;
    }

    /**
     * 导入数据
     * @param $savename
     * @param $addens
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function putExcel($savename,$addens){
        set_time_limit(0);
        // 表头对应数据库字段
        $asset_white_list = [
            "customer_name" => "小学名称",
            "customer_address" => "地址",
            "customer_mobile" => "联系电话,电话"
        ];


        $upload_path = Config::get('filesystem.disks.public.root');
        $path = $upload_path."/".$savename;
        $bPage = 0;
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($path);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType); //实例化阅读器对象。
        $spreadsheet = $reader->load($path);  //将文件读取到到$spreadsheet对象中
        $sheetAllCount = $spreadsheet->getSheetCount(); // 工作表总数

       // 空表个数
       $sky_num = 0;

       for ($whatTable = 0;$whatTable < $sheetAllCount;$whatTable++){
           $sheet = $spreadsheet->getSheet($whatTable); // 读取第一個工作表
           $highest_row = $sheet->getHighestRow();   // 取得总行数
           $highest_column = $sheet->getHighestColumn(); // 取得最大列数  字母abc
           $highestColumnIndex = Coordinate::columnIndexFromString($highest_column);   //转化为数字;
           // 验证是否为空文件
           if ($highest_row <= 1){
               BYOUT:
               $sky_num++;
               continue;
           }

           // 获取有效开始行数
           for ($r = 1;$r < $highest_row;$r++){
               $highest_column_j = $sheet->getHighestColumn($r);
               if ($highest_column_j == $highest_column){
                   if ($r == $highest_row){
                      goto BYOUT;
                   }else{
                       for ($i = 1;$i <= $highestColumnIndex;$i++){
                           $table_title = $sheet->getCellByColumnAndRow($i,$r)->getValue();
                           if ($table_title == "" || $table_title == null){
                               goto BYOUT;
                           }
                           $title[] = $table_title;
                       }
                   }
                   $bPage = $r;  //有效数据从第几行开始
                   break;
               }
           }
            $db = [];
           for ($x = 1;$x <= $highestColumnIndex;$x++){  // 列
                for($y = $bPage;$y <= $highest_row;$y++){  // 行
                    $conent = $sheet->getCellByColumnAndRow($x, $y)->getValue();
                    $ppp[] = $conent;
                }
                $key = $this->array_serach_str($ppp[0],$asset_white_list);
                if ($key === false){

                }else{
                    unset($ppp[0]);
                    $ppp = array_merge($ppp);
                    for ($k = 0;$k<count($ppp);$k++){
                        $db[$k][$key] = $ppp[$k];
                        $db[$k]["province_id"] = $addens["province_id"];
                        $db[$k]["city_id"] = $addens["city_id"];
                        $db[$k]["region_id"] = $addens["region_id"];
                        $db[$k]["app_id"] = self::$app_id;
                        $db[$k]["create_time"] = time();
                    }
                }
                $ppp = [];
           }

           try{
               $this->insertAll($db);
               return true;
           }catch (\Exception $e){
                return false;
           }
       }
    }


    // 数组是否包含指定字符串，是返回键，否返回false
    public function array_serach_str($str,$array){
        foreach ($array as $k => $v){
            if(strstr($v,$str)){
                return $k;
            }
        }
        return false;
    }

}