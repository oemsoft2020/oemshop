<?php

namespace app\common\model\product;

use app\common\library\helper;
use app\common\model\app\App;
use app\common\model\BaseModel;
use app\common\model\product\Category as CategoryModel;
use app\common\model\plus\supply\Supply as SupplyModel;
use app\facade\ESHelper as ESHelper;
use app\shop\model\settings\Setting as SettingModel;
use think\Paginator;
use app\common\model\product\ProductSku as ProductSkuModel;
use app\common\model\settings\Region as RegionModel;

/**
 * 商品模型
 */
class Product extends BaseModel
{
    protected $name = 'product';
    protected $pk = 'product_id';
    protected $append = ['product_sales','labels','region'];


    private function loopProduct(int $page = 1, int $list_rows = 100){
        $list = $this->getAllLists([
            'page' => $page,
            'list_rows' => $list_rows,
        ]);
        $this->createDocuments($list);
        if ($list->count() > 0){
            $this->loopProduct(++$page);
        }
    }

    /**
     * 一键同步数据库数据到ES中
     */
    public function oneClickSync(){
        // ini_set("display_errors", "On");
        // 全部同步,首先需要删除索引库
        $this->destroyIndex();
        // 然后执行同步程序
        $this->loopProduct(1);
    }

    /**
     * 获取所有es文档数量
     */
    public function getEsDocumentNum(){
        $indexName = $this->getESIndexName();
        $count = 0;
        $params = [
            'index' => $indexName,
            'client' => [ 'ignore' => 404 ]
        ];
        $res = ESHelper::getCount($params);
        if (isset($res['count'])){
            $count = $res['count'];
        }
        return $count;
    }

    /**
     * 返回高亮的渲染规则
     * @return string[]
     */
    protected function highlightRule(){
        return [
            'pre_tags' => '<span style="color: red;">',
            'post_tags' => '</span>'
        ];
    }

    /**
     * 把es的
     * @param array $searchRes
     * @return array
     */
    protected function esSearchResToProductsHandle(array $searchRes){
        $res['total'] = $searchRes['hits']['total'];
        if ($searchRes['hits']['total']['value'] > 0){
            $hits = $searchRes['hits']['hits'];
            foreach ($hits as $hit) {
                $res['ids'][] = intval($hit['_source']['product_id']);
                $res['list'][$hit['_id']] = $hit['_source'];
                $res['list'][$hit['_id']]['highlight'] = $hit['highlight'];
            }
        }
        // $res['ids'] = array_reverse($res['ids']);
        return $res;
    }

    /**
     * @param string $keyword
     * @param bool $highlight
     * @param int $page
     * @param int $list_row
     * @return array
     */
    public function searchShopMode(string $keyword, bool $highlight = true, int $page = 0, int $list_row = 20){
        $indexName = $this->getESIndexName();
        $query = [
            'bool'=> [
                'must' => [
                    [
                        'match' => [
                            'product_name' => $keyword
                        ]
                    ],
                    [
                        'match' => [
                            'is_delete' => 0
                        ]
                    ]
                ]
            ]
        ];
        $params = [
            'index' => $indexName,
            'body' => [
                'query' => $query,
                'from' => $page,
                'size' => $list_row
            ]
        ];
        if ($highlight){
            $params['body']['highlight'] = [
                'fields' => [
                    'product_name' => new \stdClass()
                ]
            ];
            $params['body']['highlight'] = array_merge($params['body']['highlight'], $this->highlightRule());
        }
        return $params;
    }
    /**
     * 地区名称
     */
    public function getRegionAttr($value, $data)
    {
        return [
            'province' => RegionModel::getNameById($data['province_id']),
            'city' => RegionModel::getNameById($data['city_id']),
            'region' => $data['region_id'] == 0 ? '' : RegionModel::getNameById($data['region_id']),
        ];
    }
    /**
     * 使用ES搜索
     * @param string $keyword 搜索的关键词
     * @param bool $highlight 是否高亮关键词
     * @param int $page 分页参数,当前页
     * @param int $list_row 分页参数,每页条数
     * @return array
     */
    public function search(string $keyword, bool $highlight = true, int $page = 0, int $list_row = 20){
        $params = $this->searchShopMode($keyword, $highlight, $page, $list_row);
        $selectDocument = ESHelper::selectDocument($params);
        return $this->esSearchResToProductsHandle($selectDocument);
    }


