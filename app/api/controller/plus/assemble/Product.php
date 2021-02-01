<?php

namespace app\api\controller\plus\assemble;

use app\api\controller\Controller;
use app\api\model\plus\assemble\Product as ProductModel;
use app\api\model\plus\assemble\Active as ActiveModel;
use app\common\service\product\BaseProductService;
use app\api\model\plus\assemble\Bill as BillModel;
use app\api\model\product\Product as Productm;
/**
 * 拼团控制器
 */
class Product extends Controller
{
    /**
     * 拼团活动
     */
    public function active()
    {
        $model = new ActiveModel();
        $list = $model->activityList();
        return $this->renderSuccess('', compact('list'));
    }

    /**
     * 拼团商品
     */
    public function product($assemble_activity_id)
    {
        $detail = ActiveModel::detailWithTrans($assemble_activity_id);
        $list = (new ProductModel())->getActivityList($assemble_activity_id);

        $model_p = new Productm();
        foreach ($list as &$p) {
            $p['buy_auth'] = $model_p->checkBuyAuth($p,$this->getUser(false));
        }

        return $this->renderSuccess('', compact('detail','list'));
    }
    /**
     * 所有拼团商品
     */
    public function lists()
    {
        $data = $this->postData();
        $list = (new ProductModel())->getProductListAll($data);

        $model_p = new Productm();
        foreach ($list['data'] as &$p) {
            $p['buy_auth'] = $model_p->checkBuyAuth($p['product'],$this->getUser(false));
        }

        return $this->renderSuccess('', compact('list'));
    }

    /**
     * 拼团商品详情
     */
    public function detail($assemble_product_id, $url)
    {
        $model = new ProductModel();
        //详情
        $detail = $model->getAssembleDetail($assemble_product_id);

        $model_p = new Productm();
        $detail['buy_auth'] = $model_p->checkBuyAuth($detail['product'],$this->getUser(false));

        //活动
        $active = ActiveModel::detailWithTrans($detail['assemble_activity_id']);
        //规格
        $specData = BaseProductService::getSpecData($detail['product']);
        //拼团订单
        $model = new BillModel();
        $bill = $model->getBill($detail['assemble_product_id'], $detail['assemble_activity_id'], $detail['assemble_num']);
        // 微信公众号分享参数
        $share = $this->getShareParams($url, $detail['product']['product_name'], $detail['product']['product_name'], '/pages/plus/assemble/detail/detail', $detail['product']['image'][0]['file_path']);
        return $this->renderSuccess('', compact('detail', 'active', 'specData', 'bill','share'));
    }
}