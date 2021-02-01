<?php

namespace app\shop\model\order;

use app\common\model\order\Order as OrderModel;
use app\common\library\helper;
use app\common\enum\order\OrderTypeEnum;
use app\common\service\message\MessageService;
use app\common\service\order\OrderRefundService;
use app\common\enum\order\OrderPayStatusEnum;
use app\common\service\product\factory\ProductFactory;
use app\common\model\plus\coupon\UserCoupon as UserCouponModel;
use app\common\model\user\User as UserModel;
use app\common\enum\settings\DeliveryTypeEnum;
use app\shop\model\plus\logistics\Logistics as LogisticsModel;
use app\shop\service\order\ExportService;
use think\facade\Config;
use app\common\model\product\Product as ProductModel;
use app\common\model\product\Spec as SpecModel;
use app\common\model\product\SpecValue as SpecValueModel;
use app\common\model\product\ProductSpecRel as ProductSpecRelModel;
use app\common\model\order\OrderAddress as OrderAddressModel;
use app\common\model\order\OrderProduct as OrderProductModel;
use app\common\model\plus\agent\Setting as SettingModel;
use app\common\model\plus\anchor\Anchor as AnchorModel;
use app\common\model\plus\supply\Supply as SupplyModel;
use app\common\model\product\ProductSku as ProductSkuModel;
use app\api\model\plus\storage\Storage as StorageModel;
use app\common\model\settings\Setting as SetModel;
use app\common\model\file\UploadFile as UploadFileModel;
use app\common\model\plus\agent\PlanSettle;
use app\common\model\product\ProductImage as ProductImageModel;
use app\common\model\product\ProductSpecRel as ProductSpecRel;
use app\common\model\order\ImportLog as LogModel;
use think\facade\Db;

/**
 * 订单模型
 */