    /**
     * 产品实例转es数据结构
     * @param Product $product
     * @return array
     */
    protected function productToES(Product $product){
        $res = [];
        if ($product instanceof Product){
            $res['product_id'] = (int) $product->product_id;
            $res['product_name'] = $product->product_name;
            $res['is_delete'] = (int) $product->is_delete;
        }
        return $res;
    }

    /**
     * 产品数据转es数据结构
     * @param array $product
     * @return array
     */
    protected function productArrToES(array $product){
        $res = [];
        if (isset($product['product_id'])){
            $res['product_id'] = (int) $product['product_id'];
            $res['product_name'] = $product['product_name'];
            $res['is_delete'] = (int) $product['is_delete'];
        }
        return $res;
    }

    /**
     * @param Product $product
     */
    public function updateDocument(Product $product){
        $indexName = $this->getESIndexName();
        $params = [
            'index' => $indexName,
            'id' => $product->product_id,
            'body' => [
                'doc' => $this->productToES($product)
            ]
        ];
        ESHelper::updateDocument($params);
    }

    /**
     * 删除文档
     * @param Product $product
     * @return mixed
     */
    public function destroyDocument(Product $product){
        return ESHelper::deleteDocument($this->getESIndexName(), $product->product_id);
    }

    /**
     * @param \think\Collection $data
     */
    public function createDocuments(\think\Collection $data){
        if (!empty($data)) {
            $this->createESIndex();
            $indexName = $this->getESIndexName();
            // 产品对象列表
            $params = [];
            foreach ($data as $datum) {
                // 拼接填充对象,插入ES
                $params['body'][] = [
                    'index' => [
                        '_index' => $indexName,
                        '_id' => $datum['product_id']
                    ]
                ];
                if (is_array($datum)){
                    $params['body'][] = $this->productArrToES($datum);
                }else{
                    $params['body'][] = $this->productToES($datum);
                }
            }
            return ESHelper::insertBatchDocument($params);
        }
    }

    /**
     * 保存一个文档
     * @param Product $product
     */
    public function createDocument(Product $product){
        $this->createESIndex();
        $indexName = $this->getESIndexName();
        $params = [
            'index' => $indexName,
            'id' => $product->product_id,
            'body' => $this->productToES($product)
        ];
        return ESHelper::insertDocument($params);
    }

    /**
     * 创建专门属于product的索引库
     * @return boolean
     */
    public function createESIndex(){
        $index = $this->getESIndexName();
        $flag = false;
        // 索引不存在,创建索引
        if (!ESHelper::isExistIndexByIndexName($index)){
            $params = [
                'index' => $index,
                'body'  => [
                    'mappings' => [
                        'properties' => [
                            'product_id' => [
                                'type' => 'integer'
                            ],
                            'product_name' => [
                                'type' => 'text',
                                'analyzer' => 'ik_max_word'
                            ],
                            'is_delete' => [
                                'type' => 'byte'
                            ],
                        ]
                    ]
                ]
            ];
            ESHelper::createIndex($params);
            $flag = ESHelper::isExistIndexByIndexName($index);
        }
        return $flag;
    }

    /**
     * 删除索引库
     * @return mixed
     */
    private function destroyIndex(){
        $flag = false;
        $indexName = $this->getESIndexName();
        if (ESHelper::isExistIndexByIndexName($indexName)){
            $deleteIndex = ESHelper::deleteIndex($indexName);
            $flag = true;
        }
        return $flag;
    }

    /**
     * 自动生成ES索引名称
     * @return string
     */
    public function getESIndexName(){
        return $this->name.'_'.App::$app_id;
    }

    /**
     * 获取ES type name 新版ES已经淘汰了type,所以基本上用不上
     * @return string
     */
    public function getESTypeName(){
        return $this->name;
    }

