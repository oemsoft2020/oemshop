<?php

namespace app\api\controller\product;

use app\api\controller\Controller;
use app\common\model\product\ProductCollect as ProductCollectModel;

/**
 * 商品收藏控制器
 */
class Collect extends Controller
{
  /**
   * 收藏列表
   */
  public function lists()
  {
    // 整理请求的参数
    $param = array_merge($this->postData(), [
      'product_status' => 10
    ]);
    $param = $this->postData();

    // 获取列表数据
    $model = new ProductCollectModel;
    $list = $model->getList($param, $this->getUser(false));
    return $this->renderSuccess('', compact('list'));
  }

  /**
   * 收藏产品
   * 加上产品id是为了当产品不存在的情况下自动创建
   */
  public function add($id = 0, $product_id = 0, $app_id = 0)
  {
    // 用户信息
    $user = $this->getUser(false);
    if (!$user) {
      return $this->renderError('抱歉，未登录');
    }
    $model = new ProductCollectModel();
    // 条件
    $where = ['user_id' => $user['user_id'], 'product_id' => $product_id];
    if ($id) $where['collect_id'] = $id;
    $rs = $model->field('collect_id, status')->where($where)->find();
    if (!$rs) {
      $where['app_id'] = $app_id;
      $model->save($where);
      $rs = ['collect_id' => $model->collect_id, 'status' => 1];
    } else {
      $rs['status'] = $status = $rs['status'] ? 0 : 1;
      $model->where(['collect_id' => $id])->update(['status' => $status, 'update_time' => time()]);
    }
    return $this->renderSuccess('更新成功', $rs);
  }
}