class Order extends OrderModel
{
    /**
     * 订单列表
     */
    public function getList($dataType, $data = null)
    {
        $model = $this;
        // 检索查询条件
        $model = $model->setWhere($model, $data);

        if(isset($data['logistics_id'])){
            $model = $model->where('logistics_id','in',$data['logistics_id']);

        }
        // 获取数据列表
        if (!empty(self::$supply_id)) {
            $model = $model->where('FIND_IN_SET(:supply_id,supply_ids)',['supply_id' => self::$supply_id]);
        }
        return $model->with(['product.image', 'user'])
            ->order(['create_time' => 'desc'])
            ->where($this->transferDataType($dataType))
            ->paginate($data, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 获取订单总数
     */
    public function getCount($type = 'all',$data=[])
    {   
        $model = $this;
        // 筛选条件
        $filter = [];
        // 订单数据类型
        switch ($type) {
            case 'all':
                break;
            case 'payment';
                $filter['pay_status'] = OrderPayStatusEnum::PENDING;
                $filter['order_status'] = 10;
                break;
            case 'delivery';
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 10;
                $filter['order_status'] = 10;
                break;
            case 'received';
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 20;
                $filter['receipt_status'] = 10;
                $filter['order_status'] = 10;
                break;

        }

        if(isset($data['logistics_id'])){
            $model = $model->where('logistics_id','in',$data['logistics_id']);
        }
        if (!empty(self::$supply_id)) {
            $model = $model->where('FIND_IN_SET(:supply_id,supply_ids)',['supply_id' => self::$supply_id]);
        }
        return $model->where($filter)->count();
    }

    /**
     * 订单列表(全部)
     */
    public function getListAll($dataType, $query = [])
    {
        $model = $this;
        // 检索查询条件
        $model = $model->setWhere($model, $query);
        if (!empty(self::$supply_id)) {
            $model = $model->where('FIND_IN_SET(:supply_id,supply_ids)',['supply_id' => self::$supply_id]);
        }
        // 获取数据列表
        return $model->with(['product.image', 'address', 'user', 'extract', 'extract_store'])
            ->alias('order')
            ->field('order.*')
            ->join('user', 'user.user_id = order.user_id')
            ->where($this->transferDataType($dataType))
            ->where('order.is_delete', '=', 0)
            ->order(['order.create_time' => 'desc'])
            ->select();
    }

    /**
     * 订单导出
     */
    public function exportList($dataType, $query)
    {
        // 获取订单列表
        $list = $this->getListAll($dataType, $query);
        // 导出excel文件
        return (new Exportservice)->orderList($list);
    }

    /**
     * 设置检索查询条件
     */
    private function setWhere($model, $data)
    {
        //搜索订单号
        if (isset($data['order_no']) && $data['order_no'] != '') {
            $model = $model->where('order_no', 'like', '%' . trim($data['order_no']) . '%');
        }
        //搜索自提门店
        if (isset($data['store_id']) && $data['store_id'] != '') {
            $model = $model->where('extract_store_id', '=', $data['store_id']);
        }
        //搜索配送方式
        if (isset($data['style_id']) && $data['style_id'] != '') {
            $model = $model->where('delivery_type', '=', $data['style_id']);
        }
        //搜索时间段
        if (isset($data['create_time']) && $data['create_time'] != '') {
            $sta_time = array_shift($data['create_time']);
            $end_time = array_pop($data['create_time']);
            $model = $model->whereBetweenTime('create_time', $sta_time, $end_time);
        }
        return $model;
    }

    /**
     * 转义数据类型条件
     */
    private function transferDataType($dataType)
    {
        $filter = [];
        // 订单数据类型
        switch ($dataType) {
            case 'all':
                break;
            case 'payment';
                $filter['pay_status'] = OrderPayStatusEnum::PENDING;
                $filter['order_status'] = 10;
                break;
            case 'delivery';
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 10;
                $filter['order_status'] = 10;
                break;
            case 'received';
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 20;
                $filter['receipt_status'] = 10;
                $filter['order_status'] = 10;
                break;
            case 'comment';
                $filter['is_comment'] = 0;
                $filter['order_status'] = 30;
                break;
            case 'six';
                // $filter['is_comment'] = 1;
                $filter['order_status'] = 30;
                break;
            case 'cancel';
                // $filter['is_comment'] = 1;
                $filter['order_status'] = 20;
                break;
            case 'canceling';
                // $filter['is_comment'] = 1;
                $filter['order_status'] = 21;
                break;
        }
        return $filter;
    }

    /**
     * 确认发货(单独订单)
     */
    public function delivery($data)
    {
        // 转义为订单列表
        $orderList = [$this];
        // 验证订单是否满足发货条件
        if (!$this->verifyDelivery($orderList)) {
            return false;
        }
        // 整理更新的数据
        $updateList = [[
            'order_id' => $this['order_id'],
            'express_id' => $data['express_id'],
            'express_no' => $data['express_no']
        ]];
        // 更新订单发货状态
        if ($status = $this->updateToDelivery($updateList)) {
            // 获取已发货的订单
            $completed = self::detail($this['order_id'], ['user', 'address', 'product', 'express']);
            // 发送消息通知
            $this->sendDeliveryMessage([$completed]);
        }
        return $status;
    }

    /**
     * 确认发货后发送消息通知
     */
    private function sendDeliveryMessage($orderList)
    {
        // 实例化消息通知服务类
        $Service = new MessageService;
        foreach ($orderList as $item) {
            // 发送消息通知
            $Service->delivery($item, OrderTypeEnum::MASTER);
        }
        return true;
    }

    /**
     * 更新订单发货状态(批量)
     */
    private function updateToDelivery($orderList)
    {
        $data = [];
        foreach ($orderList as $item) {
            $data[] = [
                'order_id' => $item['order_id'],
                'express_no' => $item['express_no'],
                'express_id' => $item['express_id'],
                'delivery_status' => 20,
                'delivery_time' => time(),
            ];
        }
        return $this->saveAll($data);
    }

    /**
     * 验证订单是否满足发货条件
     */
    private function verifyDelivery($orderList)
    {
        foreach ($orderList as $order) {
            if (
                $order['pay_status']['value'] != 20
                || $order['delivery_type']['value'] != DeliveryTypeEnum::EXPRESS
                || $order['delivery_status']['value'] != 10
            ) {
                $this->error = "订单号[{$order['order_no']}] 不满足发货条件!";
                return false;
            }
        }
        return true;
    }
    /**
     * 修改订单价格
     */
    public function updatePrice($data)
    {
        if ($this['pay_status']['value'] != 10) {
            $this->error = '该订单不合法';
            return false;
        }
        if ($this['order_source'] != 10) {
            $this->error = '该订单不合法';
            return false;
        }
        // 实际付款金额
        $payPrice = bcadd($data['update_price'], $data['update_express_price'], 2);
        if ($payPrice <= 0) {
            $this->error = '订单实付款价格不能为0.00元';
            return false;
        }
        return $this->save([
                'order_no' => $this->orderNo(), // 修改订单号, 否则微信支付提示重复
                'order_price' => $data['update_price'],
                'pay_price' => $payPrice,
                'update_price' => helper::bcsub($data['update_price'], helper::bcsub($this['total_price'], $this['coupon_money'])),
                'express_price' => $data['update_express_price']
            ]) !== false;
    }

    /**
     * 审核：用户取消订单
     */
    public function confirmCancel($data)
    {
        // 判断订单是否有效
        if ($this['pay_status']['value'] != 20) {
            $this->error = '该订单不合法';
            return false;
        }

        $order_info  = $this->detail($data['order_id']);
        // 订单取消事件
        $status = $this->transaction(function () use ($data) {
            if ($data['is_cancel'] == true) {
                // 执行退款操作
                (new OrderRefundService)->execute($this);
                // 回退商品库存
                ProductFactory::getFactory($this['order_source'])->backProductStock($this['product'], true);
                // 回退用户优惠券
                $this['coupon_id'] > 0 && UserCouponModel::setIsUse($this['coupon_id'], false);
                // 回退用户积分
                $user = UserModel::detail($this['user_id']);
                $describe = "订单取消：{$this['order_no']}";
                $this['points_num'] > 0 && $user->setIncPoints($this['points_num'], $describe);
                //回退订单业绩
                $order_info  = OrderModel::detail($data['order_id']);
                $user->setDecAchievement($user['user_id'],$order_info);
                //更新预计放放佣金状态
                $planSettleModel = new PlanSettle();
                $planSettleModel->updateStatus($data['order_id']);
            }
            // 更新订单状态
            return $this->save(['order_status' => $data['is_cancel'] ? 20 : 10]);
        });
        return $status;
    }

    /**
     * 获取已付款订单总数 (可指定某天)
     */
    public function getOrderData($startDate = null, $endDate = null, $type)
    {
        $model = $this;

        !is_null($startDate) && $model = $model->where('pay_time', '>=', strtotime($startDate));

        if(is_null($endDate)){
            !is_null($startDate) && $model = $model->where('pay_time', '<', strtotime($startDate) + 86400);
        }else{
            $model = $model->where('pay_time', '<', strtotime($endDate) + 86400);
        }

        $model = $model->where('is_delete', '=', 0)
            ->where('pay_status', '=', 20)
            ->where('order_status', '<>', 20);


        if($type == 'order_total'){
            // 订单数量
            return $model->count();
        }else if($type == 'order_total_price'){
            // 订单总金额
            return $model->sum('pay_price');
        }else if($type == 'order_user_total'){
            // 支付用户数
            return count($model->distinct(true)->column('user_id'));
        }
        return 0;
    }

    /**
     * 获取待处理订单
     */
    public function getReviewOrderTotal()
    {
        $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
        $filter['delivery_status'] = 10;
        $filter['order_status'] = 10;
        return $this->where($filter)->count();
    }

    /**
     * 获取某天的总销售额
     * 结束时间不传则查一天
     */
    public function getOrderTotalPrice($startDate = null, $endDate = null)
    {
        $model = $this;
        $model = $model->where('pay_time', '>=', strtotime($startDate));
        if (is_null($endDate)) {
            $model = $model->where('pay_time', '<', strtotime($startDate) + 86400);
        } else {
            $model = $model->where('pay_time', '<', strtotime($endDate) + 86400);
        }
        return $model->where('pay_status', '=', 20)
            ->where('order_status', '<>', 20)
            ->where('is_delete', '=', 0)
            ->sum('pay_price');
    }

    /**
     * 获取某天的客单价
     * 结束时间不传则查一天
     */
    public function getOrderPerPrice($startDate = null, $endDate = null)
    {
        $model = $this;
        $model = $model->where('pay_time', '>=', strtotime($startDate));
        if (is_null($endDate)) {
            $model = $model->where('pay_time', '<', strtotime($startDate) + 86400);
        } else {
            $model = $model->where('pay_time', '<', strtotime($endDate) + 86400);
        }
        return $model->where('pay_status', '=', 20)
            ->where('order_status', '<>', 20)
            ->where('is_delete', '=', 0)
            ->avg('pay_price');
    }

    /**
     * 获取某天的下单用户数
     */
    public function getPayOrderUserTotal($day)
    {
        $startTime = strtotime($day);
        $userIds = $this->distinct(true)
            ->where('pay_time', '>=', $startTime)
            ->where('pay_time', '<', $startTime + 86400)
            ->where('pay_status', '=', 20)
            ->where('is_delete', '=', 0)
            ->column('user_id');
        return count($userIds);
    }

    /**
     * 获取兑换记录
     * @param $param array
     * @return \think\Paginator
     */
    public function getExchange($param)
    {
        $model = $this;
        if (isset($param['order_status']) && $param['order_status'] > -1) {
            $model = $model->where('order.order_status', '=', $param['order_status']);
        }
        if (isset($param['nickName']) && !empty($param['nickName'])) {
            $model = $model->where('user.nickName', 'like', '%' . trim($param['nickName']) . '%');
        }

        return $model->with(['user'])->alias('order')
            ->join('user', 'user.user_id = order.user_id')
            ->where('order.order_source', '=', 20)
            ->where('order.is_delete', '=', 0)
            ->order(['order.create_time' => 'desc'])
            ->paginate($param, false, [
                'query' => request()->request()
            ]);


    }


    /**
     * 导入数据
     * @param $savename
     * @param $addens
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function putExcel($savename,$addens,$type='1'){
        set_time_limit(0);
        date_default_timezone_set('PRC');
        $upload_path = Config::get('filesystem.disks.public.root');
        $path = $upload_path."/".$savename;
        $bPage = 0;
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($path);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType); //实例化阅读器对象。
        $spreadsheet = $reader->load($path);  //将文件读取到到$spreadsheet对象中

        $currSheet = $spreadsheet->getSheet(0);
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

        $columnH = $currSheet->getHighestColumn(); // 取得总列数
        $rowCnt = $currSheet->getHighestRow();   //获取总行数

        $columnCnt = array_search($columnH, $cellName);

        $header = array();
        $data = array();

        for ($_row = 1; $_row <= 1; $_row++) {  //读取内容
            for ($_column = 0; $_column <= $columnCnt; $_column++) {
                $cellId = $cellName[$_column] . $_row;
                $cellValue = $currSheet->getCell($cellId)->getValue();
                if ($cellValue instanceof PHPExcel_RichText) {   //富文本转换字符串
                    $cellValue = $cellValue->__toString();
                }

                $header[$_row][$cellName[$_column]] = $cellValue;
            }
        }

        for ($_row = 0; $_row <= $rowCnt; $_row++) {  //读取内容
            for ($_column = 0; $_column <= $columnCnt; $_column++) {
                $cellId = $cellName[$_column] . $_row;
                $cellValue = $currSheet->getCell($cellId)->getValue();
                $cellValue = $currSheet->getCell($cellId)->getCalculatedValue();  #获取公式计算的值
                if ($cellValue instanceof PHPExcel_RichText) {   //富文本转换字符串
                    $cellValue = $cellValue->__toString();
                }

                $data[$_row][$cellName[$_column]] = $cellValue;
            }
        }

        if($data){
            return $res = $this->exportEquipmentData($data,$type);
        }else{
            return ['state'=>0,'msg'=>'导入数据为空'];
        }

    }

    // 处理导入的数据
    public function exportEquipmentData($datas,$type)
    {
        try {
            $log_mod = new LogModel();
            if ($datas) {
                /**二维数组去重,开始**/
                $rAr = array_unique($datas, SORT_REGULAR);

                $data = array_values($rAr);
                unset($data[0]);
                unset($data[1]);//表头

                $this->startTrans();
                /**二维数组去重,结束**/
                foreach ($data as $key => $value) {
                    $orderData['order_no'] = $value['A'];//订单编号
                    $orderData['goods_name'] = $value['B'];//商品名称
                    $orderData['goods_spec'] = $value['C'];//商品规格
                    // $orderData['goods_id'] = $value['D'];//第三方商品id
                    $orderData['product_no'] = $value['E'];//商品编码
                    $orderData['product_price'] = $value['F'];//商品单价
                    $orderData['total_num'] = $value['G'];//商品数量
                    $orderData['express_price'] = $value['H'];//运费
                    $orderData['coupon_money'] = $value['I'];//优惠金额
                    $orderData['pay_price'] = $value["N"];//实收款
                    $orderData['address_name'] = $value['Q'];//收件人
                    $orderData['address_phone'] = $value['R'];//收件人手机号
                    $orderData['address_detail'] = $value['S'];//收件地址
                    $orderData['user_name'] = $value['P'];//用户昵称
                    $orderData['province'] = $value['T'];//省
                    $orderData['city'] = $value['U'];//市
                    $orderData['region'] = $value['V'];//区
                    $orderData['buyer_remark'] = $value['X'];//用户留言
                    $orderData['express_company'] = $value['Y'];//快递公司
                    $orderData['express_no'] = $value['Z'];//快递单号
                    $orderData['create_time'] = $value['AA'];//订单提交时间
                    $orderData['receipt_time'] = $value['AC'];//完成时间
                    $orderData['supply_ids'] = $value['AH'];//供应商
                    $orderData['order_status'] = $value['AI'];//订单状态
                    $orderData['refund_money'] = $value['AP'];//退款金额
                    $orderData['refund_time'] = $value['AQ'];//退款申请时间

                    $orderArr[] = $orderData;
                }

                // 依据商订单编号称判断是更新还是新增操作
                if($orderArr){
                    foreach ($orderArr as $key => $value) {
                        $order_id = $this->where('order_no',$value['order_no'])->value('order_id');
                        if(empty($order_id)){
                            /* 添加*/
                            $result = $this->dealOrderLogic($value,1,$order_id,$type);

                        }else{
                            // 更新
                            // 导入过的订单不能重复导入
                            $log_mod->addLog(2,$value['order_no'],'导入失败：导入过的订单不能重复导入');
                            continue;
                            // $result = $this->dealOrderLogic($value,2,$order_id);
                        }
                    }
                }

                // if($result){
                    $this->commit();
                    return ['state'=>1,'msg'=>'导入成功'];
                // }else{
                //     $this->rollback();
                //     $returnData['state'] = 0;
                //     $returnData['msg'] = "导入失败";
                //     return $returnData;
                // }
                
            }
        } catch (Exception $exception) {
            $this->rollback();
            $returnData['state'] = 0;
            $returnData['msg'] = "添加数据异常;" . $exception->getMessage();
            return $returnData;
        }
    }

    // 处理订单逻辑
    public function dealOrderLogic($data = array(),$type = 1,$order_id = '',$act_type='1')
    {
        if(empty($data)){
            $log_mod->addLog(2,$data['order_no'],'导入失败：导入数据为空');
            return false;
        }

        $goods_mod = new ProductModel();
        $spec_mod = new SpecModel();
        $spec_val_mod = new SpecValueModel();
        $spec_rel_mod = new ProductSpecRelModel();
        // $region_mod = new RegionModel();
        $order_addr_mod = new OrderAddressModel();
        $order_goods_mod = new OrderProductModel();
        $setting_mod = new SettingModel();
        $user_mod = new UserModel();
        $supply_mod = new SupplyModel();
        $file_mod = new UploadFileModel();
        $img_mod = new ProductImageModel();
        $goods_sku_mod = new ProductSkuModel();
        $log_mod = new LogModel();

        $res = true;
        if($type == 1 && empty($order_id)){
            /* 新增*/

            // 组装订单数据
            if(!empty($data['user_name'])){
                $order_data['user_id'] = $user_mod->where(['nickName'=>$data['user_name'],'app_id'=>self::$app_id,'is_delete'=>0])->value('user_id');//根据用户昵称查询user_id
                if(empty($order_data['user_id'])){
                    $order_data['user_id'] = 0;
                }
            }else{
                $order_data['user_id'] = 0;
            }
            
            $order_data['order_no'] = $data['order_no'];
            $order_data['express_price'] = $data['express_price'];
            $order_data['coupon_money'] = $data['coupon_money'];
            $order_data['pay_price'] = $data['pay_price'];
            $order_data['buyer_remark'] = empty($data['buyer_remark']) ? ' ':$data['buyer_remark'];
            $order_data['express_company'] = empty($data['express_company']) ? ' ' : $data['express_company'];
            $order_data['express_no'] = empty($data['express_no']) ? ' ' : $data['express_no'];
            $order_data['create_time'] = strtotime($data['create_time']);
            $order_data['receipt_time'] = strtotime($data['receipt_time']);
            $order_data['order_status'] = $this->makeOrderStatus($data['order_status']);
            $order_data['pay_status'] = 20;
            $order_data['delivery_status'] = 20;
            $order_data['receipt_status'] = 20;
            $order_data['app_id'] = self::$app_id;
            $order_data['create_time'] = time();
            $order_data['update_time'] = time();
            // 查询供应商
            if(!empty($data['supply_ids'])){
                $supply_id = $supply_mod->where('name',$data['supply_ids'])->where('app_id',self::$app_id)->value('supply_id');
                $order_data['supply_ids'] = !empty($supply_id) ? $supply_id : '';   
            }

            $order_id = $this->insertGetId($order_data);
            if($order_id){
                // 添加商品数据
                if(!empty($data['goods_name'])){

                    $goods_info = $goods_mod->where(['product_name'=>$data['goods_name'],'app_id'=>self::$app_id])->find();
                    if($goods_info){
                        // 存在的商品
                        $goods_id = $goods_info['product_id'];
                        // 订单商品 

                        if(isset($goods_id) && !empty($goods_id)){

                            // 查询商品规格
                            if(!empty($data['goods_spec'])){ 
                                $spec_arr = explode(':', $data['goods_spec']);
                                $spec_sku_info = $spec_rel_mod->findSpecInfo($goods_id,$spec_arr[0],$spec_arr[1]);
                                
                                if(empty($spec_sku_info)){
                                    $log_mod->addLog(2,$data['order_no'],'导入失败：商品规格不存在');
                                    return false;//规格不存在
                                }

                            }else{
                                // 没有设置规格，查询默认sku
                                $spec_sku_info = $goods_sku_mod->where('product_id',$goods_id)->find();
                            }
                            
                            $orderGoodsData['product_id'] = $goods_id;
                            $orderGoodsData['product_name'] = $data['goods_name'];
                            $orderGoodsData['spec_type'] = empty($data['goods_spec']) ? 10 : 20;
                            $orderGoodsData['product_price'] = $data['product_price'];
                            $orderGoodsData['product_no'] = empty($data['product_no']) ? ' ':$data['product_no'];
                            $orderGoodsData['coupon_money'] = $data['coupon_money'];
                            $orderGoodsData['total_num'] = $data['total_num'];
                            $orderGoodsData['total_price'] = $data['total_num']*$data['product_price'];
                            $orderGoodsData['order_id'] = $order_id;
                            $orderGoodsData['app_id'] = self::$app_id;
                            $orderGoodsData['content'] = ' ';
                            $orderGoodsData['supply_id'] = $goods_info['supply_id'];
                            $orderGoodsData['image_id']  = $img_mod->where('product_id',$goods_id)->value('image_id');
                            $orderGoodsData['product_sku_id'] = isset($spec_sku_info['product_sku_id']) ? $spec_sku_info['product_sku_id']:'';
                            $orderGoodsData['spec_sku_id'] = isset($spec_sku_info['spec_sku_id']) ? $spec_sku_info['spec_sku_id']:'';

                            $order_goods_res = $order_goods_mod->save($orderGoodsData);
                            $res = $order_goods_res ? true : false;

                            // 获取分销配置
                            $agent_config = $setting_mod->where(['key'=>'basic','app_id'=>self::$app_id])->value('values');
                            
                            if(!empty($agent_config)){
                                $config_str = json_decode($agent_config);
                                if(isset($config_str->dist_mod) && $config_str->dist_mod == 3 && $act_type == '2' && $order_data['order_status'] == 30){
                                    $this->comissionLogic($order_data['user_id'],$goods_id,$orderGoodsData['product_sku_id'],$order_data['order_no'],$goods_info['supply_id'],$data['total_num']);
                                }
                            }
                        }

                    }else{
                        // 新增商品
                        // $goods_data['product_name'] = $data['goods_name'];
                        // $goods_data['product_price'] = $data['product_price'];
                        // $goods_data['product_no'] = $data['product_no'];
                        // $goods_data['create_time'] = time();
                        // $goods_data['app_id'] = self::$app_id;
                        // $goods_data['spec_type'] = empty($data['goods_spec']) ? 10 : 20;

                        // $goods_data['content'] = ' '; 

                        // $goods_id = $goods_mod->insertGetId($goods_data);

                        // if($goods_id && !empty($data['goods_spec'])){
                        //     // 处理商品规格
                        //     $spec_arr = explode(':', $data['goods_spec']);
                        //     if(!empty($spec_arr)){
                        //         $spec_data['spec_name'] = $spec_arr[0];
                        //         $spec_data['app_id'] = self::$app_id;
                        //         $spec_data['create_time'] = time();

                        //         $spec_id = $spec_mod->where('spec_name',$spec_arr[0])->value('spec_id');

                        //         if(empty($spec_id)){
                        //             $spec_id = $spec_mod->insertGetId($spec_data);
                        //         }

                        //         if($spec_id && isset($spec_arr[1])){
                        //             $specArr = explode(';', $spec_arr[1]);

                        //             if(!empty($specArr)){

                        //                 for ($i=0; $i < count($specArr); $i++) { 
                        //                     $specData['spec_value'] = $specArr[$i]; 
                        //                     $specData['spec_id'] = $spec_id; 

                        //                     $specData['app_id'] = self::$app_id;
                        //                     $specData['create_time'] = time();

                        //                     $spec_val_id = $spec_val_mod->where(['spec_value'=>$specArr[$i],'spec_id'=>$spec_id])->value('spec_value_id');

                        //                     if(empty($spec_value_id)){
                        //                         $spec_val_id = $spec_val_mod->insertGetId($specData);
                        //                     }

                        //                     if($spec_val_id){
                        //                         $goods_spec['product_id'] = $goods_id;
                        //                         $goods_spec['spec_id'] = $spec_id;
                        //                         $goods_spec['spec_value_id'] = $spec_val_id;
                        //                         $goods_spec['app_id'] = self::$app_id;

                        //                         $rel_res = $spec_rel_mod->insert($goods_spec);

                        //                         $res = $rel_res ? true : false;
                        //                     }else{
                        //                         $res = false;
                        //                     }
                        //                 }

                        //             }   

                        //         }else{
                        //             // $res = false;
                        //         }
                        //     }
                        // }




                        // 订单商品不存在，订单导入失败
                        $log_mod->addLog(2,$data['order_no'],'导入失败：订单商品不存在，订单导入失败');
                        return false;

                    }

                }

                /*订单地址*/
                // 查询省市区id
                $province_id = Db::table('kmdshop_region')->where(['name'=>$data['province'],'level'=>1])->value('id');
                $address_data['province_id'] = empty($province_id) ? 0 : $province_id;

                $city_id = Db::table('kmdshop_region')->where(['name'=>$data['city'],'level'=>2])->value('id');
                $address_data['city_id'] = empty($city_id) ? 0 : $city_id;

                $region_id = Db::table('kmdshop_region')->where(['name'=>$data['region'],'level'=>3])->value('id');
                $address_data['region_id'] = empty($region_id) ? 0 : $region_id;

                // $address_data['province_id'] = $region_mod->where(['name'=>$data['province'],'level'=>1])->value('id');
                // $address_data['city_id'] = $region_mod->where(['name'=>$data['city'],'level'=>2])->value('id');
                // $address_data['region_id'] = $region_mod->where(['name'=>$data['region'],'level'=>3])->value('id');
                $address_data['name'] = $data['address_name'];
                $address_data['phone'] = $data['address_phone'];
                $address_data['detail'] = $data['address_detail'];
                $address_data['order_id'] = $order_id;
                $address_data['app_id'] = self::$app_id;

                $order_addr_res = $order_addr_mod->save($address_data);
                $res = $order_addr_res ? true : false;
                // 退款 TODO
                // $return_data['order_id'] = $order_id;
                // $return_data['order_product_id'] = $order_id;
                // $return_data['type'] = 30;
                // $return_data['refund_money'] = $data['refund_money'];

            }else{
                $log_mod->addLog(2,$data['order_no'],'导入失败：订单导入失败');
                $res = false;
            }

            if(!$res){
                $log_mod->addLog(2,$data['order_no'],'导入失败：用户收货地址错误');
            }else{
                $log_mod->addLog(1,$data['order_no'],'导入成功：订单导入成功');
            }

            return $res;
        }else{
            // 修改
            return $res;
        }
    }

    // 处理订单状态
    public function makeOrderStatus($status_text = '')
    {
        $status = 10;
        if(!empty($status_text)){
            switch ($status_text) {
                case '已完成':
                    $status = 30;
                    break;
                case '已取消':
                    $status = 20;
                    break;
                default:
                    $status = 10;
                    break;
            }
        }

        return $status;
    }


    /**
     * 处理抖音分销逻辑
     * @Author   linpf
     * @DataTime 2020-10-21T10:55:25+0800
     * @param    string                   $user_id  [购买者/主播user_id]
     * @param    string                   $goods_id [商品id]
     * @param    string                   $order_sn [订单编号]
     * @param    string                   $supply_id [供应商id]
     * @param    string                   $buy_num [商品购买数量id]
     * @return   [type]                             [description]
     */

    public function comissionLogic($user_id = '',$goods_id = '',$spec_sku_id = '',$order_sn = '',$supply_id = '',$buy_num = 0)
    {
        $anchor_mod = new AnchorModel();
        $goods_mod = new ProductSkuModel();
        $supply_mod = new SupplyModel();
        $storage_mod = new StorageModel();
        $set_mod = new SetModel();
        $user_mod = new UserModel();
        $log_mod = new LogModel();

        //用户不存在不进行返利
        if(empty($user_id)){
             $log_mod->addLog(2,$order_sn,'结算失败：订单购买用户不存在',2);
            return false;
        }else{
            $is_anchor = $anchor_mod->where(['user_id'=>$user_id,'app_id'=>self::$app_id,'is_delete'=>0])->count();

             // 不是主播不进行返利
            if($is_anchor <= 0){
                $log_mod->addLog(2,$order_sn,'结算失败：该用户不是主播不进行返利',2);
                return false;
            }else{
                // 查询商品sku信息
                if(empty($goods_id)){
                    $log_mod->addLog(2,$order_sn,'结算失败：订单商品不存在不返利',2);
                    return false;//商品不存在不返利
                }else{
                    
                    $map['product_id'] = $goods_id; 
                    $map['app_id'] = self::$app_id;
                    $goods_info = $goods_mod->where($map)->find();
                    if($goods_info){
                        // 查询商品的供应商
                        if(!empty($supply_id)){
                            // 查询供应商
                            $supply_map['supply_id'] = $supply_id;

                            $supply_map['app_id'] = self::$app_id;
                            $supply_user_id = $supply_mod->where($supply_map)->value('user_id');
                            
                            if(!empty($supply_user_id)){
                                // 判断商品云仓库存
                                $set_info = $set_mod->where(['key'=>'depot','app_id'=>self::$app_id])->value('values');

                                if($set_info){
                                    $set_info = json_decode($set_info);
                                    if(isset($set_info->is_open_storage) && $set_info->is_open_storage){
                                        $storage_info = $storage_mod->where(['user_id' => $user_id, 'product_id' => $goods_id, 'product_sku_id' => $spec_sku_id])->where('number','>=',$buy_num)->find();

                                        if($storage_info){
                                            // 扣除余额
                                            // 供应商
                                            $dec_fee = $goods_info['product_price']-$goods_info['product_supply_price'];
                                            $supply_res = $user_mod->handleBalance($supply_user_id,2,$dec_fee,$order_sn);
                                            // 主播
                                            $anchor_res = $user_mod->handleBalance($user_id,1,$goods_info['product_price'],$order_sn);

                                            // 扣除库存
                                            $storage_res = $storage_mod->where('storage_id',$storage_info['storage_id'])->dec('number',$buy_num)->update();

                                            if($supply_res && $anchor_res && $storage_res){
                                                 $log_mod->addLog(1,$order_sn,'结算成功：主播获取佣金:'.$goods_info['product_price'].';供应商扣除余额:'.$dec_fee);
                                                return true;
                                            }else{
                                                $log_mod->addLog(2,$order_sn,'结算失败：导入失败',2);
                                                return false;
                                            }
                                        }else{
                                            $log_mod->addLog(2,$order_sn,'结算失败：商品不在云仓或者云仓配置未开或者商品云仓库存不足',2);
                                            return false;//商品不在云仓或者云仓配置未开或者商品云仓库存不足
                                        }

                                    }else{
                                        $log_mod->addLog(2,$order_sn,'结算失败：云仓功能已关闭',2);
                                        return false;//云仓设置关闭
                                    }
                                }else{
                                    $log_mod->addLog(2,$order_sn,'结算失败：云仓设置错误',2);
                                    return false;//没有云仓设置
                                }

                            }else{
                                $log_mod->addLog(2,$order_sn,'结算失败：订单商品绑定的供应商为空',2);
                                return false;//供应商为空
                            }

                        }else{
                            $log_mod->addLog(2,$order_sn,'结算失败：订单商品绑定的供应商不存在',2);
                            return false;//没有绑定供应商
                        }

                    }else{
                        $log_mod->addLog(2,$order_sn,'结算失败：订单商品不存在或者已被删除',2);
                        return false;//商品不存在或者已被删除
                    }
                }
                
            }
        }

    }


}