    /**
     * 计算显示销量 (初始销量 + 实际销量)
     */
    public function getProductSalesAttr($value, $data)
    {
        if(!empty($data)){
            if(isset($data['sales_initial']) && isset($data['sales_actual'])){
                return $data['sales_initial'] + $data['sales_actual'];
            }
        }
        
        return 0;
    }

    public function getLabelsAttr($value,$data)
    {
        $label = new Label();
        if(!empty($data) && isset($data['kmd_label_ids']) && is_array($data['kmd_label_ids'])){
            $data['labels'] = $label->where('kmd_label_id','in',$data['kmd_label_ids'])->field('name')->select();
            return $data['labels'];
        }else{
            return [];
        }
        
    }

    /**
     * 获取器：单独设置折扣的配置
     */
    public function getAloneGradeEquityAttr($json)
    {
        return json_decode($json, true);
    }

    /**
     * 修改器：单独设置折扣的配置
     */
    public function setAloneGradeEquityAttr($data)
    {
        return json_encode($data);
    }

    /**
     * 关联商品分类表
     */
    public function category()
    {
        return $this->belongsTo('app\\common\\model\\product\\Category');
    }
    /**
     * 关联供应商
     */
    public function supply()
    {
        return $this->belongsTo('app\\common\\model\\plus\\supply\\Supply');
    }
/**
     * 关联收藏表
     */
    public function collect()
    {
        return $this->belongsTo('app\\common\\model\\product\\ProductCollect', 'product_id', 'product_id');
    }
    /**
     * 关联商品规格表
     */
    public function sku()
    {   
        $res = $this->hasMany('ProductSku')->order(['product_sku_id' => 'asc']);
        return $res;
    }

    /**
     * 关联商品规格关系表
     */
    public function specRel()
    {
        return $this->belongsToMany('SpecValue', 'ProductSpecRel')->order(['id' => 'asc']);
    }

    /**
     * 关联商品图片表
     */
    public function image()
    {
        return $this->hasMany('app\\common\\model\\product\\ProductImage')->order(['id' => 'asc']);
    }

    /**
     * 关联运费模板表
     */
    public function delivery()
    {
        return $this->BelongsTo('app\\common\\model\\settings\\Delivery');
    }

    /**
     * 关联订单评价表
     */
    public function commentData()
    {
        return $this->hasMany('app\\common\\model\\product\\Comment', 'product_id', 'product_id');
    }

    /**
     * 计费方式
     */
    public function getProductStatusAttr($value)
    {
        $status = [10 => '上架', 20 => '下架', 30 => '草稿'];
        return ['text' => $status[$value], 'value' => $value];
    }

