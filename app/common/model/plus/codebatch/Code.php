<?php

namespace app\common\model\plus\codebatch;

use app\common\model\BaseModel;
use app\common\model\product\Product;

class Code extends BaseModel
{
    protected $pk = 'code_id';

    public function product()
    {
        return $this->belongsTo('app\common\model\product\Product', 'product_id');
    }

    /**
     * 编码回退到未使用状态
     * @param $productList
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function backCodeNumber($productList)
    {
        foreach ($productList as $product) {
            $productModel = new Product();
            $productDetail = $productModel->where('product_id', $product['product_id'])->find();
            if ($productDetail && $productDetail['code_product_open'] == 1) {
                $codeIds = explode('_', $product['product_no']);
                if (is_array($codeIds)) {
                    foreach ($codeIds as $v) {
                        $this->where('code_id', $v)->update(['use_time' => 0]);
                    }
                }
            }
        }
    }

    /**
     * 设置编码为占用状态
     * @param $code_id
     * @return Code|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setCode($code_id)
    {
        $codeDetail = $this->where('code_id', $code_id)->where('use_time', 0)->find();
        if ($codeDetail) {
            return $this->where('code_id', $code_id)->update(['use_time' => 1]);
        }
        return false;
    }

    /**
     * 设置编码为已使用
     * @param $code_id
     * @return Code|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setCodeUse($code_id)
    {
        $codeDetail = $this->where('code_id', $code_id)->where('use_time', 1)->find();
        if ($codeDetail) {
            return $this->where('code_id', $code_id)->update(['is_use' => 1]);
        }
        return false;
    }

    /**
     * 获取随机数量的CODE数据
     * @param $array
     * @param $number
     * @return array
     */
    public function getRandomCode($array, $number)
    {
        $codeIds = array_rand($array, $number);
        $list = array();
        if (is_array($codeIds)) {
            foreach ($codeIds as $v) {
                $list[$v] = $array[$v];
            }
        } else {
            $list[] = $array[$codeIds];
        }
        return array_merge($list);
    }

    /**
     * 搜索编码
     * @param $param
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function searchCode($param)
    {
        $codeList = $this->where('use_time', 0)
            ->where('is_use', 0)
            ->where('code_batch_id', $param['code_batch_id'])
            ->where('end_code', 'like', '%' . $param['code'] . '%')
            ->select()->toArray();
        if ($codeList) {
            return $codeList;
        }
        return false;
    }
}