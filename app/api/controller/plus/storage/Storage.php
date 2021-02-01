<?php

namespace app\api\controller\plus\storage;

use app\api\controller\Controller;
use app\api\model\order\Order as OrderModel;
use app\api\model\plus\codetemplate\CodeTemplate as CodeTemplateModel;
use app\api\model\plus\storage\Storage as StorageModel;
use app\common\model\plus\codebatch\Code;
use app\common\model\plus\storage\StorageLog;
use app\common\model\settings\Setting as SettingModel;
use app\common\model\plus\storage\CodeProductInfo as InfoModel;

/**
 * 仓库控制器
 */
class Storage extends Controller
{


    /**
     * 仓库列表
     */
    public function index()
    {
        $user = $this->getUser();
        $model = new StorageModel;
        $vars = SettingModel::getItem('depot');
        $list = $model->getList($user['user_id'], $this->postData());
        return $this->renderSuccess('', compact('list', 'vars'));
    }

    /**
     *仓库详情
     */
    public function detail($storage_id)
    {
        $user = $this->getUser();
        $vars = SettingModel::getItem('depot');
        $detail = StorageModel::getStorageDetail($user['user_id'], $storage_id);
        $model = new CodeTemplateModel;
        $template = $model->getDefault();
        return $this->renderSuccess('', compact('detail', 'vars','template'));
    }

    /**
     * 用户签名
     * @return \think\response\Json
     * @throws \app\common\exception\BaseException
     */
    public function sign()
    {
        $param['sign'] = input('sign');
        $param['storage_id'] = input('storage_id');
        $user = $this->getUser();
        $param['user_id'] = $user['user_id'];
        $detail = StorageModel::getStorageDetail($user['user_id'], $param['storage_id']);

        $model = new InfoModel;
        $tag = $model->add($param);
        if ($tag) {
            return $this->renderSuccess('签名成功');
        }
        return $this->renderError('签名失败');
    }

    /**
     * 获取签名
     */
    public function getSign()
    {
        $param['storage_id'] = input('storage_id');
        $user = $this->getUser();
        $param['user_id'] = $user['user_id'];
        $res = (new InfoModel)->getInfo($user['user_id'], $param['storage_id']);

        return $this->renderSuccess('', $res);
    }

    /**
     * 获取模板列表
     */
    public function getTempleList()
    {

    }


    /**
     *酒窖提货
     */
    public function delivery($storage_id)
    {
        $user = $this->getUser();

        $delivery = StorageModel::getStorageDetail($user['user_id'], $storage_id);
        if ($delivery && $delivery['create_time'] > 0) {
            $delivery['buy_time'] = date("Y-m-d", strtotime($delivery['create_time']));
            //计算当前时间与购买时间的时间差
            $year = time() - strtotime($delivery['create_time']);

            //计算年份，向上取整
            $y = ceil($year / (86400 * 365));
            $delivery['hoard_fee'] = 0;
            if ($delivery['product_attr'] != '') {
                if (stripos($delivery['product_attr'], "500ML") !== false) {
                    if ($y > 1) {
                        $delivery['hoard_fee'] = 2 * ($y - 1);
                    } else {
                        $delivery['hoard_fee'] = 0;
                    }

                }
                if (stripos($delivery['product_attr'], "5L") !== false) {
                    if ($y > 1) {
                        $delivery['hoard_fee'] = 12 * ($y - 1);
                    } else {
                        $delivery['hoard_fee'] = 0;
                    }

                }
            }


        }
        $vars = SettingModel::getItem('depot');
        $delivery['address'] = $user['address_default'];
        $delivery['exist_address'] = $user['address_id'] > 0;
        return $this->renderSuccess('', compact('delivery', 'vars'));
    }

    public function test()
    {
        $detail = OrderModel::getUserOrderDetail('109', '1');
        $vars = SettingModel::getItem('depot');
        $model = new StorageModel;
        if (!empty($vars['is_open_storage'])) {
            var_dump($detail);
            var_dump($detail['delivery_type']);
            die;
            $res = $model->createStorage($detail);
            var_dump($detail);
        }


    }

    //云仓提货成功，number数量改变
    public function delivery_success($storage_id, $number)
    {
        $user = $this->getUser();
        $model = new StorageModel;
        $result = $model->changeDeliveryType($user['user_id'], $storage_id, $number);

        if ($result) {
            return $this->renderSuccess('申请提货成功');
        }
        return $this->renderError('申请提货失败');

    }


    //云仓转移 搜索要赠送的用户有没有在小程序注册
    public function searchUser($receiveway, $param)
    {
        $user = $this->getUser();
        if (isset($receiveway) && $receiveway == '') {
            return $this->renderError('请选择接收方式');
        }
        if (isset($param) && $param == '') {
            return $this->renderError('请填写会员id或者手机号码');
        }
        $model = new StorageModel;
        $userinfo = $model->getuserinfo($receiveway, $param);
        if ($userinfo) {
            return $this->renderSuccess('', compact('userinfo'));
        } else {
            return $this->renderError('未查询到该用户，是否继续赠送？');
        }


    }

    //云仓转移
    public function giving($params)
    {
        $user = $this->getUser();
        $model = new StorageModel;
        $result = $model->giving($user, $params);

        if ($result == 0) {
            return $this->renderError('赠送失败,请选择手机号码接收');

        }
        return $this->renderSuccess('赠送成功', $result);

        //return $this->renderSuccess('', compact('result'));
    }