    /**
     * 获取商品列表
     */
    public function getList($param)
    {
        // 商品列表获取条件
        $params = array_merge([
            'type' => 'sell',         // 商品状态
            'category_id' => 0,     // 分类id
            'sortType' => 'all',    // 排序类型
            'sortPrice' => false,   // 价格排序 高低
            'list_rows' => 15,       // 每页数量
        ], $param);
        // 筛选条件
        $filter = [];
        $model = $this;
        if (!empty(self::$supply_id)) {
            $model = $model->where('supply_id', '=', self::$supply_id);
        }
        if (!empty($params['supply_id'])) {
            $model = $model->where('supply_id', '=', $params['supply_id']);
        }
        if (isset($params['owner'])) {
            if ($params['owner'] == 'supply') {
                $model = $model->where('supply_id', '>', 0)->where('supply_id', '=', $params['supply_id']);
            }
        }
        if ($params['category_id'] > 0) {
            $arr = Category::getSubCategoryId($params['category_id']);
            $model = $model->where('category_id', 'IN', $arr);
        }
        if (!empty($params['product_name'])) {
            $model = $model->where('product_name', 'like', '%' . trim($params['product_name']) . '%');
        }
        if (!empty($params['product_no'])) {
            $model = $model->whereRaw("product_no=:product_no or product_diy_no=:product_diy_no", ['product_no' => trim($params['product_no']),'product_diy_no' => trim($params['product_no'])]);
        }
        $search = [];
        if (!empty($params['search'])) {
            // 如果有关键词进来,判断是否开启了专业搜索,如果开启,调用专业搜索,返回搜索到的ids
            try {
                if ($this->iselasticsearchopen()){
                    $esPage = $params['page'] < 2 ? 0 : $params['page']-1;
                    $search = $this->search($params['search'], true, $esPage, $params['list_rows']);
                    if (!empty($search['ids'])){
                        $model = $model->where('product_id', 'in', $search['ids']);
                        $exp = "field(product_id,".implode(',', $search['ids']).")";
                        $model = $model->orderraw($exp);
                    }
                }else{
                    $model = $model->where('product_name', 'like', '%' . trim($params['search']) . '%');
                }
            } catch (\Exception $e) {
                $model = $model->where('product_name', 'like', '%' . trim($params['search']) . '%');
            }
        }
        // 排序规则
        $sort = [];
        if ($params['sortType'] === 'all') {
            $sort = ['product_sort', 'product_id' => 'desc'];
        } else if ($params['sortType'] === 'sales') {
            $sort = ['product_sales' => 'desc'];
        } else if ($params['sortType'] === 'price') {
            $sort = $params['sortPrice'] ? ['product_max_price' => 'desc'] : ['product_min_price'];
        }
        if (isset($params['type'])) {
            //出售中
            if ($params['type'] == 'sell') {
                $model = $model->where('product_status', '=', 10);
            }
            //库存紧张
            if ($params['type'] == 'stock') {
                $model = $model->whereBetween('product_stock', [1, 20]);
            }
            //已售罄
            if ($params['type'] == 'over') {
                $model = $model->where('product_stock', '=', 0);
            }
            //已下架
            if ($params['type'] == 'lower') {
                $model = $model->where('product_status', '=', 20);
            }
            //草稿
            if ($params['type'] == 'draft') {
                $model = $model->where('product_status', '=', 30);
            }
        }
        // 商品表名称
        $tableName = $this->getTable();
        // 多规格商品 最高价与最低价
        $ProductSku = new ProductSku;
        $minPriceSql = $ProductSku->field(['MIN(product_price)'])
            ->where('product_id', 'EXP', "= `$tableName`.`product_id`")->buildSql();
        $maxPriceSql = $ProductSku->field(['MAX(product_price)'])
            ->where('product_id', 'EXP', "= `$tableName`.`product_id`")->buildSql();
        // 执行查询

        $model = $model
            ->field(['*', '(sales_initial + sales_actual) as product_sales',
                "$minPriceSql AS product_min_price",
                "$maxPriceSql AS product_max_price"
            ])
            ->with(['category', 'image.file','supply'])
            ->where('is_delete', '=', 0)
            ->where($filter)
            ->order($sort)
            ->order(['promotion_time'=>'desc']);
        
        if ($search){
            $results = $model->select();
            foreach ($results as &$result) {
                if (isset($search['list'][$result->product_id]['highlight']['product_name'][0])){
                    $result['highlight'] = $search['list'][$result->product_id]['highlight']['product_name'][0];
                }
            }
            $list = Paginator::make($results, $params['list_rows'], $params['page'], $search['total']['value'], false, []);
        }else{
            $list = $model->paginate($params, false, [
                'query' => \request()->request(),
            ]);
        }

        // 整理列表数据并返回
        return $this->setProductListData($list, true);
    }

    /**
     * 获取商品列表
     */
    public function getLists($param)
    {
        // 商品列表获取条件
        $params = array_merge([
            'product_status' => 10,         // 商品状态
            'category_id' => 0,     // 分类id
        ], $param);
        // 筛选条件
        $model = $this;
        if (!empty(self::$supply_id)) {
            $model = $model->where('supply_id', '=', self::$supply_id);
        }
        if ($params['category_id'] > 0) {
            $arr = Category::getSubCategoryId($params['category_id']);
            $model = $model->where('category_id', 'IN', $arr);
        }
        if (!empty($params['product_name'])) {
            $model = $model->where('product_name', 'like', '%' . trim($params['product_name']) . '%');
        }
        if (!empty($params['search'])) {
            $model = $model->where('product_name', 'like', '%' . trim($params['search']) . '%');
        }
        $list = $model
            ->with(['category', 'image.file'])
            ->where('is_delete', '=', 0)
            ->where('product_status', '=', $params['product_status'])
            ->order(['promotion_time'=>'desc'])
            ->paginate($params, false, [
                'query' => \request()->request(),
            ]);
        // 整理列表数据并返回
        return $this->setProductListData($list, true);
    }

