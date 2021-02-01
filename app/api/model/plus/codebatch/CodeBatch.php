<?php

namespace app\api\model\plus\codebatch;

use app\api\model\product\Product;
use app\common\exception\BaseException;
use app\common\model\plus\codebatch\Code;
use app\common\model\plus\codebatch\CodeBatch as CodeBatchModel;

/**
 * 好友圈模型
 */
class CodeBatch extends CodeBatchModel
{
    /**
     * 隐藏字段
     */
    protected $hidden = [
        'app_id',
    ];

    /**
     * 获取批次数据
     * @param $product_id
     * @param $productType
     * @param $time
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductBatch($product_id, $productType, $time = null)
    {
        if ($time && $productType == 'birthday') {
            $detail = $this->where('product_id', $product_id)->whereDay('create_time', $time)->find();
        } elseif (!$time && $productType == 'birthday') {
            $day = date('Y-m-d', time());
            $detail = $this->where('product_id', $product_id)->whereDay('create_time', $day)->find();
        } else {
            $detail = $this->where('product_id', $product_id)->find();
        }
        if (!$detail) {
            return null;
        }
        $codeModel = new Code();
        $data['list'] = $codeModel->where(['code_batch_id' => $detail['code_batch_id'], 'use_time' => 0, 'is_use' => 0])->limit(20)->select()->toArray();
        $data['info'] = $detail;
        return $data;
    }

    /**
     * 从商品批次订单商品编码
     * @param $productDetail
     * @param $total_num
     * @param null $codeIds
     * @param null $time
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductNoByCodeBatch($productDetail, $total_num, $codeIds = null, $time = null)
    {
        $codeModel = new Code();
        if ($codeIds) {
            $codeList = explode('_', $codeIds);
            foreach ($codeList as $v) {
                $res = $codeModel->setCode($v);
                if (!$res) continue;
            }
            $productNo = $codeIds;
        } else {
            $codeList = $this->getCodeList($productDetail['product_id'], $productDetail['type'], $total_num, $time);
            $codeIds = [];
            foreach ($codeList as $v) {
                $codeIds[] = $v['code_id'];
                $res = $codeModel->setCode($v['code_id']);
                if (!$res) continue;
            }
            $codeIds = implode('_', $codeIds);
            $productNo = $codeIds;
        }
        return $productNo;
    }

    /**
     * 获取指定数量的商品编码
     * @param $product_id
     * @param $productType
     * @param $totalNum
     * @param null $time
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCodeList($product_id, $productType, $totalNum, $time = null)
    {
        $codeModel = new Code();
        if ($productType == 'birthday') {
            $codeDataList = $this->getProductBatch($product_id, $productType, $time);
        } else {
            $codeDataList = $this->getProductBatch($product_id, $productType);
        }
        if (!$codeDataList) {
            throw new BaseException(['msg' => '编码批次未找到']);
        }
        return $codeModel->getRandomCode($codeDataList['list'], $totalNum);
    }

    /**
     * 根据传来的编码id获取编码列表
     * @param $code_ids
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCodeListByCodeIds($code_ids)
    {
        $codeModel = new Code();
        $codeIds = explode('_', $code_ids);
        $codeList = [];
        foreach ($codeIds as $v) {
            $codeList[] = $codeModel->where('code_id', $v)->where('use_time', 0)->find();
        }
        return $codeList;
    }

    public function getCodeListByCodes($codes)
    {
        $codeModel = new Code();
        $codes = explode('_', $codes);
        $codeList = [];
        foreach ($codes as $v) {
            $codeList[] = $codeModel->where('code', $v)->where('use_time', 0)->find();
        }
        return $codeList;
    }
}