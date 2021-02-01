<?php

namespace app\shop\model\plus\auction;

use app\shop\model\user\User as UserModel;
use app\common\model\plus\auction\Auction as AuctionModel;
use app\common\exception\BaseException;

/**
 * 用户拍卖模型
 */
class Auction extends AuctionModel
{
    /**
     * @param $data array 查询条件
     * 
     * @return mixed
     */
    public function getList($data)
    {
        
          return $this->with(['image'])->where('is_delete', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate($data, false, [
                'query' => request()->request()
            ]);
    }

    public function purchase($data)
    {
        return $this->with(['image'])->where('is_delete', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate($data, false, [
                'query' => request()->request()
            ]);
    }
    /**
     * 新增记录
     */
    public function add($data)
    {

        $data['app_id'] = self::$app_id;
        return $this->save($data);
    }

    /**
     * 更新记录
     */
    public function edit($data)
    {

        $where['auction_id'] = $data['auction_id'];
        unset($data['auction_id']);
        return self::update($data, $where);
    }


    /**
     * 删除记录 (软删除)
     */
    public function setDelete($where)
    {
        return self::update(['is_delete' => 1], $where);
    }


   

}