    /**
     * 获取商品列表 'product_id','product_name', 'selling_point', 'product_no','product_diy_no'
     * 改方法主要使为了elasticsearch使用
     * @param array $params
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAllLists(array $params)
    {
        // 筛选条件
        return $this
            ->field(['product_id','product_name', 'is_delete'])
            ->page($params['page'], $params['list_rows'])->select();
    }

    /*
     * 获取回收站商品列表
     */
    public function getRecycleGoods($params)
    {
        // 筛选条件
        $model = $this;
        if (!empty(self::$supply_id)) {
            $model = $model->where('supply_id', '=', self::$supply_id);
        }
        if ($params['category_id'] > 0) {
            $arr = Category::getSubCategoryId($params['category_id']);
            $model = $model->where('category_id', 'IN', $arr);
        }
        if (!empty($params['product_name'])) {
            $model = $model->where('product_name', 'like', '%' . trim($params['product_name']) . '%');
        }
        if (!empty($params['search'])) {
            $model = $model->where('product_name', 'like', '%' . trim($params['search']) . '%');
        }
        $list = $model
            ->with(['category', 'image.file'])
            ->where('is_delete', '=', 1)

            ->paginate($params, false, [
                'query' => \request()->request(),
            ]);
        // 整理列表数据并返回

        return $this->setProductListData($list, true);

    }

    /**
     * 设置商品展示的数据
     */
    protected function setProductListData($data, $isMultiple = true, callable $callback = null)
    {
        $sku_mod = new ProductSkuModel();

        if (!$isMultiple) $dataSource = [&$data]; else $dataSource = &$data;
        // dump($dataSource->toArray());die;
        // 整理商品列表数据
        foreach ($dataSource as &$product) {
            if($product['spec_type'] == 20){
                foreach ($product['sku'] as $key => $value) {
                    if(isset($value['spec_sku_id']) && !empty($value['spec_sku_id'])){
                        // var_dump($value['spec_sku_id']);
                        $product['product_sku'] = $value;
                        break;
                    }
                }

                //$product['product_sku'] = isset($product['product_sku']) ? $product['product_sku'] : $product['sku'][0];
            }else{
                // 商品默认规格
                $product['product_sku'] = isset($product['sku'][0]) ? $product['sku'][0] : '';
            }

            // 商品主图
            $product['product_image'] = isset($product['image'][0]) ? $product['image'][0]['file_path'] : '';
            
            // 回调函数
            is_callable($callback) && call_user_func($callback, $product);
        }

       
        return $data;
    }

    /**
     * 根据商品id集获取商品列表
     */
    public function getListByIds($productIds, $status = null)
    {
        $model = $this;
        $filter = [];
        // 筛选条件
        $status > 0 && $filter['product_status'] = $status;
        if (!empty($productIds)) {
            $model = $model->orderRaw('field(product_id, ' . implode(',', $productIds) . ')');
        }
        // 获取商品列表数据
        $data = $model->with(['category', 'image.file', 'sku'])
            ->where($filter)
            ->where('product_id', 'in', $productIds)
            ->select();

        // 整理列表数据并返回
        return $this->setProductListData($data, true);
    }

