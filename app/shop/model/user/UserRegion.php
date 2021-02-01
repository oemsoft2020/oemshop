<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/24
 * Time: 10:56
 */

namespace app\shop\model\user;

use app\common\model\user\UserRegion as UserRegionModel;
use app\common\model\settings\Region as RegionModel;

class UserRegion extends UserRegionModel
{

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
     * 用户地址详细信息
     * @param $data
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCustomerData($data){
        $list = $this->where(["app_id" => self::$app_id,"user_id" => $data["user_id"]])->find();
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

}