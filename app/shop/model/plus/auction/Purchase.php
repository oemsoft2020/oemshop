<?php


namespace app\shop\model\plus\auction;

use app\common\model\plus\auction\Purchase as PurchaseModel;
class Purchase extends PurchaseModel
{
    public function getPurchase($data)
    {

        return $this->where('auction_type', '>', 0)
            ->order(['create_time' => 'desc'])
            ->paginate($data, false, [
                'query' => request()->request()
            ]);
    }
}