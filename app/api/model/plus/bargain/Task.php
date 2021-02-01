<?php

namespace app\api\model\plus\bargain;

use app\api\model\product\Product as ProductModel;
use app\api\model\plus\bargain\Product as BargainProductModel;
use app\api\model\plus\bargain\ProductSku as BargainSkuModel;
use app\api\model\plus\bargain\Active as ActiveModel;
use app\api\model\settings\Setting as SettingModel;
use app\common\exception\BaseException;
use app\common\model\plus\bargain\Task as TaskModel;
use app\api\model\plus\bargain\TaskHelp as TaskHelpModel;
use app\api\service\bargain\Amount as AmountService;
use app\common\library\helper;

/**
 * 砍价任务模型
 */
class Task extends TaskModel
{
    /**
     * 隐藏的字段
     * @var array
     */
    protected $hidden = [
        'peoples',
        'section',
        'is_delete',
        'app_id',
        'update_time',
    ];

    /**
     *  通过砍价活动id 砍价活动商品 获取砍价任务表信息
     * @param $user_id 用户id
     * @param $activity_id 活动ID
     * @param $activity_product_id 活动商品id
     */
    public static function getTaskByBargain($user_id,$activity_id,$activity_product_id){
        return self::where('bargain_activity_id','=',$activity_id)
            ->where('bargain_product_id','=',$activity_product_id)
            ->where('is_buy','=',0)
            ->where('status','=',0)
            ->where('is_delete','=',0)
            ->where('user_id','=',$user_id)
            ->where('end_time',">=",time())
            ->order('bargain_task_id','desc')
            ->find();
    }

