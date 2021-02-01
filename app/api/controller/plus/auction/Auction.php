<?php


namespace app\api\controller\plus\auction;
use app\api\controller\Controller;
use app\api\model\plus\auction\Auction as AuctionleModel;
use app\api\model\settings\Setting;


class Auction extends Controller
{

    public function index()
    {
        $model = new AuctionleModel;
        $list = $model->getList($this->postData());
        $vars = Setting::getItem('auction');
        return $this->renderSuccess('', compact('list','vars'));
    }

    /**
     *详情
     */
    public function detail($auction_id)
    {
        $detail = AuctionleModel::detail($auction_id);
        return $this->renderSuccess('', compact('detail'));
    }

}

