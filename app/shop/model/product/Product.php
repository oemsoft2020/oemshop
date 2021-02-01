<?php

namespace app\shop\model\product;

use app\common\model\product\Product as ProductModel;
use app\shop\model\plus\assemble\AssembleSku;
use app\shop\model\product\Category as CategoryModel;
use app\common\model\product\Brand as BrandModel;
use app\common\model\product\Spec as SpecModel;
use app\common\model\product\SpecValue as SpecValueModel;
use app\common\model\product\ProductSpecRel as ProductSpecRelModel;
use app\common\model\product\ProductImage as ProductImageModel;
use app\common\model\file\UploadFile as UploadFileModel;
use app\common\model\product\ProductSku as ProductSkuModel;
use app\shop\model\settings\Setting as SettingModel;
use app\common\model\plus\supply\Supply as SupplyModel;
use app\common\model\settings\Delivery as DeliveryModel;
use app\shop\service\ProductService;
use think\Collection;
use think\facade\Config;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * 商品模型
 */
class Product extends ProductModel
{
    /**
     * 添加商品
     */
    public function add(array $data)
    {
        if (!isset($data['image']) || empty($data['image'])) {
            $this->error = '请上传商品图片';
            return false;
        }
        $data = $this->setLabelsProducts($data);
        $data['content'] = isset($data['content']) ? $data['content'] : '';
        $data['alone_grade_equity'] = isset($data['alone_grade_equity']) ? json_decode($data['alone_grade_equity'], true) : '';
        $data['app_id'] = $data['sku']['app_id'] = self::$app_id;
        $data['supply_id'] = isset($data['supply_id']) ? $data['supply_id'] : self::$supply_id;
        $data['commission_type'] = isset($data['commission_type']) ? json_encode($data['commission_type']) : '';
        $data['promotion_time'] = time();

        //预售时间
        if ($data['sale_time']) {
            $data['sale_time'] = strtotime($data['sale_time']);
        }
        //发货时间
        if ($data['delivery_time']) {
            $data['delivery_time'] = strtotime($data['delivery_time']);
        }

        /**
         * 判断是否开启上下架功能
         * by keven
         * 2020.9.30
         */
        if (isset($data['automatic_shelves']) && $data['automatic_shelves'] == 1) {
            $data['the_shelves_time'] = strtotime($data['shelves_time'][1]);
            $data['shelves_time'] = strtotime($data['shelves_time'][0]);

//            $times = time();
            //根据时间判断现在的上下架状态
            if ($data['shelves_time'] < time() && $data['the_shelves_time'] > time()) {
                $data['product_status'] = 10;
            } else {
                $data['product_status'] = 20;
            }
        }


        // 开启事务
        $this->startTrans();
        try {
            // 添加商品
            $this->save($data);
            // 商品规格
            $this->addProductSpec($data);
            // 商品图片
            $this->addProductImages($data['image']);
            $this->commit();
            // 新建es索引文档
            try {
                if ($this->isElasticsearchOpen()) {
                    $esData = [
                        $this->productToES($this)
                    ];
                    $this->createDocuments(new Collection($esData));
                }
            } catch (\Exception $e) {
            }
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 添加商品图片
     */
    private function addProductImages($images)
    {
        $this->image()->delete();
        $data = array_map(function ($images) {
            return [
                'image_id' => isset($images['file_id']) ? $images['file_id'] : $images['image_id'],
                'app_id' => self::$app_id
            ];
        }, $images);
        return $this->image()->saveAll($data);
    }

    /**
     * 编辑商品
     * $from_user 是否来自用户端
     */
    public function edit($data, $from_user = false)
    {
        if (!isset($data['image']) || empty($data['image'])) {
            $this->error = '请上传商品图片';
            return false;
        }
        if (!$from_user) {
            $data = $this->setLabelsProducts($data);
            $data['alone_grade_equity'] = isset($data['alone_grade_equity']) ? json_decode($data['alone_grade_equity'], true) : '';
            $data['commission_type'] = isset($data['commission_type']) ? json_encode($data['commission_type']) : '';
        }


        $data['spec_type'] = isset($data['spec_type']) ? $data['spec_type'] : $this['spec_type'];
        $data['content'] = isset($data['content']) ? $data['content'] : '';

        $data['app_id'] = $data['sku']['app_id'] = self::$app_id;
        $data['brand_id'] = isset($data['brand_id']) ? $data['brand_id'] : '';

        /**
         * 预售时间
         * by keven
         * 2020.12.02
         */

        $data['sale_time'] ?: strtotime($data['sale_time']);
        if ($data['delivery_time']) {
            $data['delivery_time'] = strtotime($data['delivery_time']);
        }
        /**
         * 判断是否开启上下架功能
         * by keven
         * 2020.9.30
         */
        if (isset($data['automatic_shelves']) && $data['automatic_shelves'] == 1) {
            $data['the_shelves_time'] = strtotime($data['shelves_time'][1]);
            $data['shelves_time'] = strtotime($data['shelves_time'][0]);

//            $times = time();
            //根据时间判断现在的上下架状态
            if ($data['shelves_time'] < time() && $data['the_shelves_time'] > time()) {
                $data['product_status'] = 10;
            } else {
                $data['product_status'] = 20;
            }
        }

        return $this->transaction(function () use ($data) {
            // 保存商品
            $this->save($data);
            // 商品规格
            $this->addProductSpec($data, true);
            // 商品图片
            $this->addProductImages($data['image']);
            // 更新es索引文档
            try {
                if ($this->isElasticsearchOpen()) {
                    $this->updateDocument($this);
                }
            } catch (\Exception $e) {
            }
            return true;
        });
    }

    /**
     * 关联商品和标签
     */
    private function setLabelsProducts($data)
    {
        // 关联商品
        $relation_product_ids = [];
        $data['relation_product'] = empty($data['relation_product']) ? [] : $data['relation_product'];
        $data['relation_label'] = empty($data['relation_label']) ? [] : $data['relation_label'];
        foreach ($data['relation_product'] as $p) {
            array_push($relation_product_ids, $p['product_id']);
        }
        unset($p);
        unset($data['relation_product']);
        $data['relation_product_ids'] = implode(',', $relation_product_ids);
        // 关联标签
        $kmd_label_ids = [];
        $kmd_label_names = '';
        foreach ($data['relation_label'] as $p) {
            array_push($kmd_label_ids, $p['kmd_label_id']);
            $kmd_label_names .= $p['name'];
        }
        unset($p);
        unset($data['relation_label']);
        $data['kmd_label_ids'] = implode(',', $kmd_label_ids);
        $product_vars = SettingModel::getItem('product');
        if (!empty($product_vars['label_in_title'])) {
            $data['product_name'] = $kmd_label_names . $data['product_name'];
        }
        $data['setting'] = json_encode($data['setting'], JSON_NUMERIC_CHECK);
        return $data;
    }

    /**
     * 添加商品规格
     */
    private function addProductSpec($data, $isUpdate = false)
    {
        // 更新模式: 先删除所有规格
        $model = new ProductSku;

        $isUpdate && $model->removeAll($this['product_id']);
        $stock = 0;//总库存
        $product_price = 0;//价格

        // 添加规格数据
        if ($data['spec_type'] == '10') {

            // 单规格
            $productSuk = $this->sku()->save($data['sku']);
            //修改拼团商品sku
            $this->updateAssembleProductSku($this['product_id'], [$productSuk['product_sku_id']]);

            $stock = $data['sku']['stock_num'];
            $product_price = $data['sku']['product_price'];
            $agent_max_price = isset($data['sku']['agent_max_price']) ? $data['sku']['agent_max_price'] : 0;
            $agent_min_price = isset($data['sku']['agent_min_price']) ? $data['sku']['agent_min_price'] : 0;
        } else if ($data['spec_type'] == '20') {
            // 添加商品与规格关系记录
            $model->addProductSpecRel($this['product_id'], $data['spec_many']['spec_attr']);
            // 添加商品sku
            $productSuk = $model->addSkuList($this['product_id'], $data['spec_many']['spec_list']);
            //拼团商品suk
            $assembleProduct = new \app\shop\model\plus\assemble\Product();
            $assemble = new AssembleSku;
            $assembleProductSuk = $assembleProduct->with('assembleSku')->where('product_id', $this['product_id'])->select();

            if (!$assembleProductSuk->isEmpty()) {

                foreach ($assembleProductSuk as &$assembleSuk) {

                    foreach ($assembleSuk['assembleSku'] as $key => &$suk) {
                        $suk['product_sku_id'] = $productSuk[$key][0];
                        $suk['product_price'] = $productSuk[$key][1];
                    }
                }
                $assemble->saveAll($assembleSuk['assembleSku']->toArray());
            }

            $product_price = $data['spec_many']['spec_list'][0]['spec_form']['product_price'];
            $agent_max_price = isset($data['spec_many']['spec_list'][0]['spec_form']['agent_max_price']) ? $data['spec_many']['spec_list'][0]['spec_form']['agent_max_price'] : 0;
            $agent_min_price = isset($data['spec_many']['spec_list'][0]['spec_form']['agent_min_price']) ? $data['spec_many']['spec_list'][0]['spec_form']['agent_min_price'] : 0;
            foreach ($data['spec_many']['spec_list'] as $item) {
                $stock += $item['spec_form']['stock_num'];
                if ($item['spec_form']['product_price'] < $product_price) {
                    $product_price = $item['spec_form']['product_price'];
                }
            }
        }
        $this->save([
            'product_stock' => $stock,
            'product_price' => $product_price,
            'agent_max_price' => $agent_max_price,
            'agent_min_price' => $agent_min_price,
        ]);
    }

    function updateAssembleProductSku(int $product_id, array $sku_list)
    {
        $assembleProductModel = new  \app\shop\model\plus\assemble\Product();
        $assembleProduct = $assembleProductModel->with('assembleSku')->where('product_id', $product_id)->select();
        if (!$assembleProduct->isEmpty()) {
            foreach ($assembleProduct as &$product) {
                foreach ($product->assembleSku as &$sku) {
                    $sku['product_sku_id'] = $sku_list[0];
                }
                $sku->save();
            }

        }

    }

    /**
     * 修改商品状态
     */
    public function setStatus($state)
    {
        return $this->allowField(true)->save(['product_status' => $state ? 10 : 20]) !== false;
    }

    /**
     * 软删除
     * 由于是软删除,把es库中的is_delete字段改成1
     */
    public function setDelete()
    {
        if (ProductService::checkSpecLocked($this, 'delete')) {
            $this->error = '当前商品正在参与其他活动，不允许删除';
            return false;
        }
        // 更新es索引文档
        try {
            if ($this->isElasticsearchOpen()) {
                $this->is_delete = 1;
                $this->updateDocument($this);
            }
        } catch (\Exception $e) {
        }
        return $this->save(['is_delete' => 1]);
    }

    /**
     * 获取当前商品总数
     */
    public function getProductTotal($where = [])
    {
        return $this->where('is_delete', '=', 0)->where($where)->count();
    }

    /**
     * 获取商品告急数量总数
     */
    public function getProductStockTotal()
    {

        return $this->where('is_delete', '=', 0)->where('product_stock', '<', 20)->count();
    }

    public function getProductId($search)
    {
        $res = $this->where('product_name', 'like', '%' . $search . '%')->select()->toArray();
        return array_column($res, 'product_id');
    }

    //商品批量上架
    public function batchShelvesProduct($product_ids)
    {
        if ($product_ids != '') {
            $product_ids = explode(',', $product_ids);
            $data = array();
            $data['product_status'] = 10;
            return $this->where('product_id', 'in', $product_ids)
                ->where('product_status', '<>', 10)
                ->save($data);
        } else {
            return false;
        }
    }

    //商品批量下架
    public function batchUnShelvesProduct($product_ids)
    {
        if ($product_ids != '') {
            $product_ids = explode(',', $product_ids);
            $data = array();
            $data['product_status'] = 20;
            return $this->where('product_id', 'in', $product_ids)
                ->where('product_status', '<>', 20)
                ->save($data);
        } else {
            return false;
        }
    }

    //商品批量修改库存
    public function batchStock($product_ids, $num = 0)
    {
        if ($product_ids != '') {
            $product_ids = explode(',', $product_ids);
            $data['product_stock'] = $num;

            return $this->where('product_id', 'in', $product_ids)
                ->update($data);
        } else {
            return false;
        }
    }

    //商品批量修改直推收益
    public function batchIncome($product_ids, $money = 0)
    {
        if ($product_ids != '') {
            $product_ids = explode(',', $product_ids);
            $data['direct_commission'] = $money;

            return $this->where('product_id', 'in', $product_ids)
                ->update($data);
        } else {
            return false;
        }
    }

    /**
     * 导入数据
     * @param $savename
     * @param $addens
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function putExcel($savename, $addens)
    {
        set_time_limit(0);
        date_default_timezone_set('PRC');
        $upload_path = Config::get('filesystem.disks.public.root');
        $path = $upload_path . "/" . $savename;
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

        if ($data) {
            return $res = $this->exportEquipmentData($data);
        } else {
            return ['state' => 0, 'msg' => '导入数据为空'];
        }

    }

    // 处理导入的数据
    public function exportEquipmentData($datas)
    {
        try {
            if ($datas) {
                $brand_obj = new BrandModel();
                $cate_mod = new CategoryModel();
                $supply_mod = new SupplyModel();

                /**二维数组去重,开始**/
                $rAr = array_unique($datas, SORT_REGULAR);

                $data = array_values($rAr);
                unset($data[0]);
                unset($data[1]);//表头
                unset($data[2]);//标题

                $this->startTrans();

                /**二维数组去重,结束**/
                foreach ($data as $key => $value) {
                    $goodsData['product_name'] = $value['A'];//商品名称
                    $goodsData['imgs'] = $value['B'];//商品封面图
                    $goodsData['brand_id'] = $brand_obj->findBrandInfo($value['C']);//所属品牌
                    // $goodsData['category_id'] = $value['D'];//所属分类
                    // $goodsData['category_id'] = $value['E'];//二级分类
                    $goodsData['category_id'] = $cate_mod->addCate($value['D'], $value['E']);
                    $goodsData['spec'] = $value['F'];//商品规格型号
                    $goodsData['content'] = $this->makeGoodsDetail($value['G']);//商品详情
                    $goodsData['product_price'] = $value['H'];//商品价格(单价)
                    $goodsData['product_supply_price'] = $value['I'];//商品供货价
                    $goodsData['line_price'] = $value["J"];//商品划线价
                    $goodsData['dist_rate'] = $value['K'];//佣金比例
                    $goodsData['link'] = $value['L'];//抖音小店商品链接
                    $goodsData['supply_id'] = $supply_mod->where('name', $value['M'])->value('supply_id');//供应商

                    $goodsArr[] = $goodsData;
                }

                // 依据商品名称判断是更新还是新增操作
                if ($goodsArr) {

                    foreach ($goodsArr as $key => $value) {
                        $goods_id = $this->where('product_name', $value['product_name'])->value('product_id');
                        if (empty($goods_id)) {
                            /* 添加*/
                            // 商品
                            $result = $this->dealGoodsLogic($value, 1);
                        } else {
                            // 更新
                            $result = $this->dealGoodsLogic($value, 2, $goods_id);
                        }
                    }
                }

                if ($result) {
                    $this->commit();
                    return ['state' => 1, 'msg' => '导入成功'];
                } else {
                    $this->rollback();
                    $returnData['state'] = 0;
                    $returnData['msg'] = "导入失败";
                    return $returnData;
                }

            }
        } catch (Exception $exception) {
            $this->rollback();
            $returnData['state'] = 0;
            $returnData['msg'] = "添加数据异常;" . $exception->getMessage();
            return $returnData;
        }
    }

    // 数组是否包含指定字符串，是返回键，否返回false
    public function array_serach_str($str, $array)
    {
        foreach ($array as $k => $v) {
            if (strstr($v, $str)) {
                return $k;
            }
        }
        return false;
    }

    // 组装商品详情
    public function makeGoodsDetail($info = '')
    {
        $str = '';
        if (!empty($info)) {
            $info_arr = explode(';', $info);
            for ($i = 0; $i < count($info_arr); $i++) {
                $str .= '<p><img src="' . $info_arr[$i] . '"></p>';
            }
        }

        return $str;
    }

    // 处理商品数据逻辑
    public function dealGoodsLogic($data = array(), $type = 1, $goods_id = '')
    {
        if (empty($data)) {
            return false;
        }

        $res = true;
        $sku_mod = new ProductSkuModel();
        $file_mod = new UploadFileModel();
        $img_mod = new ProductImageModel();
        $spec_mod = new SpecModel();
        $spec_val_mod = new SpecValueModel();
        $spec_rel_mod = new ProductSpecRelModel();
        $delivery_mod = new DeliveryModel();

        if ($type == 1 && empty($goods_id)) {
            // 添加
            // 商品
            $add_data['product_name'] = $data['product_name'];
            $add_data['product_price'] = $data['product_price'];
            $add_data['category_id'] = $data['category_id'];
            $add_data['content'] = $data['content'];
            $add_data['app_id'] = self::$app_id;
            $add_data['brand_id'] = $data['brand_id'];
            $add_data['link'] = $data['link'];
            $add_data['create_time'] = time();
            $add_data['spec_type'] = empty($data['spec']) ? 10 : 20;
            $add_data['supply_id'] = !empty($data['supply_id']) ? $data['supply_id'] : 0;
            // 获取默认运费模板
            $add_data['delivery_id'] = $delivery_mod->where('app_id', self::$app_id)->value('delivery_id');

            $goods_id = $this->insertGetId($add_data);

            if ($goods_id) {

                // 商品图片
                if (!empty($data['imgs'])) {
                    $img_arr = explode(';', $data['imgs']);
                    for ($i = 0; $i < count($img_arr); $i++) {
                        $img_name = basename($img_arr[$i]);
                        if (!empty($img_arr[$i])) {
                            $imgInfo = parse_url($img_arr[$i]);
                            $img_data['file_size'] = @file_get_contents($img_arr[$i]) ? strlen(file_get_contents($img_arr[$i])) : 0;
                            $img_data['storage'] = 'upload';
                            $img_data['file_url'] = $imgInfo['scheme'] . '://' . $imgInfo['host'];
                            $img_data['file_name'] = $imgInfo['path'];
                            $img_data['app_id'] = self::$app_id;
                            $img_data['real_name'] = $img_name;
                            $img_data['extension'] = explode('.', $img_name)[1];
                            $img_data['create_time'] = time();

                            // 判断图片是否已存在
                            $img_id = $file_mod->where('file_name', $imgInfo['path'])->value('file_id');

                            if (empty($img_id)) {
                                $img_id = $file_mod->insertGetId($img_data);
                            }

                            if ($img_id) {
                                $imgData['product_id'] = $goods_id;
                                $imgData['image_id'] = $img_id;
                                $imgData['app_id'] = self::$app_id;
                                $imgData['create_time'] = time();

                                $goodsImgData[] = $imgData;
                            }
                        }

                    }

                    if (isset($goodsImgData) && !empty($goodsImgData)) {
                        $img_res = $img_mod->insertAll($goodsImgData);

                        $res = $img_res ? true : false;
                    }
                }

                // 商品规格
                if (!empty($data['spec'])) {
                    $spec_arr = explode(':', $data['spec']);
                    if (!empty($spec_arr)) {
                        $spec_data['spec_name'] = $spec_arr[0];
                        $spec_data['app_id'] = self::$app_id;
                        $spec_data['create_time'] = time();

                        $spec_id = $spec_mod->insertGetId($spec_data);

                        if ($spec_id) {
                            $specArr = explode(';', $spec_arr[1]);
                            if (!empty($specArr)) {
                                for ($i = 0; $i < count($specArr); $i++) {
                                    $specData['spec_value'] = $specArr[$i];
                                    $specData['spec_id'] = $spec_id;
                                    $specData['app_id'] = self::$app_id;
                                    $specData['create_time'] = time();

                                    $spec_val_id = $spec_val_mod->insertGetId($specData);
                                    if ($spec_val_id) {
                                        // 处理划线价和供货价,sku
                                        if (!empty($data['line_price']) || !empty($data['product_supply_price'])) {
                                            $sku_data['product_id'] = $goods_id;
                                            $sku_data['line_price'] = $data['line_price'];
                                            $sku_data['product_supply_price'] = $data['product_supply_price'];
                                            $sku_data['app_id'] = self::$app_id;
                                            $sku_data['spec_sku_id'] = $spec_val_id;

                                            $sku_res = $sku_mod->insert($sku_data);
                                            if (!$sku_res) {
                                                $res = false;
                                            }
                                        }

                                        $goods_spec['product_id'] = $goods_id;
                                        $goods_spec['spec_id'] = $spec_id;
                                        $goods_spec['spec_value_id'] = $spec_val_id;
                                        $goods_spec['app_id'] = self::$app_id;

                                        $rel_res = $spec_rel_mod->insert($goods_spec);

                                        $res = $rel_res ? true : false;
                                    } else {
                                        $res = false;
                                    }
                                }
                            }
                        } else {
                            $res = false;
                        }
                    }
                }
            } else {
                $res = false;
            }

            return $res;
        } else {
            // 更新
            // 商品
            $edit_data['product_name'] = $data['product_name'];
            $edit_data['product_price'] = $data['product_price'];
            $edit_data['category_id'] = $data['category_id'];
            $edit_data['content'] = $data['content'];
            $edit_data['app_id'] = self::$app_id;
            $edit_data['brand_id'] = $data['brand_id'];
            $edit_data['link'] = $data['link'];
            $edit_data['spec_type'] = empty($data['spec']) ? 10 : 20;
            $edit_data['supply_id'] = !empty($data['supply_id']) ? $data['supply_id'] : 0;

            $this->where('product_id', $goods_id)->update($edit_data);

            if ($goods_id) {

                // 商品图片
                if (!empty($data['imgs'])) {
                    $img_arr = explode(';', $data['imgs']);
                    for ($i = 0; $i < count($img_arr); $i++) {
                        $img_name = basename($img_arr[$i]);
                        if (!empty($img_arr[$i])) {
                            $imgInfo = parse_url($img_arr[$i]);
                            $img_data['file_size'] = @file_get_contents($img_arr[$i]) ? strlen(file_get_contents($img_arr[$i])) : 0;
                            $img_data['storage'] = 'upload';
                            $img_data['file_url'] = $imgInfo['scheme'] . '://' . $imgInfo['host'];
                            $img_data['file_name'] = $imgInfo['path'];
                            $img_data['app_id'] = self::$app_id;
                            $img_data['real_name'] = $img_name;
                            $img_data['extension'] = explode('.', $img_name)[1];
                            $img_data['create_time'] = time();

                            // 判断图片是否已存在
                            $img_id = $file_mod->where('file_name', $imgInfo['path'])->value('file_id');

                            if (empty($img_id)) {
                                $img_id = $file_mod->insertGetId($img_data);
                            }

                            if ($img_id) {
                                $imgData['product_id'] = $goods_id;
                                $imgData['image_id'] = $img_id;
                                $imgData['app_id'] = self::$app_id;
                                $imgData['create_time'] = time();

                                $goodsImgData[] = $imgData;
                            }
                        }

                    }

                    if (isset($goodsImgData) && !empty($goodsImgData)) {
                        // 先删除后添加
                        $img_mod->where('product_id', $goods_id)->delete();

                        $img_res = $img_mod->insertAll($goodsImgData);

                        $res = $img_res ? true : false;
                    }
                }

                // 商品规格
                if (!empty($data['spec'])) {
                    $spec_arr = explode(':', $data['spec']);
                    if (!empty($spec_arr)) {
                        $spec_data['spec_name'] = $spec_arr[0];
                        $spec_data['app_id'] = self::$app_id;
                        $spec_data['create_time'] = time();

                        $spec_id = $spec_mod->where('spec_name', $spec_arr[0])->value('spec_id');

                        if (empty($spec_id)) {
                            $spec_id = $spec_mod->insertGetId($spec_data);
                        }

                        if ($spec_id) {
                            $specArr = explode(';', $spec_arr[1]);
                            $spec_rel_mod->where('product_id', $goods_id)->delete();//先删除后添加

                            // 先删除sku信息再添加
                            $sku_mod->where('product_id', $goods_id)->delete();

                            if (!empty($specArr)) {
                                for ($i = 0; $i < count($specArr); $i++) {
                                    $specData['spec_value'] = $specArr[$i];
                                    $specData['spec_id'] = $spec_id;
                                    $specData['app_id'] = self::$app_id;
                                    $specData['create_time'] = time();

                                    $spec_val_id = $spec_val_mod->where(['spec_value' => $specArr[$i], 'spec_id' => $spec_id])->value('spec_value_id');

                                    if (empty($spec_value_id)) {
                                        $spec_val_id = $spec_val_mod->insertGetId($specData);
                                        // 处理划线价和供货价,sku
                                        if (!empty($data['line_price']) || !empty($data['product_supply_price'])) {
                                            $sku_data['line_price'] = $data['line_price'];
                                            $sku_data['product_supply_price'] = $data['product_supply_price'];
                                            $sku_data['app_id'] = self::$app_id;
                                            $sku_data['spec_sku_id'] = $spec_val_id;

                                            $sku_res = $sku_mod->save($sku_data);
                                            if (!$sku_res) {
                                                $res = false;
                                            }
                                        }

                                    }

                                    if ($spec_val_id) {
                                        $goods_spec['product_id'] = $goods_id;
                                        $goods_spec['spec_id'] = $spec_id;
                                        $goods_spec['spec_value_id'] = $spec_val_id;
                                        $goods_spec['app_id'] = self::$app_id;

                                        $rel_res = $spec_rel_mod->insert($goods_spec);

                                        $res = $rel_res ? true : false;
                                    } else {
                                        $res = false;
                                    }
                                }
                            }
                        } else {
                            $res = false;
                        }
                    }
                }
            } else {
                $res = false;
            }

            return $res;
        }

    }

}
