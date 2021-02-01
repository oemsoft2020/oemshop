<?php
namespace app\api\controller\plus\agent;
use app\api\controller\Controller;
use app\api\model\order\OrderRefund as OrderRefundModel;
use app\api\model\user\User as userModel;


/**
 * 代理商售后处理控制器
 */
Class Processing extends Controller{

    /**
     * 代理商下线所有售后订单
     */
    public function list($state=-1){
        $model = new OrderRefundModel;
        $userModel = new userModel();
        $user = $this->getUser();
        $data1 = $this->postData();
        //$user['user_id'],分销商的id是他的用户id
        $list = $model->getAgentList($user['user_id'], $data1['state'], $this->postData());
//        $list = $model->getAgentList(3, $data1['state'], $this->postData());
        foreach ($list as $k =>$v){
           $data = $userModel->where('user_id',$v['user_id'])->field('nickname')->find();
            $list[$k]['nickname1'] = $data['nickname'];
        }
        return $this->renderSuccess('', compact('list'));
    }


    /**
     * 代理商通过用户的售后审核
     */
    public function changeStatus(){
        $data = $this->getData();
        $model = new OrderRefundModel;
        $res= $model->where('order_refund_id',$data['order_refund_id'])->update(['agent_status'=>$data['agent_status']]);
        return $res;

    }

    /**
     * 处理售后：下线的所有售后订单
     */
    public function saleApplication(){
        $model = new OrderRefundModel;
        $userModel = new userModel();
        $user = $this->getUser();
        $data = $this->postData();
        //$user['user_id'],分销商的id是他的用户id
//        $list = $model->getAllSaleApplication(3,$data['state'], $this->postData());
        $list = $model->getAllSaleApplication($user['user_id'],$data['state'], $this->postData());
        foreach ($list as $k =>$v){
            $data = $userModel->where('user_id',$v['user_id'])->field('nickname')->find();
            $list[$k]['nickname1'] = $data['nickname'];
        }
        return $this->renderSuccess('', compact('list'));
    }
}