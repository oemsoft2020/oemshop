<?php

namespace app\api\controller\plus\supply;

use app\api\controller\Controller;
use app\api\model\plus\assemble\Product as ProductModel;
use app\api\model\plus\assemble\Active as ActiveModel;
use app\common\service\product\BaseProductService;
use app\api\model\plus\assemble\Bill as BillModel;
use app\api\model\product\Product as Productm;
use app\api\model\plus\supply\Supply as SupplyModel;
use app\common\service\message\MessageService;
/**
 * 拼团控制器
 */
class Assemble extends Controller
{
    /**
     * 构造方法
     */
    public function initialize()
    {
        parent::initialize();
        $this->user = $this->getUser();   // 用户信息
        $this->supply = SupplyModel::detail(['user_id'=>$this->user['user_id']]);

    }
    /**
     * 拼团活动
     */
    public function active()
    {
        $model = new ActiveModel();
        if (empty($this->supply['supply_id'])) {
            return $this->renderError('无权限操作');
        }
        if ($this->request->isGet()) {
            $where = [
                'status'=>1,
                'is_delete'=>0,
                'supply_id'=>$this->supply['supply_id']
            ];
           $list = $model->with('file')
                        ->where($where)
                        ->order(['sort' => 'asc', 'create_time' => 'desc'])
                        ->select();
           return $this->renderSuccess('', compact('list'));
        }
        $data = $this->postData();
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);
        if ($data['end_time']<=$data['start_time']) {
          return $this->renderError('结束日期必须大于开始日期');
        }
        $data['supply_id'] = $this->supply['supply_id'];
        if ($model->save($data)) {
          return $this->renderSuccess('添加成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
       
    }
    /**
     * 修改拼团活动
     */
    public function editActive($assemble_activity_id)
    {
        if ($this->request->isGet()) {
           $detail = ActiveModel::detail($assemble_activity_id)->toArray();
           $detail['start_time'] = date('Y-m-d',$detail['start_time']);
           $detail['end_time'] = date('Y-m-d',$detail['end_time']);
           return $this->renderSuccess('', compact('detail'));
        }
        $data = $this->postData();
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);
        if ($data['end_time']<=$data['start_time']) {
          return $this->renderError('结束日期必须大于开始日期');
        }
        $data['supply_id'] = $this->supply['supply_id'];
        $model = new ActiveModel();
        if ($model->update($data)) {
          return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
       
    }
   /**
     * 删除活动
     */
    public function delete($assemble_activity_id)
    {
        // 活动会场详情
        $model = ActiveModel::detail($assemble_activity_id);
        if ($model->del()) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?: '删除失败');
    }

    /**
     * 拼团商品
     */
    public function product($assemble_activity_id)
    {
        $detail = ActiveModel::detailWithTrans($assemble_activity_id);
        $list = (new ProductModel())->getActivityList($assemble_activity_id);

        return $this->renderSuccess('', compact('detail','list'));
    }

    /**
     * 拼团商品详情
     */
    public function edit($assemble_product_id)
    {
         $model = new ProductModel();
        if ($this->request->isGet()) {
            //详情
            $detail = $model->getAssembleDetail($assemble_product_id);
            //规格
            $specData = BaseProductService::getSpecData($detail['product']);
            return $this->renderSuccess('', compact('detail', 'specData'));
        }
        $data = $this->postData();
        $data['product'] = json_decode($data['product_data'],true);
        unset($data['product_data']);
        if ($model->edit($data['product'])) {
             return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }
    /**
     * 删除拼团商品
     */
    public function del($assemble_product_id)
    {
        $model = new ProductModel();
        if ($model->del($assemble_product_id)) {
             return $this->renderSuccess('删除成功');
        }
    }
    /**
     * 添加拼团商品
     */
    public function detail($product_id,$assemble_activity_id)
    {
        if ($this->request->isGet()) {
            $activity = ActiveModel::detail($assemble_activity_id);
            $detail = Productm::detail($product_id);
            return $this->renderSuccess('',compact('detail','activity'));
        }
    } 
    /**
     * 添加拼团商品
     */
    public function add()
    {
        
        $data = $this->postData();
        $data['product'] = json_decode($data['product_data'],true);
        unset($data['product_data']);
        $activity = ActiveModel::detail($data['assemble_activity_id']);
        // var_dump($data);
        $model = new ProductModel();
        $activity_product = $model->where('assemble_activity_id','=',$data['assemble_activity_id'])
                                  ->where('product_id','=',$data['product_id'])
                                  ->find();
        if (!empty($activity_product)) {
          return $this->renderSuccess('请不要重复添加,请选择其他产品');
        }
        if ($model->add($activity,$data['product'])) {
            $Service = new MessageService;
            // 发送消息通知
            $Service->assemble($activity);
           return $this->renderSuccess('添加成功');
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

}