    /**
     * 我的砍价列表
     */
    public function getList($user_id, $params)
    {
        // 砍价活动列表
        return $this->where('user_id', '=', $user_id)->with(['file'])
            ->where('status', '=', $params['status'])
            ->where('is_delete', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate($params, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 获取砍价任务详情
     */
    public function getTaskDetail($bargain_task_id, $user = false)
    {
        // 砍价任务详情
        $task = static::detail($bargain_task_id, ['user']);
        if (empty($task)) {
            throw new BaseException(['msg' => '砍价任务不存在']);
        }
        // 砍价活动详情
        $active = ActiveModel::detail($task['bargain_id']);
        // 获取商品详情
        $product = BargainProductModel::detail($task['bargain_product_id'], ['product'=>['image.file','sku']]);
        // 好友助力榜
        $help_list = TaskHelpModel::getListByTaskId($bargain_task_id);
        // 当前是否为发起人
        $is_creater = $this->isCreater($task, $user);
        // 当前是否已砍
        $is_cut = $this->isCut($help_list, $user);
        // 砍价规则
        $setting = SettingModel::getBargain();
        return compact('task', 'is_creater', 'is_cut', 'active', 'product', 'help_list', 'setting');
    }

    /**
     * 获取砍价任务的商品列表（用于订单结算）
     */
    public function getTaskGoods($taskId)
    {
        // 砍价任务详情
        $task = static::detail($taskId);
        if (empty($task) || $task['is_delete'] || $task['status'] == false) {
            $this->error = '砍价任务不存在或已结束';
            return false;
        }
        if ($task['is_buy'] == true) {
            $this->error = '该砍价商品已购买';
            return false;
        }
        // 砍价商品详情
        $goods = ProductModel::detail($task['goods_id']);
        // 商品sku信息
        $goods['goods_sku'] = ProductModel::getGoodsSku($goods, $task['spec_sku_id']);
        // 商品列表
        $goodsList = [$goods->hidden(['category', 'content', 'image', 'sku'])];
        foreach ($goodsList as &$item) {
            // 商品单价
            $item['goods_price'] = $task['actual_price'];
            // 商品购买数量
            $item['total_num'] = 1;
            $item['spec_sku_id'] = $item['goods_sku']['spec_sku_id'];
            // 商品购买总金额
            $item['total_price'] = $task['actual_price'];
        }
        return $goodsList;
    }


    /**
     * 新增砍价任务
     */
    public function add($user_id, $bargain_activity_id, $bargain_product_id, $bargain_product_sku_id, $product_sku_id)
    {
        // 获取活动详情
        $active = ActiveModel::detail($bargain_activity_id);
        // 验证能否创建砍价任务
        if (!$this->onVerify($active, $user_id)) {
            return false;
        }
        // 获取商品详情
        $product = BargainProductModel::detail($bargain_product_id, ['product.image']);
        // 商品sku信息
        $product_sku = BargainSkuModel::detail($bargain_product_sku_id, ['productSku']);
        // 创建砍价任务
        return $this->addTask($user_id, $active, $product, $product_sku, $product_sku_id);
    }

    /**
     * 创建砍价任务记录
     */
    private function addTask($user_id, $active, $product, $product_sku, $product_sku_id)
    {
        // 分配砍价金额区间
        $section = $this->calcBargainSection(
            $product_sku['product_price'],
            $product_sku['bargain_price'],
            $product_sku['bargain_num']
        );
        // 新增记录
        return $this->save([
            'bargain_activity_id' => $active['bargain_activity_id'],
            'user_id' => $user_id,
            'bargain_product_id' => $product['bargain_product_id'],
            'bargain_product_sku_id' => $product_sku['bargain_product_sku_id'],
            'product_price' => $product_sku['product_price'],
            'bargain_price' => $product_sku['bargain_price'],
            'actual_price' => $product_sku['product_price'],
            'peoples' => $product_sku['bargain_num'],
            'image_id' => $product['product']['image'][0]['image_id'],
            'product_name' => $product['product']['product_name'],
            'product_attr' => $product_sku['product_attr'],
            'product_sku_id' => $product_sku_id,
            'cut_people' => 0,
            'section' => $section,
            'cut_money' => 0.00,
            'end_time' => time() + ($active['together_time'] * 3600),
            'app_id' => self::$app_id,
        ]);
        //增加参与人数
        (new BargainProductModel)->where('bargain_product_id', '=', $product['bargain_product_id'])->inc('join_num')->update();
    }
    /**
     * 帮砍一刀
     */
    public function helpCut($user)
    {
        // 好友助力榜
        $helpList = TaskHelpModel::getListByTaskId($this['bargain_task_id']);
        // 当前是否已砍
        if ($this->isCut($helpList, $user)) {
            $this->error = '您已参与砍价，请不要重复操作';
            return false;
        }
        // 帮砍一刀事件
        return $this->transaction(function () use ($user) {
            return $this->onCutEvent($user['user_id'], $this->isCreater($this, $user));
        });
    }

    /**
     * 砍一刀的金额
     */
    public function getCutMoney()
    {
        return $this['section'][$this['cut_people']];
    }

    /**
     * 帮砍一刀事件
     */
    private function onCutEvent($userId, $isCreater = false)
    {
        // 砍价金额
        $cutMoney = $this->getCutMoney();
        // 砍价助力记录
        $model = new TaskHelpModel;
        $model->add($this, $userId, $cutMoney, $isCreater);
        // 实际购买金额
        $actualPrice = helper::bcsub($this['actual_price'], $cutMoney);
        // 更新砍价任务信息
        $this->save([
            'cut_people' => ['inc', 1],
            'cut_money' => ['inc', $cutMoney],
            'actual_price' => $actualPrice,
            'is_floor' => helper::bcequal($actualPrice, $this['bargain_price']),
            'status' => helper::bcequal($actualPrice, $this['bargain_price']) == 0?0:1,
        ]);
        return true;
    }



    /**
     * 砍价任务标记为已购买
     */
    public function setIsBuy()
    {
        return $this->save(['is_buy' => 1]);
    }

    /**
     * 分配砍价金额区间
     */
    private function calcBargainSection($product_price, $bargain_price, $bargain_num)
    {
        $AmountService = new AmountService(helper::bcsub($product_price, $bargain_price), $bargain_num);
        return $AmountService->handle()['items'];
    }

    /**
     * 当前是否为发起人
     */
    private function isCreater($task, $user)
    {
        if ($user === false) return false;
        return $user['user_id'] == $task['user_id'];
    }

    /**
     * 当前是否已砍
     */
    private function isCut($helpList, $user)
    {
        if ($user === false) return false;
        foreach ($helpList as $item) {
            if ($item['user_id'] == $user['user_id']) return true;
        }
        return false;
    }

    /**
     * 验证能否创建砍价任务
     */
    private function onVerify($active, $userId)
    {
        // 活动是否开始
        if ($active['start_time'] > time()) {
            $this->error = '很抱歉，当前砍价活动未开始';
            return false;
        }
        // 活动是否到期合法
        if ($active['end_time'] < time()) {
            $this->error = '很抱歉，当前砍价活动已结束';
            return false;
        }
        return true;
    }

}