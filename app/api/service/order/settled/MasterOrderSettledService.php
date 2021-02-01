<?php

namespace app\api\service\order\settled;

use app\api\model\order\Order;
use app\api\model\settings\Setting;
use app\common\enum\order\OrderSourceEnum;

/**
 * 普通订单结算服务类
 */
class MasterOrderSettledService extends OrderSettledService
{
    /**
     * 构造函数
     */
    public function __construct($user, $productList, $params)
    {
        parent::__construct($user, $productList, $params);
        //订单来源
        $this->orderSource = [
            'source' => OrderSourceEnum::MASTER,
        ];
        //自身构造,差异化规则
    }


    /**
     * 验证订单商品的状态
     */
    public function validateProductList()
    {
        $falsecount = 0;
        $countnum = count($this->productList);
        $order_model = new Order();
        foreach ($this->productList as &$product) {
            // 判断商品是否下架
            if ($product['product_status']['value'] != 10) {
                $this->error = "很抱歉，商品 [{$product['product_name']}] 已下架";
                return false;
            }
            // 判断商品库存
            if (($product['total_num'] > $product['product_sku']['stock_num']) || (empty($product['total_num']) && empty($product['product_sku']['stock_num']))) {
                $product['nobuy'] = true;
                $product['nobuytext'] = "库存不足";

                $falsecount += 1;
            }
            //判断是否限购
            if ($product['buy_limit'] > 0) {
                $count = $order_model->getBuyedProductNum($this->user['user_id'], $product['product_id']);
                $total_count = $count + $product['total_num'];

                if ($total_count > $product['buy_limit']) {
                    $this->error = $product['product_name'] . "限购数量" . $product['buy_limit'] . "件，您已购买" . $count . "件";
                    return false;
                }

                if ($product['total_num'] > $product['buy_limit']) {
                    $product['nobuy'] = true;
                    $product['nobuytext'] = "超过限购数量";

                    $this->error = "很抱歉，" . $product['product_name'] . "商品超过限购数量";
                    return false;
                }
            }

            if ($product['type'] == 'birthday') {
                $count = $order_model->getTodayBuyProductNum($this->user['user_id'], $product['product_id']);
                $total_count = $count + $product['total_num'];
                if ($total_count > 6) {
                    $this->error = $product['product_name'] . "每天限购数量6件，您已购买" . $count . "件";
                    return false;
                }
                if ($product['total_num'] > 6) {
                    $product['nobuy'] = true;
                    $product['nobuytext'] = "超过限购数量";

                    $this->error = "很抱歉，" . $product['product_name'] . "商品超过限购数量";
                    return false;
                }
            }

            $storage_vars = Setting::getItem('depot');


        }
        unset($product);
        if ($falsecount == $countnum) {
            $this->error = "很抱歉，商品均库存不足";
            return false;
        }
        return true;
    }
}