    /**
     * 商品多规格信息
     */
    public function getManySpecData($specRel, $skuData)
    {
        // spec_attr
        $specAttrData = [];
        foreach ($specRel as $item) {
            if (!isset($specAttrData[$item['spec_id']])) {
                $specAttrData[$item['spec_id']] = [
                    'group_id' => $item['spec']['spec_id'],
                    'group_name' => $item['spec']['spec_name'],
                    'spec_items' => [],
                ];
            }
            $specAttrData[$item['spec_id']]['spec_items'][] = [
                'item_id' => $item['spec_value_id'],
                'spec_value' => $item['spec_value'],
            ];
        }
        // spec_list
        $specListData = [];
        foreach ($skuData as $item) {
            $image = (isset($item['image']) && !empty($item['image'])) ? $item['image'] : ['file_id' => 0, 'file_path' => ''];
            $specListData[] = [
                'product_sku_id' => $item['product_sku_id'],
                'spec_sku_id' => $item['spec_sku_id'],
                'rows' => [],
                'spec_form' => [
                    'image_id' => $image['file_id'],
                    'image_path' => $image['file_path'],
                    'product_no' => $item['product_no'],
                    'product_price' => $item['product_price'],
                    'product_supply_price' => $item['product_supply_price'],
                    'agent_min_price' => $item['agent_min_price'],
                    'agent_max_price' => $item['agent_max_price'],
                    'product_weight' => $item['product_weight'],
                    'line_price' => $item['line_price'],
                    'stock_num' => $item['stock_num'],
                ],
            ];
        }
        return ['spec_attr' => array_values($specAttrData), 'spec_list' => $specListData];
    }

    /**
     * 多规格表格数据
     */
    public function getManySpecTable(&$product)
    {
        $specData = $this->getManySpecData($product['spec_rel'], $product['sku']);
        $totalRow = count($specData['spec_list']);
        foreach ($specData['spec_list'] as $i => &$sku) {
            $rowData = [];
            $rowCount = 1;
            foreach ($specData['spec_attr'] as $attr) {
                $skuValues = $attr['spec_items'];
                $rowCount *= count($skuValues);
                $anInterBankNum = ($totalRow / $rowCount);
                $point = (($i / $anInterBankNum) % count($skuValues));
                if (0 === ($i % $anInterBankNum)) {
                    $rowData[] = [
                        'rowspan' => $anInterBankNum,
                        'item_id' => $skuValues[$point]['item_id'],
                        'spec_value' => $skuValues[$point]['spec_value']
                    ];
                }
            }
            $sku['rows'] = $rowData;
        }
        return $specData;
    }


    /**
     * 获取商品详情
     */
    public static function detail($product_id)
    {
        $model = (new static())->with([
            'category',
            'image.file',
            'sku.image',
            'spec_rel.spec',
        ])->where('product_id', '=', $product_id)
            ->find();
        if (empty($model)) {
            return $model;
        }
        //上下架时间
        if(isset($model['automatic_shelves']) && $model['automatic_shelves'] == 1){
            $shelves[]=date('Y-m-d H:i:s',$model['shelves_time']);
            $shelves[]=date('Y-m-d H:i:s',$model['the_shelves_time']);
            $model['shelves_time']=$shelves;
        }
        //预售时间
        if($model['sale_time']){
            $model['sale_time'] = date('Y-m-d H:i:s',$model['sale_time']);
        }
        //发货时间
        if($model['delivery_time']){
            $model['delivery_time'] = date('Y-m-d H:i:s',$model['delivery_time']);
        }
        // 整理商品数据并返回
        return $model->setProductListData($model, false);
    }

    /**
     * 指定的商品规格信息
     */
    public static function getProductSku($product, $specSkuId)
    {
        // 获取指定的sku
        $productSku = [];
        if(empty($product)){
            return false;
        }
        foreach ($product['sku'] as $item) {
            if ($item['spec_sku_id'] == $specSkuId) {
                $productSku = $item;
                break;
            }
        }
        if (empty($productSku)) {
            return false;
        }
        // 多规格文字内容
        $productSku['product_attr'] = '';
        if ($product['spec_type'] == 20) {
            $specRelData = helper::arrayColumn2Key($product['spec_rel'], 'spec_value_id');

            if(!empty($productSku['spec_sku_id'])){
                
                $attrs = explode('_', $productSku['spec_sku_id']);

                foreach ($attrs as $specValueId) {
                    if(isset($specRelData[$specValueId]['spec']['spec_name']) && isset($specRelData[$specValueId]['spec_value'])){
                        $productSku['product_attr'] .= $specRelData[$specValueId]['spec']['spec_name'] . ':'
                        . $specRelData[$specValueId]['spec_value'] . '; ';
                    }
                
                }
            }
            
        }
        return $productSku;
    }

