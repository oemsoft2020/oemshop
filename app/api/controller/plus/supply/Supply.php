<?php

namespace app\api\controller\plus\supply;

use app\api\controller\Controller;
use app\api\model\plus\supply\Supply as SupplyModel;
use app\common\service\qrcode\QrcodeService;
use app\api\model\settings\Setting as SettingModel;
use app\api\model\plus\deposit\Record as OrderModel;
use app\common\model\plus\supply\Grade as GradeModel;
use app\api\model\plus\certification\Apply as ApplyModel;
use app\common\model\plus\ad\Ad as AdModel;

/**
 * 供应商控制器
 */
class Supply extends Controller
{
    // 供应商信息
    public function getInfo()
    {
        $model = new SupplyModel();

        $user_info = $this->getUser(true);
        $supply = $model->detail(['user_id'=>$user_info['user_id']]);
        return $this->renderSuccess('', compact('supply'));
    }
    // 供应商申请
    public function apply()
    {
        $user_info = $this->getUser(true);
        $setting = SettingModel::getItem('supply');
        $model = new GradeModel;
        $where = [
            'kmd_grade_id'=>$setting['grade']
        ];
        $list =  $model->where($where)
            ->select();
        foreach ($list as &$va) {
            $va['money'] = $va['setting']['charge_fee']+$va['setting']['deposit'];
        }
        $certification = ApplyModel::detail([
            'user_id' => $user_info['user_id']
        ]);
        $apply = [
            'is_open'=>$setting['is_open'],
            'is_apply'=>$setting['is_apply'],
            'can_apply'=>1,
            'has_apply'=>false,
            'need_mobile'=>empty($user_info['mobile'])?true:false,
        ];
        if (!empty($setting['need_certification'])) {
            if (empty($certification)) {
                $apply['can_apply'] = 0;
            }elseif ($certification['apply_status']['value']!=20&&$setting['need_certification']==2) {
                $apply['can_apply'] = 2;
            }
        }
        $supply= (new SupplyModel())->detail(['user_id'=>$user_info['user_id']]);
        if (!empty($supply['kmd_grade_id'])) {
            $apply['has_apply'] = true;
        }
        return $this->renderSuccess('', compact('list','setting','apply'));
    }
    /**
     * @param $id
     * 供应商申请
     * @return \think\response\Json
     */
    public function buy($id)
    {
        // 用户信息
        $user = $this->getUser();
        $supply = new SupplyModel();

        $supply_user = $supply->detail(['user_id'=>$user['user_id']]);
        if (!empty($supply_user)) {
             return $this->renderError('你已经是供应商用户了，不能再次申请');
        }
        $params = $this->request->param();
        $apply_info = [
                'user_name' => $user['mobile'],
                'name' => $params['name'],
                'password' => 123456,
                'user_id' =>$user['user_id'],
                'kmd_grade_id' =>$id,
                'introduce' =>$params['introduce'],
            ];
        // 升级订单
        $model = new OrderModel;
        // 创建订单
        if (!$model->createOrder($user, $id, $params,$apply_info)) {
            return $this->renderError($model->getError() ?: '订单创建失败');
        }
        // 构建支付请求
        $payment = OrderModel::onOrderPayment($user, $model, $params['pay_type'], $params['pay_source']);
        // 返回结算信息
        return $this->renderSuccess(['success' => '支付成功', 'error' => '订单未支付'], [
            'order_id' => $model['order_id'],   // 订单id
            'pay_type' => $params['pay_type'],  // 支付方式,仅支持微信
            'payment' => $payment,               // 微信支付参数
        ]);
    }
    /**
     * 供应商信息
     */
    public function detail($supply_id)
    {
        $model = new SupplyModel();

        $user_info = $this->getUser(false);
        if(empty($supply_id)){
            $supply_id = $model->getSupplyBySetting($user_info);
        }
        $model->supplyBrowseRecods($user_info,$supply_id);
   
        $supply = $model->detail($supply_id);
        return $this->renderSuccess('', compact('supply'));
    }

    /* 
    * 供应商二维码
    */

    public function qrcode($supply_id)
    {
        $params = $this->postData();
        if (empty($params['supply_id'])) {
            return  $this->renderError('不存在');
        }
        $source = $params['source'];
        $supply_model = new SupplyModel();
        $supply_info =  $supply_model->detail($params['supply_id']);


        $page = 'pages/supply/detail/detail';

        $scene = [
            'supply_id'=>$supply_info['supply_id']
        ];
        $Qrcode = new QrcodeService($supply_info,$source,$page,$scene);
        return $this->renderSuccess('', [
            // 二维码图片地址
            'qrcode' => $Qrcode->getImage(),
            'supply_name'=>$supply_info['name']
        ]);
    }

    /* 
    * 获取广告图
    */
    public function getAdImg($supply_id)
    {
        $adModel = new AdModel();
        $where = [
            'supply_id'=>$supply_id,
            'is_delete'=>0,
            'status'=>1
        ];
        $adList = $adModel->where($where)->select();
        $adinfo = [
            'supply_top'=>'',
            'supply_bottom'=>''
        ];
        foreach ($adList as $item) {
            if($item['position']=='supply-top'){
                $adinfo['supply_top'] = $item['image_url'];
            }
            if($item['position']=='supply-bottom'){
                $adinfo['supply_bottom'] = $item['image_url'];
            }
        }

        return $this->renderSuccess('success',compact('adinfo'));

    }

}