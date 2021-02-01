<?php
namespace app\common\model\plus\storage;

use app\common\model\BaseModel;
use app\common\exception\BaseException;

/**
 * Class Partake
 * 仓库模型
 * @package app\common\model\plus\storage
 */
class CodeProductInfo extends BaseModel
{
    protected $pk = 'code_product_info_id';

    /**
     * 添加签名
     * @param $user
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        $res = $this->where('storage_id',$data['storage_id'])->find();
        if($res){
            throw new BaseException(['msg' => '您已签名']);
        }
        $data = [
            'sign' => $data['sign'],
            'create_time' => time(),
            'user_id' => $data['user_id'],
            'storage_id' => $data['storage_id'],
            'app_id' => self::$app_id
        ];
        $tag = $this->insertGetId($data);
        return true;
    }

    public function getInfo($userid,$storageid){
        $map['user_id'] = $userid;
        $map['storage_id'] = $storageid;
        $res = $this->where($map)->find();
        return $res;
    }
}