    /**
     * 根据商品名称得到相关列表
     */
    public function getWhereData($product_name)
    {
        return $this->where('product_name', 'like', '%' . trim($product_name) . '%')->select();
    }

    /* 
    * 商品关联其他库存
    */
    public function stock()
    {
       return $this->hasMany('app\\common\\model\\plus\\logistics\\Stock','product_id','product_id')->order(['product_stock' => 'desc']);
    }

    /* 
    * 商品关联
    */

    /**
     * 显示的sku，目前取最低价
     */
    public static function getShowSku($product)
    {
        //如果是单规格
        if($product['spec_type'] == 10){
            return isset($product['sku'][0])?$product['sku'][0]:0;
        }else{
            //多规格返回最低价
            foreach ($product['sku'] as $sku){
                if(!empty($sku['spec_sku_id'])){
                    return $sku;
                }
                // if($product['product_price'] == $sku['product_price']){
                    // return $sku;
                // }
            }
        }
        // 兼容历史数据，如果找不到返回第一个
        return isset($product['sku'][0])?$product['sku'][0]:0;
    }

    // 获取商品导出数据
    public function getExportGoods($params = array(),$ids = '')
    {
        if(!empty($params)){
            $model = $this;
            $supply_mod = new SupplyModel();
            $cate_mod = new CategoryModel();

            if (isset($params['type'])) {
                //出售中
                if ($params['type'] == 'sell') {
                    $model = $model->where('product_status', '=', 10);
                }
                //库存紧张
                if ($params['type'] == 'stock') {
                    $model = $model->whereBetween('product_stock', [1, 20]);
                }
                //已售罄
                if ($params['type'] == 'over') {
                    $model = $model->where('product_stock', '=', 0);
                }
                //已下架
                if ($params['type'] == 'lower') {
                    $model = $model->where('product_status', '=', 20);
                }
                //草稿
                if ($params['type'] == 'draft') {
                    $model = $model->where('product_status', '=', 30);
                }
            }

            if ($params['category_id'] > 0) {
                $arr = Category::getSubCategoryId($params['category_id']);
                $model = $model->where('category_id', 'IN', $arr);
            }

            if (!empty($params['product_name'])) {
                $model = $model->where('product_name', 'like', '%' . trim($params['product_name']) . '%');
            }

            if(!empty(self::$supply_id)){
                $model = $model->where('supply_id',self::$supply_id);
            }

            if(!empty($ids)){
                $model = $model->where('product_id','in',explode(',', $ids));
            }
            
            $list = $model
                ->field(['product_id','product_name','product_stock','category_id','create_time','supply_id','product_status','link','agent_max_price','agent_min_price', '(sales_initial + sales_actual) as product_sales'
                ])
                ->where('is_delete', '=', 0)
                ->where('app_id',self::$app_id)
                ->select();

            // 整理列表数据并返回
            if(!empty($list)){
                foreach ($list as $key => $value) {
                    // $list[$key]['create_time'] = $value['create_time'];
                    $list[$key]['supply_name'] = $supply_mod->where(['supply_id'=>$value['supply_id'],'app_id'=>self::$app_id])->value('name');
                    $cate_info = $cate_mod->getCateName($value['category_id']);
                    if($cate_info){
                        $list[$key]['first_cate_name'] = $cate_info['cate_name_1'];
                        $list[$key]['send_cate_name'] = $cate_info['cate_name_2'];
                    }else{
                        $list[$key]['first_cate_name'] = '';
                        $list[$key]['send_cate_name'] = '';
                    }

                    $list[$key]['price_area'] = '['.$value['agent_min_price'].'-'.$value['agent_max_price'].']';
                }
            }

            return $list;
        }else{
            return [];
        }
    }

