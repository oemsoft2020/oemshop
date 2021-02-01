<?php


namespace app\api\model\plus\auction;
use app\common\model\plus\auction\Auction as AuctionleModel;

class Auction extends  AuctionleModel
{
    public function getList($data)
    {

        if (empty($data['search'])) {
            return $this->with(['image'])->where('is_delete', '=', 0)
                ->order(['create_time' => 'desc'])
                ->paginate($data, false, [
                    'query' => request()->request()
                ]);

        }else{

            return $this->with(['image'])->where('is_delete', '=', 0)
                ->where('auction_name','like','%'.trim($data['search']).'%')
                ->order(['create_time' => 'desc'])
                ->paginate($data, false, [
                    'query' => request()->request()
                ]);
        }
    }
    /**
     * 拍卖详情
     */
    public static function detail($auction_id)
    {
        if (!$model = parent::detail($auction_id)) {
            throw new BaseException(['msg' => '该拍卖不存在']);
        }

        return $model;
    }
}