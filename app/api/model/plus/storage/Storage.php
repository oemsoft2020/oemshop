<?php

namespace app\api\model\plus\storage;

use app\api\model\order\Order as OrderModel;
use app\api\model\plus\task\Task;
use app\common\exception\BaseException;
use app\common\model\plus\codebatch\Code;
use app\common\model\plus\storage\CodeProductInfo;
use app\common\model\plus\storage\Storage as StorageModel;
use app\common\model\plus\storage\StorageLog;
use app\common\model\product\Product;
use app\common\model\settings\Setting as SettingModel;
use app\api\model\user\User as UserModel;
use app\common\model\plus\storage\StorageLog as StorageLogModel;
use app\common\model\user\BalanceLog;
use app\common\service\qrcode\ProductService;
use app\common\service\qrcode\StorageService;

/**
 * 仓库模型
 */
class Storage extends StorageModel
{

    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'is_delete',
        'app_id',
        'update_time'
    ];


    /**
     * 获取仓库列表
     */
    public function getList($user_id, $params)
    {
        $model = $this;
        $model = $model->with(['image'])
            ->where('user_id', '=', $user_id)
            ->where('is_delete', '=', 0)
            ->where('number', '>', 0)
            ->order(['create_time' => 'desc'])
            ->paginate($params, false, [
                'query' => \request()->request()
            ]);
        return $model;
    }

    /**
     * 获取云仓商品分类的数量及类型
     * @param $user_id
     * @return mixed
     */
    public function getStorageCategoryNumber($user_id)
    {
        $model = $this;
        //生日酒
        $list['birthday']['number'] = $model->alias('storage')
            ->join('product', 'product.product_id = storage.product_id', 'left')
            ->where('storage.user_id', '=', $user_id)
            ->where('storage.is_delete', '=', 0)
            ->where('storage.number', '>', 0)
            ->where('product.type', '=', 'birthday')
            ->sum('storage.number');
        $list['birthday']['type'] = 'birthday';

        //典藏酒
        $list['book_reservation']['number'] = $model->alias('storage')
            ->join('product', 'product.product_id = storage.product_id', 'left')
            ->where('storage.user_id', '=', $user_id)
            ->where('storage.is_delete', '=', 0)
            ->where('storage.number', '>', 0)
            ->where('product.type', '=', 'book_reservation')
            ->sum('storage.number');
        $list['book_reservation']['type'] = 'book_reservation';
        //明星酒
        $list['star']['number'] = $model->alias('storage')
            ->join('product', 'product.product_id = storage.product_id', 'left')
            ->where('storage.user_id', '=', $user_id)
            ->where('storage.is_delete', '=', 0)
            ->where('storage.number', '>', 0)
            ->where('product.type', '=', 'star')
            ->sum('storage.number');
        $list['star']['type'] = 'star';
        //大师酒
        $list['great_master']['number'] = $model->alias('storage')
            ->join('product', 'product.product_id = storage.product_id', 'left')
            ->where('storage.user_id', '=', $user_id)
            ->where('storage.is_delete', '=', 0)
            ->where('storage.number', '>', 0)
            ->where('product.type', '=', 'great_master')
            ->sum('storage.number');
        $list['great_master']['type'] = 'great_master';
        return $list;
    }

    /**
     * 根据商品分类获取云仓列表
     * @param $user_id
     * @param $type
     * @param $params
     * @return mixed
     */
    public function getStorageListByType($user_id, $type, $params)
    {
        $model = $this;
        $model = $model->alias('storage')->with(['image'])
            ->join('product', 'product.product_id = storage.product_id', 'left')
            ->where('storage.user_id', '=', $user_id)
            ->where('storage.is_delete', '=', 0)
            ->where('storage.number', '>', 0)
            ->where('product.type', '=', $type)
            ->order(['storage.create_time' => 'desc'])
            ->paginate($params, false, [
                'query' => \request()->request()
            ]);
        return $model;
    }

    /**
     * 仓库详情
     */
    public static function getStorageDetail($user_id, $storage_id)
    {
        $model = new static();
        $storage = $model->where(['storage_id' => $storage_id, 'user_id' => $user_id])->with(['image'])
            ->find();
        if (empty($storage)) {
            throw new BaseException(['msg' => '产品不存在']);
        }
        $storage['dif_time'] = (time() - strtotime($storage['create_time'])) / 86400;
        $product = new Product();
        $product = $product->where('product_id', $storage['product_id'])->find();
        if (empty($product)) {
            throw new BaseException(['msg' => '产品不存在']);
        }
        $storage['type'] = $product['type'];
        return $storage;
    }

    /**
     * 创建仓库记录
     */
    public static function createStorage($order)
    {
        // 仓库模型
        $model = new self;
        foreach ($order['product'] as $product) {
            $productModel = new Product();
            $product_id = $product['product_id'];
            $product_detail = $productModel->where('product_id', $product_id)->find();
            if ($product_detail['code_product_open'] == 0) {//未开启批次的商品进入云仓
                $storageInfo = $model->where(['user_id' => $product['user_id'], 'product_id' => $product_id, 'product_sku_id' => $product['product_sku_id']])->find();
                if ($storageInfo) {
                    $model->incNumber($storageInfo['storage_id'], $product['total_num']);
                    $model->incTotalPrice($storageInfo['storage_id'], $product['total_pay_price']);
                    //添加云仓记录
                    $describe = "加入云仓：{$order['order_no']}";
                    $logModel = new StorageLog();
                    $logModel->addLog($storageInfo['storage_id'], '加入云仓', $product['user_id'], $product['total_num'], 1, 0, $product['app_id'], $describe);
                } else {
                    $storage_id = $model->add($product);
                    //添加云仓记录
                    $describe = "加入云仓：{$order['order_no']}";
                    $logModel = new StorageLog();
                    $logModel->addLog($storage_id, '加入云仓', $product['user_id'], $product['total_num'], 1, 0, $product['app_id'], $describe);
                }
            } elseif ($product_detail['code_product_open'] == 1) {
                $model->addList($product, $order['order_no']);
            }
        }
        return true;

    }


    /**
     * 增加数量
     * @param $storage_id
     * @param $totalNumber
     * @return mixed
     */
    public function incNumber($storage_id, $totalNumber)
    {
        return $this->where('storage_id', $storage_id)->inc('number', $totalNumber)->update();

    }

    /**
     * 添加用户支付总价格
     * @param $storage_id
     * @param $total_price
     * @return mixed
     */
    public function incTotalPrice($storage_id, $total_price)
    {
        return $this->where('storage_id', $storage_id)->inc('total_pay_price', $total_price)->update();

    }

    /**
     * 添加仓库记录
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        $data = [
            'code' => $data['product_no'],
            'product_name' => $data['product_name'],
            'product_id' => $data['product_id'],
            'image_id' => $data['image_id'],
            'spec_sku_id' => $data['spec_sku_id'],
            'product_sku_id' => $data['product_sku_id'],
            'product_attr' => $data['product_attr'],
            'product_price' => $data['product_price'],
            'total_pay_price' => $data['total_pay_price'],
            'supply_id' => $data['supply_id'],
            'source' => 10,
            'number' => $data['total_num'],
            'order_id' => $data['order_id'],
            'user_id' => $data['user_id'],
            'app_id' => $data['app_id'],
            'delivery_type' => 40,
            'create_time' => time(),
            'update_time' => time()
        ];
        $storage_id = $this->insertGetId($data);
        return $storage_id;
    }

    /**
     * 按编码添加多条记录
     * @param $data
     * @param $order_no
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function addList($data, $order_no)
    {
        $user_id = $data['user_id'];
        if ($data['gift_status'] == 1) {//预约购买
            $phone = $data['gift_phone'];
            $name = $data['gift_name'];
            $give_date = $data['date'];
            $today = date('Y-m-d');
            $userInfo = $this->getuserinfo_cz($phone);
            $codeIds = explode('_', $data['product_no']);
            foreach ($codeIds as $v) {
                $codeModel = new Code();
                $code_id = $v;
                $codeDetail = $codeModel->where('code_id', $code_id)->where('use_time', 1)->find();
                $describe = "赠送：{$order_no}";
                if ($userInfo) {
                    if ($give_date == $today) {
                        $describe = "赠送：{$order_no}";
                        $give_describe = "接收：{$order_no}";
                        $storage_id = $this->addStorage($codeDetail['code'], $code_id, $data, $userInfo['user_id'], 20);
                        $logModel = new StorageLog();
                        //赠送记录
                        $logModel->addLog($storage_id, '转赠', $user_id, 1, 1, $userInfo['user_id'], $data['app_id'], $describe, $phone, $name, $give_date);
                        //接收记录
                        $logModel->addLog($storage_id, '接收', $userInfo['user_id'], 1, 1, $user_id, $data['app_id'], $give_describe, $phone, $name, $give_date);
                    } else {
                        //添加云仓
                        $storage_id = $this->addStorage($codeDetail['code'], $code_id, $data, $data['user_id'], 10);
                        //减云仓库存
                        $this->where(['user_id' => $user_id, 'storage_id' => $storage_id])->dec('number', 1)->update();
                        $logModel = new StorageLog();
                        //添加赠送记录
                        $logModel->addLog($storage_id, '转赠', $user_id, 1, 0, 0, $data['app_id'], $describe, $phone, $name, $give_date);
                    }
                } else {
                    //添加云仓
                    $storage_id = $this->addStorage($codeDetail['code'], $code_id, $data, $data['user_id'], 10);
                    //减云仓库存
                    $this->where(['user_id' => $user_id, 'storage_id' => $storage_id])->dec('number', 1)->update();
                    $logModel = new StorageLog();
                    //添加赠送记录
                    $logModel->addLog($storage_id, '转赠', $user_id, 1, 0, 0, $data['app_id'], $describe, $phone, $name, $give_date);
                }

            }

        } else {//立即购买
            $codeIds = explode('_', $data['product_no']);
            $codeModel = new Code();
            $describe = "加入云仓：{$order_no}";
            foreach ($codeIds as $v) {
                $code_id = $v;
                $codeDetail = $codeModel->where('code_id', $code_id)->where('use_time', 1)->find();
                $storage_id = $this->addStorage($codeDetail['code'], $code_id, $data, $data['user_id'], 10);
                //添加云仓记录
                $logModel = new StorageLog();
                $logModel->addLog($storage_id, '加入云仓', $user_id, 1, 1, 0, $data['app_id'], $describe);
            }
        }

        return true;
    }

    public function addStorage($code, $code_id, $data, $user_id, $source)
    {
        $codeModel = new Code();
        $list = [
            'code' => $code,
            'product_name' => $data['product_name'],
            'product_id' => $data['product_id'],
            'image_id' => $data['image_id'],
            'spec_sku_id' => $data['spec_sku_id'],
            'product_sku_id' => $data['product_sku_id'],
            'product_attr' => $data['product_attr'],
            'product_price' => $data['product_price'],
            'total_pay_price' => $data['total_pay_price'],
            'source' => $source,
            'number' => 1,
            'order_id' => $data['order_id'],
            'user_id' => $user_id,
            'app_id' => $data['app_id'],
            'supply_id' => $data['supply_id'],
            'delivery_type' => 40,
            'create_time' => time(),
            'update_time' => time()
        ];
        $codeModel->setCodeUse($code_id);
        $storage_id = $this->insertGetId($list);
        return $storage_id;
    }

    /**
     * 倉庫詳情
     * @param $user_id
     * @param $product_id
     * @param $product_sku_id
     * @param $number
     * @return array|bool|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function detail($user_id, $product_id, $product_sku_id, $number)
    {
        $vars = SettingModel::getItem('deopt');
        if (empty($vars['is_open_storage'])) return false;
        $data = $this->where(['user_id' => $user_id, 'product_id' => $product_id, 'product_sku_id' => $product_sku_id])->where('number', '>', $number)->find();
        if (!$data) {
            return false;
        }
        return $data;
    }


    /**
     * 立即购买：获取云仓订单列表
     */
    public static function getOrderStorageByNow($params, $time = null)
    {
        // 商品详情
        $product = StorageModel::storagedetail($params['storage_id']);
        $productList = [$product->hidden(['update_time'])];
        return $productList;
    }

    /**
     * 云仓提货成功，number数量改变
     * @param $user_id
     * @param $storage_id
     * @param $number
     * @return bool
     */
    public function changeDeliveryType($user_id, $storage_id, $number)
    {

        $where = [
            'user_id' => $user_id,
            'storage_id' => $storage_id
        ];

        $res = $this->where($where)->dec('number', $number)->update();
        if ($res) {
            $taskModel = new Task();
            $taskModel->saveTaskLog(6, $user_id);
        }
        return $res;

    }

    /**
     *云仓转移 搜索要赠送的用户有没有在小程序注册
     * @param $receiveway
     * @param $param
     * @return bool
     */
    public function getuserinfo($receiveway, $param)
    {
        $userModel = new UserModel();
        if ($receiveway == 'userid') {
            $res = $userModel->where('user_id', '=', $param)->find();
        }
        if ($receiveway == 'phonenumber') {
            $res = $userModel->where('mobile', '=', $param)->find();
        }

        return $res;
    }

    /**
     *云仓转移 搜索要赠送的用户有没有在小程序注册
     * @param $receiveway
     * @param $param
     * @return bool
     */
    public function getuserinfo_cz($param)
    {
        $userModel = new UserModel();
        $res = $userModel->where('mobile', '=', $param)->find();
        return $res;
    }

    /**
     *云仓转移
     * @param $user
     * @param $params
     * @return bool
     */
    public function giving($user, $params)
    {
        $params = json_decode($params, true);
        $user_id = $user['user_id'];
        $userinfo = $this->getuserinfo($params['receiveway'], $params['phone']);
        $phonenumber = '';
        if ($params['receiveway'] == 'phonenumber') {
            $phonenumber = $params['phone'];
        }
        //如果要赠送的用户存在的情况
        if ($userinfo) {
            $storage = new StorageModel();
            $storage_data = $storage->where('storage_id', '=', $params['storage_id'])->find();
            if ($storage_data) {
                //新增一条酒窖赠送记录
                $data = [
                    'code' => $storage_data['code'],
                    'product_name' => $storage_data['product_name'],
                    'product_id' => $storage_data['product_id'],
                    'image_id' => $storage_data['image_id'],
                    'spec_sku_id' => $storage_data['spec_sku_id'],
                    'product_sku_id' => $storage_data['product_sku_id'],
                    'product_attr' => $storage_data['product_attr'],
                    'product_price' => $storage_data['product_price'],
                    'total_pay_price' => $storage_data['total_pay_price'],
                    'source' => 20,
                    'number' => $params['num'],
                    'order_id' => $storage_data['order_id'],
                    'user_id' => $userinfo['user_id'],
                    'app_id' => $storage_data['app_id'],
                    'delivery_type' => $storage_data['delivery_type'],
                    'create_time' => time(),
                    'update_time' => time()
                ];
                $new_storage = $storage->insertGetId($data);

                //减掉赠送者库存
                $where = [
                    'user_id' => $user_id,
                    'storage_id' => $params['storage_id']
                ];
                $reduce = $storage->where($where)->dec('number', $params['num'])->update();

                //新增一条赠送记录表
                $storageLog = new StorageLogModel();
                $giving_user = [
                    'storage_id' => $params['storage_id'],
                    'operation' => '转赠',
                    'user_id' => $user_id,
                    'number' => $params['num'],
                    'app_id' => $storage_data['app_id'],
                    'source_user' => $userinfo['user_id'],
                    'status' => 1,
                    'mobile' => $phonenumber,
                    'giving_time' => time()
                ];
                $givingUser = $storageLog->save($giving_user);

                //新增一条接收记录
                $receive_user = [
                    'storage_id' => $new_storage,
                    'operation' => '接收',
                    'user_id' => $userinfo['user_id'],
                    'number' => $params['num'],
                    'app_id' => $storage_data['app_id'],
                    'source_user' => $user_id,
                    'status' => 1,
                    'mobile' => $phonenumber,
                    'giving_time' => time()
                ];
                $receiveUser = $storageLog::create($receive_user);

            }
            return 1;
        } //要赠送的用户不存在的情况
        else {
            if ($params['receiveway'] == 'userid') {
                return 0;
            } else {
                $storage = new StorageModel();
                //减掉赠送者库存
                $where = [
                    'user_id' => $user_id,
                    'storage_id' => $params['storage_id']
                ];
                $reduce = $storage->where($where)->dec('number', $params['num'])->update();


                //新增一条赠送记录表
                $storageLog = new StorageLogModel();
                $giving_user = [
                    'storage_id' => $params['storage_id'],
                    'operation' => '转赠',
                    'user_id' => $user_id,
                    'number' => $params['num'],
                    'app_id' => $user['app_id'],
                    'source_user' => '0',
                    'status' => 0,
                    'mobile' => $phonenumber,
                    'giving_time' => time()
                ];
                $givingUser = $storageLog->save($giving_user);
            }
            return 1;
        }
    }

    /**
     * 计算回购价格
     * @param $storageDetail
     * @param $user_id
     * @param $order_id
     * @return mixed
     * @throws BaseException
     */
    public function repo($storageDetail, $user_id, $order_id)
    {
        $orderDetail = OrderModel::getUserOrderDetail($order_id, $user_id);
        $num = $orderDetail->product[0]['total_num'];
        $pay_fee = sprintf("%.2f", $storageDetail['total_pay_price'] / $num);
        $storageDetail['pay_fee'] = $pay_fee;
        $storageDetail['buy_time'] = date("Y-m-d", strtotime($storageDetail['create_time']));
        //计算当前时间与购买时间的时间差
        $year = time() - strtotime($storageDetail['create_time']);
        $y = intval($year / (86400 * 365));
        $storageDetail['repo_fee'] = 0;
        if ($y >= 5) {
            $storageDetail['repo_fee'] = sprintf("%.2f", ($pay_fee * 112.5) / 100);
        } else {
            $storageDetail['repo_fee'] = sprintf("%.2f", ($pay_fee * 95) / 100);
        }
        return $storageDetail;
    }

    /**
     * 回购
     * @param $storageDetail
     * @param $user_id
     * @param $repo_fee
     * @return bool
     * @throws BaseException
     */
    public function repo_balance($storageDetail, $user_id, $repo_fee)
    {
        $dif_time = (time() - strtotime($storageDetail['create_time'])) / 86400;
        if ($dif_time < 7) {
            throw new BaseException(['msg' => '购买后7天后方可回购']);
        }
        $codeProductInfo = new CodeProductInfo();
        $detail = $codeProductInfo->where('storage_id', $storageDetail['storage_id'])->find();
        if ($detail) {
            throw new BaseException(['msg' => '已录入定制信息的不可回购']);
        }
        if ($storageDetail['type'] == 'birthday' || $storageDetail['type'] == 'book_reservation') {
            $res = $this->updateBalance($storageDetail, $user_id, $repo_fee, '回购', 50);
            if ($res) {
                return true;
            } else {
                return false;
            }
        } else {
            throw new BaseException(['msg' => '不是生日酒或者大坛封藏酒不可回购']);
        }
    }

    /**
     * 退货
     * @param $storageDetail
     * @param $user_id
     * @param $sales_return_fee
     * @return bool
     * @throws BaseException
     */
    public function sales_return_balance($storageDetail, $user_id, $sales_return_fee)
    {
        $dif_time = (time() - strtotime($storageDetail['create_time'])) / 86400;
        if ($dif_time > 7) {
            throw new BaseException(['msg' => '超出7天不能退']);
        }
        $codeProductInfo = new CodeProductInfo();
        $detail = $codeProductInfo->where('storage_id', $storageDetail['storage_id'])->find();
        if ($detail) {
            throw new BaseException(['msg' => '已录入定制信息的不可退货']);
        }
        if ($storageDetail['source'] != '10') {
            throw new BaseException(['msg' => '不是购买的不退']);

        }
        if ($storageDetail['type'] == 'birthday' || $storageDetail['type'] == 'book_reservation') {
            $res = $this->updateBalance($storageDetail, $user_id, $sales_return_fee, '退货', 60);
            if ($res) {
                return true;
            } else {
                return false;
            }
        } else {
            throw new BaseException(['msg' => '不是生日酒或者大坛封藏酒不可回购']);
        }
    }

    /**
     * 退货回购增加记录
     * @param $storageDetail
     * @param $user_id
     * @param $fee
     * @param $operation
     * @param $scene
     * @return bool
     * @throws BaseException
     */
    public function updateBalance($storageDetail, $user_id, $fee, $operation, $scene)
    {
        $userModel = new UserModel();
        $res = $userModel->where('user_id', $user_id)->inc('balance', $fee)->update();
        if ($res) {
            $order = OrderModel::getUserOrderDetail($storageDetail['order_id'], $user_id);
            //添加云仓记录
            $describe = $operation . "：{$order['order_no']}";
            $logModel = new StorageLog();
            $logModel->addLog($storageDetail['storage_id'], $operation, $user_id, 1, 1, 0, self::$app_id, $describe);
            //添加余额记录
            $balanceLog = new BalanceLog();
            $balanceData = [
                'user_id' => $user_id,
                'money' => $fee,
            ];
            $balanceLog::add($scene, $balanceData, $describe);
            //减数量
            $this->where('storage_id', $storageDetail['storage_id'])->dec('number', 1)->update();
            //退编码
            $code = new Code();
            $code->where('code', $storageDetail['code'])->update(['use_time' => 0, 'is_use' => 0]);
            return true;
        }
        return false;
    }


    /**
     *云仓转移 陈酌
     * @param $user
     * @param $params
     * @return bool
     */
    public function czgiving($user, $params)
    {
        $params = json_decode($params, true);
        $user_id = $user['user_id'];
        $today = strtotime(date("Y-m-d"));
        $givingday = strtotime($params['days']);
        if ($givingday < $today) {
            return 2;

        }
        $userinfo = $this->getuserinfo_cz($params['phone']);

        //如果要赠送的用户手机号码存在的情况
        if ($userinfo) {
            $storage = new StorageModel();
            $storage_data = $storage->where('storage_id', '=', $params['storage_id'])->find();
            if ($storage_data) {
                //赠送时间为当天的情况
                if ($givingday == $today) {
                    //新增一条酒窖赠送记录
                    $data = [
                        'code' => $storage_data['code'],
                        'product_name' => $storage_data['product_name'],
                        'product_id' => $storage_data['product_id'],
                        'image_id' => $storage_data['image_id'],
                        'spec_sku_id' => $storage_data['spec_sku_id'],
                        'product_sku_id' => $storage_data['product_sku_id'],
                        'product_attr' => $storage_data['product_attr'],
                        'product_price' => $storage_data['product_price'],
                        'total_pay_price' => $storage_data['total_pay_price'],
                        'source' => 20,
                        'number' => $params['num'],
                        'order_id' => $storage_data['order_id'],
                        'user_id' => $userinfo['user_id'],
                        'app_id' => $storage_data['app_id'],
                        'delivery_type' => $storage_data['delivery_type'],
                        'create_time' => time(),
                        'update_time' => time()
                    ];
                    $new_storage = $storage->insertGetId($data);

                    //减掉赠送者库存
                    $where = [
                        'user_id' => $user_id,
                        'storage_id' => $params['storage_id']
                    ];
                    $reduce = $storage->where($where)->dec('number', $params['num'])->update();

                    //新增一条赠送记录表
                    $storageLog = new StorageLogModel();
                    $giving_user = [
                        'storage_id' => $params['storage_id'],
                        'operation' => '转赠',
                        'user_id' => $user_id,
                        'number' => $params['num'],
                        'app_id' => $storage_data['app_id'],
                        'source_user' => $userinfo['user_id'],
                        'status' => 1,
                        'mobile' => $params['phone'],
                        'name' => $params['name'],
                        'giving_time' => strtotime($params['days'])
                    ];
                    $givingUser = $storageLog->save($giving_user);

                    //新增一条接收记录
                    $receive_user = [
                        'storage_id' => $new_storage,
                        'operation' => '接收',
                        'user_id' => $userinfo['user_id'],
                        'number' => $params['num'],
                        'app_id' => $storage_data['app_id'],
                        'source_user' => $user_id,
                        'status' => 1,
                        'mobile' => $params['phone'],
                        'name' => $params['name'],
                        'giving_time' => strtotime($params['days'])
                    ];
                    $receiveUser = $storageLog::create($receive_user);
                    return 1;
                } else {
                    //赠送时间还没到的情况
                    $storage = new StorageModel();
                    //减掉赠送者库存
                    $where = [
                        'user_id' => $user_id,
                        'storage_id' => $params['storage_id']
                    ];
                    $reduce = $storage->where($where)->dec('number', $params['num'])->update();


                    //新增一条赠送记录表
                    $storageLog = new StorageLogModel();
                    $giving_user = [
                        'storage_id' => $params['storage_id'],
                        'operation' => '转赠',
                        'user_id' => $user_id,
                        'number' => $params['num'],
                        'app_id' => $user['app_id'],
                        'source_user' => '0',
                        'status' => 0,
                        'mobile' => $params['phone'],
                        'name' => $params['name'],
                        'giving_time' => strtotime($params['days'])

                    ];
                    $givingUser = $storageLog->save($giving_user);
                    return 1;
                }
            }
            $taskModel = new Task();
            $taskModel->saveTaskLog(3, $user_id);
        } //要赠送的用户不存在的情况
        else {

            $storage = new StorageModel();
            //减掉赠送者库存
            $where = [
                'user_id' => $user_id,
                'storage_id' => $params['storage_id']
            ];
            $reduce = $storage->where($where)->dec('number', $params['num'])->update();


            //新增一条赠送记录表
            $storageLog = new StorageLogModel();
            $giving_user = [
                'storage_id' => $params['storage_id'],
                'operation' => '转赠',
                'user_id' => $user_id,
                'number' => $params['num'],
                'app_id' => $user['app_id'],
                'source_user' => '0',
                'status' => 0,
                'mobile' => $params['phone'],
                'name' => $params['name'],
                'giving_time' => strtotime($params['days'])

            ];
            $givingUser = $storageLog->save($giving_user);
            $taskModel = new Task();
            $taskModel->saveTaskLog(3, $user_id);
            return 1;
        }
    }

    //定时任务 处理未注册用户赠送和赠送时间已经大于当前时间
    public function giving_log($appid)
    {
        $user = new UserModel();
        $param = [
            'app_id' => $appid,
            'is_delete' => 0
        ];

        $storageLog = new StorageLogModel();
        $time = time();
        $where = [
            'app_id' => $appid,
            'status' => 0,
        ];
        $storage_log_data = $storageLog->where($where)->where('giving_time', '<', $time)->select();
        if ($storage_log_data) {
            foreach ($storage_log_data as $k => $v) {
                $userinfo = $user->where($param)->where('mobile', '=', $v['moblie'])->find();
                if ($userinfo) {
                    $storage = new StorageModel();
                    $storagedata = $storage->where('storage_id', '=', $v['storage_id'])->find();
                    //新增一条酒窖赠送记录
                    $data = [
                        'code' => $storagedata['code'],
                        'product_name' => $storagedata['product_name'],
                        'product_id' => $storagedata['product_id'],
                        'image_id' => $storagedata['image_id'],
                        'spec_sku_id' => $storagedata['spec_sku_id'],
                        'product_sku_id' => $storagedata['product_sku_id'],
                        'product_attr' => $storagedata['product_attr'],
                        'product_price' => $storagedata['product_price'],
                        'total_pay_price' => $storagedata['total_pay_price'],
                        'source' => 20,
                        'number' => $v['number'],
                        'order_id' => $storagedata['order_id'],
                        'user_id' => $userinfo['user_id'],
                        'app_id' => $storagedata['app_id'],
                        'delivery_type' => $storagedata['delivery_type'],
                        'create_time' => time(),
                        'update_time' => time()
                    ];
                    $new_storage = $storage->insertGetId($data);

                    //新增一条接收记录
                    $receive_user = [
                        'storage_id' => $new_storage,
                        'operation' => '接收',
                        'user_id' => $userinfo['user_id'],
                        'number' => $v['number'],
                        'app_id' => $storagedata['app_id'],
                        'source_user' => $v['user_id'],
                        'status' => 1,
                        'mobile' => $v['phone'],
                        'name' => $v['name'],
                        'giving_time' => $v['giving_time']
                    ];
                    $receiveUser = $storageLog::create($receive_user);

                    //更新storage_log表status字段改为1
                    $updateData = [
                        'status' => 1,
                        'source_user' => $userinfo['user_id']
                    ];
                    $update = $storageLog->where('storage_log_id', '=', $v['storage_log_id'])->update($updateData);
                }
            }
        }

    }

    public function saveCode($params,$user)
    {
        $storage_id = $params['storage_id'];
        $storageModel = new Storage();
        $detail = $storageModel::storagedetail($storage_id);
        $Qrcode = new StorageService($detail, $params['code_template_id'],$user);
        $code_url = $Qrcode->getImage();
        if (!$code_url) {
            throw new BaseException(['msg' => '保存二维码失败']);
        }

        return $storageModel->where('storage_id', $storage_id)->update(['code_url' => $code_url,'desc'=>$params['desc'],'type'=>$params['type']]);

    }
}