    /**
     * 商品上下架
     * 根据当前时间，实现商品上下架功能
     */
    public function shelves(){
        $model =$this;
        $list = $model->field(['*'])
            ->where('automatic_shelves', '=', 1)
           ->select();
        foreach ($list as $v){
            if($v['shelves_time'] < time() && $v['the_shelves_time'] > time()){
                $model->where('product_id','=',$v['product_id'])->update(['product_status'=>10]);
            }else{
                $model->where('product_id','=',$v['product_id'])->update(['product_status'=>20]);
            }
        }
    }

    /**
     * 获取esConfig配置
     * @return mixed
     */
    protected function getEsConfig()
    {
        $vars['values'] = SettingModel::getItem('esConfig');
        return $vars;
    }

    /**
     * 获取当前的配置,是否开启专业搜索
     * @return bool
     */
    protected function isElasticsearchOpen(){
        $var = $this->getEsConfig();
        return isset($var['values']['isOpen']) ? boolval($var['values']['isOpen']) : false;
    }

    /**
     * 获取全部商品列表
     */
    public function getAllList($param)
    {
        // 商品列表获取条件
        $params = array_merge([
//            'type' => 'sell',         // 商品状态
            'category_id' => 0,     // 分类id
            'sortType' => 'all',    // 排序类型
            'sortPrice' => false,   // 价格排序 高低
            'list_rows' => 15,       // 每页数量
        ], $param);
        // 筛选条件
        $filter = [];
        $model = $this;
        if (!empty(self::$supply_id)) {
            $model = $model->where('supply_id', '=', self::$supply_id);
        }
        if (!empty($params['supply_id'])) {
            $model = $model->where('supply_id', '=', $params['supply_id']);
        }
        if ($params['category_id'] > 0) {
            $arr = Category::getSubCategoryId($params['category_id']);
            $model = $model->where('category_id', 'IN', $arr);
        }
        if (!empty($params['product_name'])) {
            $model = $model->where('product_name', 'like', '%' . trim($params['product_name']) . '%');
        }
        if (!empty($params['search'])) {
            $model = $model->where('product_name', 'like', '%' . trim($params['search']) . '%');
        }
        // 排序规则
        $sort = [];
        if ($params['sortType'] === 'all') {
            $sort = ['product_sort', 'product_id' => 'desc'];
        } else if ($params['sortType'] === 'sales') {
            $sort = ['product_sales' => 'desc'];
        } else if ($params['sortType'] === 'price') {
            $sort = $params['sortPrice'] ? ['product_max_price' => 'desc'] : ['product_min_price'];
        } else {
            $sort = ['product_sort'=>'asc'];
        }
        if (isset($params['type'])) {
            //出售中
            if ($params['type'] == 'sell') {
                $model = $model->where('product_status', '=', 10);
            }
            //库存紧张
            if ($params['type'] == 'stock') {
                $model = $model->whereBetween('product_stock', [1, 20]);
            }
            //已售罄
            if ($params['type'] == 'over') {
                $model = $model->where('product_stock', '=', 0);
            }
            //已下架
            if ($params['type'] == 'lower') {
                $model = $model->where('product_status', '=', 20);
            }
            //草稿
            if ($params['type'] == 'draft') {
                $model = $model->where('product_status', '=', 30);
            }
        }
        // 商品表名称
        $tableName = $this->getTable();
        // 多规格商品 最高价与最低价
        $ProductSku = new ProductSku;
        $minPriceSql = $ProductSku->field(['MIN(product_price)'])
            ->where('product_id', 'EXP', "= `$tableName`.`product_id`")->buildSql();
        $maxPriceSql = $ProductSku->field(['MAX(product_price)'])
            ->where('product_id', 'EXP', "= `$tableName`.`product_id`")->buildSql();
        // 执行查询

        $list = $model
            ->field(['*', '(sales_initial + sales_actual) as product_sales',
                "$minPriceSql AS product_min_price",
                "$maxPriceSql AS product_max_price"
            ])
            ->with(['category', 'image.file','supply'])
            ->where('is_delete', '=', 0)
            ->where($filter)
            ->order($sort)
            ->paginate($params, false, [
                'query' => \request()->request(),
            ]);

        // 整理列表数据并返回
        return $this->setProductListData($list, true);
    }

}
