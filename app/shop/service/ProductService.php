<?php

namespace app\shop\service;

use app\common\service\product\BaseProductService;
use app\shop\model\product\Category as CategoryModel;
use app\shop\model\product\Brand as BrandModel;
use app\shop\model\settings\Delivery as DeliveryModel;
use app\shop\model\user\Grade as GradeModel;
use app\shop\model\plus\point\Product as PointsProductModel;
use app\shop\model\plus\assemble\Product as AssembleProductModel;
use app\shop\model\plus\bargain\Product as BargainProductModel;
use app\shop\model\plus\seckill\Product as SeckillProductModel;
use app\common\model\plus\supply\Supply as SupplyModel;

/**
 * 商品服务类
 */
class ProductService extends BaseProductService
{
    /**
     * 商品管理公共数据
     */
    public static function getEditData($model = null, $scene = 'edit')
    {
        // 商品分类
        $catgory = CategoryModel::getCacheTree();
        // $catgory = CategoryModel::getCacheAll();
        // 商品品牌
        $brand = BrandModel::getBrandCate();
        // 配送模板
        $delivery = DeliveryModel::getAll();
        // 会员等级列表
        $gradeList = GradeModel::getUsableList();
        foreach ($gradeList as $key => &$item) {
            $item['independent_commission'] = ['first_money'=>0,'second_money'=>0,'third_money'=>0];
        }
        unset($item);
        // 商品sku数据
        $specData = static::getSpecData($model);
        // 商品规格是否锁定
        $isSpecLocked = static::checkSpecLocked($model, $scene);
        $supply = SupplyModel::find(session('kmdshop_store')['supply_id']);
        return compact('catgory', 'delivery', 'gradeList', 'specData', 'isSpecLocked','supply','brand');
    }

    /**
     * 验证商品是否允许删除
     */
    public static function checkSpecLocked($model = null, $scene = 'edit')
    {
        if ($model == null || $scene == 'copy') return false;
        $service = new static;
        // 积分
        if ($service->checkPointsProduct($model['product_id'])) return true;
        // 拼团
        if ($service->checkAssembleProduct($model['product_id'])) return true;
        // 砍价
        if ($service->checkBargainProduct($model['product_id'])) return true;
        // 秒杀
        if ($service->checkSeckillProduct($model['product_id'])) return true;
        return false;
    }

    /**
     * 验证商品是否参与了积分商品
     */
    private function checkPointsProduct($productId)
    {
        return PointsProductModel::isExistProductId($productId);
    }

    /**
     * 验证商品是否参与了拼团商品
     */
    private function checkAssembleProduct($productId)
    {
        return AssembleProductModel::isExistProductId($productId);
    }

    /**
     * 验证商品是否参与了砍价商品
     */
    private function checkBargainProduct($productId)
    {
        return BargainProductModel::isExistProductId($productId);
    }

    /**
     * 验证商品是否参与了秒杀商品
     */
    private function checkSeckillProduct($productId)
    {
        return SeckillProductModel::isExistProductId($productId);
    }
}