    /**
     * 搜索生日酒编码
     * @return \think\response\Json
     * @throws \app\common\exception\BaseException
     */
    public function searchCode()
    {
        $this->getUser();
        $codeModel = new Code();
        $codeList = $codeModel->searchCode($this->getData());
        if ($codeList) {
            return $this->renderSuccess('', compact('codeList'));
        }
        return $this->renderError('编码不存在');
    }

    /**
     * 回购
     * @param $storage_id
     * @return \think\response\Json
     * @throws \app\common\exception\BaseException
     */
    public function repo($storage_id)
    {
        $user = $this->getUser();
        $storageModel = new StorageModel;
        $storageDetail = $storageModel::getStorageDetail($user['user_id'], $storage_id);
        if (!$storageDetail) {
            return $this->renderError('产品不存在');
        }
        $vars = SettingModel::getItem('depot');
        $storageDetail = $storageModel->repo($storageDetail, $user['user_id'], $storageDetail['order_id']);
        return $this->renderSuccess('', compact('storageDetail','vars'));
    }

    //回购退余额
    public function repo_balance($storage_id, $repo_fee)
    {
        $user = $this->getUser();
        $storageModel = new StorageModel;
        $storageDetail = $storageModel::getStorageDetail($user['user_id'], $storage_id);
        $result = $storageModel->repo_balance($storageDetail, $user['user_id'], $repo_fee);
        if ($result) {
            return $this->renderSuccess('', compact('repo_fee'));
        }
        return $this->renderError('回退失败');
    }

    /**
     * 退货
     * @param $storage_id
     * @return \think\response\Json
     * @throws \app\common\exception\BaseException
     */
    public function sales_return($storage_id)
    {
        $user = $this->getUser();
        $storageModel = new StorageModel;
        $storageDetail = $storageModel::getStorageDetail($user['user_id'], $storage_id);
        if (!$storageDetail) {
            return $this->renderError('产品不存在');
        }
        if ($storageDetail['source'] == '20') {
            return $this->renderError('被赠送的酒不可退');
        }
        $orderDetail = OrderModel::getUserOrderDetail($storageDetail['order_id'], $user['user_id']);
        $num = $orderDetail->product[0]['total_num'];
        $storageDetail['sales_return_fee'] = sprintf("%.2f", $storageDetail['total_pay_price'] / $num);
        $storageDetail['buy_time'] = date("Y-m-d", strtotime($storageDetail['create_time']));
        $vars = SettingModel::getItem('depot');
        return $this->renderSuccess('', compact('storageDetail','vars'));
    }

    /**
     * 退货退余额
     * @param $storage_id
     * @param $sales_return_fee
     * @return \think\response\Json
     *
     * @throws \app\common\exception\BaseException
     */
    public function sales_return_balance($storage_id, $sales_return_fee)
    {
        $user = $this->getUser();
        $storageModel = new StorageModel;
        $storageDetail = $storageModel::getStorageDetail($user['user_id'], $storage_id);
        $result = $storageModel->sales_return_balance($storageDetail, $user['user_id'], $sales_return_fee);
        if ($result) {
            return $this->renderSuccess('');
        }
        return $this->renderError('退货失败');
    }

    /**
     * 操作记录
     * @return \think\response\Json
     * @throws \app\common\exception\BaseException
     */
    public function recordList()
    {
        $user = $this->getUser();
        $model = new StorageLog();
        $list = $model->getList($user['user_id'], $this->postData());
        return $this->renderSuccess('', compact('list'));
    }

    //赠送成功，获取赠送者信息
    public function giving_success()
    {
        $user = $this->getUser();
        return $this->renderSuccess('', compact('user'));
    }

    //云仓转移 陈酌
    public function czgiving($params)
    {
        $user = $this->getUser();

        $model = new StorageModel;
        $result = $model->czgiving($user, $params);

        if ($result == 2) {
            return $this->renderError('赠送失败,赠送时间不能小于当前时间');

        }
        return $this->renderSuccess('赠送成功', $result);

        //return $this->renderSuccess('', compact('result'));
    }

    //定时任务 处理赠送记录
    public function giving_log()
    {
        $appid = $this->app_id;
        $model = new StorageModel;
        $result = $model->giving_log($appid);
        return $this->renderSuccess('执行成功', $result);
    }

    /**
     * 获取云仓商品分类的数量及类型
     * @return \think\response\Json
     * @throws \app\common\exception\BaseException
     */
    public function getStorageCategoryNumber()
    {
        $user = $this->getUser();
        $model = new StorageModel;
        $list = $model->getStorageCategoryNumber($user['user_id']);
        return $this->renderSuccess('', compact('list'));
    }

    /**
     * 根据商品分类获取云仓列表
     * @param $type
     * @return \think\response\Json
     * @throws \app\common\exception\BaseException
     */
    public function getStorageListByType($type)
    {
        $user = $this->getUser();
        $model = new StorageModel;
        $list = $model->getStorageListByType($user['user_id'], $type, $this->postData());
        return $this->renderSuccess('', compact('list'));
    }

    public function saveCode()
    {
        $user = $this->getUser();
        $model = new StorageModel;
        $result = $model->saveCode($this->postData(), $user);
        if (!$result) {
            return $this->renderError('保存失败');
        }
        return $this->renderSuccess('');
    }

}