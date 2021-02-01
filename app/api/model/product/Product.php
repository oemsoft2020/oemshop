<?php

namespace app\api\model\product;

use app\common\model\product\Product as ProductModel;
use app\common\service\product\BaseProductService;
use app\common\model\settings\Setting;
use app\common\model\user\Grade as GradeModel;
use app\common\library\helper;
use app\common\model\user\UserBrowseRecords as UserBrowseRecordsModel;
use app\common\model\settings\Setting as SettingModel;
use app\common\model\file\UploadFile as UploadFileModel;
use Grafika\Color;
use Grafika\Grafika;
use Endroid\QrCode\QrCode;
use app\common\model\plus\live\AnchorNotice as noticeMod;
use app\common\model\plus\anchor\Anchor as AnchorMod;
use app\common\model\product\ProductImage as imgMod;
use app\api\model\plus\agent\Setting as agentSetting;
use app\api\model\plus\agent\User as agentUser;
use app\common\model\product\ProductCollect;

/**
 * 商品模型
 */
class Product extends ProductModel
{
    /**
     * 隐藏字段
     */
    protected $hidden = [
        'spec_rel',
        'delivery',
        'sales_initial',
        'sales_actual',
        'is_delete',
        'app_id',
        'create_time',
        'update_time'
    ];

    /**
     * 商品详情：HTML实体转换回普通字符
     */
    public function getContentAttr($value)
    {
        return htmlspecialchars_decode($value);
    }

    /**
     * 获取商品列表
     */
    public function getList($param, $userInfo = false)
    {
        // 获取商品列表
        $data = parent::getList($param);

        // 隐藏api属性
        !$data->isEmpty() && $data->hidden(['category', 'content', 'image', 'sku']);
        // 整理列表数据并返回
        return $this->setProductListDataFromApi($data, true, ['userInfo' => $userInfo]);
    }

    /**
     * 商品详情
     */
    public static function detail($product_id)
    {
        // 商品详情
        $detail = parent::detail($product_id);
        // 多规格商品sku信息
        $detail['product_multi_spec'] = BaseProductService::getSpecData($detail);
        return $detail;
    }

    /**
     * 获取商品详情页面
     */
    public function getDetails($productId, $userInfo = false)
    {
        // 获取商品详情
        $detail = $this->with([
            'category',
            'image' => ['file'],
            'sku' => ['image'],
            'spec_rel' => ['spec'],
            'delivery' => ['rule'],
            'commentData' => function ($query) {
                $query->with('user')->where(['is_delete' => 0, 'status' => 1])->limit(2);
            }
        ])->withCount(['commentData' => function ($query) {
            $query->where(['is_delete' => 0, 'status' => 1]);
        }])
            ->where('product_id', '=', $productId)
            ->find();
        // 判断商品的状态
        if (empty($detail) || $detail['is_delete'] || $detail['product_status']['value'] != 10) {
            $this->error = '很抱歉，商品信息不存在或已下架';
            return false;
        }
        // 更新访问数据
        $this->where('product_id', '=', $detail['product_id'])->inc('view_times')->update();
        // 设置商品展示的数据
        $detail = $this->setProductListDataFromApi($detail, false, ['userInfo' => $userInfo]);
        $detail['collect'] =[
            'collect_id' => 0,
            'status' => 0
        ];
        if ($userInfo['user_id']){
            $collect = ProductCollect::field('collect_id, status')->where(['user_id'=>$userInfo['user_id'], 'product_id'=>$detail['product_id']])->find();
            if ($collect) $detail['collect'] = $collect;
        }
        // 多规格商品sku信息
        $detail['product_multi_spec'] = BaseProductService::getSpecData($detail);
        return $detail;
    }

    /**
     * 根据商品id集获取商品列表
     */
    public function getListByIdsFromApi($productIds, $userInfo = false)
    {
        // 获取商品列表
        $data = parent::getListByIds($productIds, 10);
        // 整理列表数据并返回
        return $this->setProductListDataFromApi($data, true, ['userInfo' => $userInfo]);
    }


    /**
     * 设置商品展示的数据 api模块
     */
    public function setProductListDataFromApi(&$data, $isMultiple, $param)
    {
        return parent::setProductListData($data, $isMultiple, function ($product) use ($param) {
            // 计算并设置商品会员价
            $this->setProductGradeMoney($param['userInfo'], $product);
        });
    }

    /**
     * 设置商品的会员价以及购买权限
     */
    private function setProductGradeMoney($user, &$product)
    {
        // 会员等级状态
        $gradeStatus = (!empty($user) && $user['grade_id'] > 0 && !empty($user['grade']))
            && (!$user['grade']['is_delete']);
        $trade = Setting::getItem('trade');
        $price_mode = isset($trade['price_mode']) ? $trade['price_mode'] : 0;
        $setting = json_decode($product['setting'], true);

        $buy_auth = $trade['buy_auth'];

        $buy_auth['buy_auth_id'] = empty($setting['buy_auth']['buy_auth_id']) ? $buy_auth['buy_auth_id'] : $setting['buy_auth']['buy_auth_id'];

        $buy_auth['show_price_id'] = empty($setting['buy_auth']['show_price_id']) ? $buy_auth['show_price_id'] : $setting['buy_auth']['show_price_id'];

        $buy_auth['tips'] = empty($setting['buy_auth']['tips']) ? $buy_auth['tips'] : $setting['buy_auth']['tips'];

        $buy_auth['linkUrl'] = empty($setting['buy_auth']['linkUrl']) ? $buy_auth['linkUrl'] : $setting['buy_auth']['linkUrl'];

        $buy_auth['can_buy'] = 1;

        if (empty($buy_auth['buy_auth_id']) || !empty($buy_auth['buy_auth_id']) && in_array($user['grade']['grade_id'], $buy_auth['buy_auth_id'])) {
            $buy_auth['can_buy'] = 1;
        } else {
            $buy_auth['can_buy'] = $buy_auth['is_open'] > 0 ? 0 : 1;
        }
        if (empty($buy_auth['show_price_id']) || !empty($buy_auth['show_price_id']) && in_array($user['grade']['grade_id'], $buy_auth['show_price_id'])) {
            $buy_auth['no_price'] = 0;
        } else {
            $buy_auth['no_price'] = $buy_auth['no_price'] > 0 ? 1 : 0;
        }


        $product['buy_auth'] = $buy_auth;

        // 判断商品是否参与会员折扣
        if (!$gradeStatus || !$product['is_enable_grade']) {
            $product['is_user_grade'] = false;
            return;
        }
        // 商品单独设置了会员折扣
        if ($product['is_alone_grade'] && isset($product['alone_grade_equity'][$user['grade_id']])) {
            // 折扣比例
            if ($product['is_alone_grade'] == 1) {

                $discountRatio = helper::bcdiv($product['alone_grade_equity'][$user['grade_id']], 100);

            } elseif ($product['is_alone_grade'] == 2) {

                $product_price = $product['alone_grade_equity'][$user['grade_id']];
            }

        } else {
            // 折扣比例
            $discountRatio = helper::bcdiv($user['grade']['equity'], 100);
        }
        if (isset($discountRatio) && $discountRatio < 1 || isset($product_price) && $product_price > 0) {
            // 标记参与会员折扣
            $product['is_user_grade'] = true;
            if ($product['is_alone_grade'] == 2 && isset($product_price)) {
                $product['product_price'] = $product_price;
            } else {
                $product['product_price'] = helper::number2(helper::bcmul($product['product_price'], $discountRatio), true);
            }

            // 会员折扣价
            foreach ($product['sku'] as &$skuItem) {
                if ($product['is_alone_grade'] == 2 && isset($product_price)) {

                    $skuItem['product_price'] = $product_price;

                } else {
                    $skuItem['product_price'] = helper::number2(helper::bcmul($skuItem['product_price'], $discountRatio), true);
                }

            }

        } else {
            $product['is_user_grade'] = false;
        }
        // 渠道会员走渠道价，非渠道走会员价
        if (!empty($price_mode)) {
            $product['is_user_grade'] = true;
            $gradeList = GradeModel::getAgentList();
            // 渠道价格
            if (!empty($gradeList)) {
                $product['product_price'] = $this->getGredePrice($gradeList, $user['grade'], $product);
                foreach ($product['sku'] as &$skuItem) {
                    $skuItem['product_price'] = $this->getGredePrice($gradeList, $user['grade'], $skuItem);
                }
            }
        }
        $direct_commission = $this->getDirectCommission($user,$product);
        $product['show_commission'] = !empty($direct_commission)?1:0;
        $product['direct_commission'] = !empty($direct_commission)?$direct_commission:0;

    }

    /**
     * 为你推荐
     */
    public function getRecommendProduct($data, $userInfo = false)
    {
        $params = json_decode($data['param'], true);
        //读取推荐配置文件
        $params = Setting::getItem('recommend');
        $model = $this;

        // 手动
        if ($params['choice'] == 1) {
            $product_id = array_column($params['product'], 'product_id');
            $model = $model->where('product_id', 'IN', $product_id);
            $list = $model->with(['category', 'image.file'])
                ->where('product_status', '=', 10)
                ->where('is_delete', '=', 0)
                ->paginate($data, false, [
                    'query' => \request()->request(),
                ]);
            // 整理列表数据并返回
            // return $this->setProductListData($list, true);
            return $this->setProductListDataFromApi($list, true, ['userInfo' => $userInfo]);
        } else {

            switch ($params['type']) {
                case '10':
                    $sort = ['sales_actual' => 'desc'];
                    break;
                case '20':
                    $sort = ['create_time' => 'desc'];
                    break;
                case '30':
                    $sort = ['view_times' => 'desc'];
                    break;
            }

            // 自动
            //$sort = $params['type'] == 20 ? ['create_time' => 'desc'] : ['sales_actual' => 'desc'];
            $list = $model->with(['category', 'image.file'])
                ->where('product_status', '=', 10)
                ->where('is_delete', '=', 0)
                ->order($sort)
                ->paginate(['list_rows' => $params['num'],], false, [
                    'query' => \request()->request(),
                ]);
            // return $this->setProductListData($list, true);
            return $this->setProductListDataFromApi($list, true, ['userInfo' => $userInfo]);
        }
    }

    /**
     * 获取会员自动折扣价
     */
    private function getGredePrice($gradeList, $grade, $skuItem)
    {
        $count = count($gradeList) - 1;
        if ($count == 0) {
            $step_price = 0;
        } else {
            $step_price = helper::bcdiv(helper::number2($skuItem['agent_max_price'] - $skuItem['agent_min_price'], true, 0), $count);
        }

        $product_price = $skuItem['product_price'];
        $agent_max_price = $skuItem['agent_max_price'] > 0 ? $skuItem['agent_max_price'] : $skuItem['product_price'];
        foreach ($gradeList as $k => $v) {
            if ($v['level'] == $grade['level']) {
                $product_price = helper::number2($agent_max_price - $k * $step_price, true);
                if ($step_price > 0 && $k == $count) {
                    $product_price = $skuItem['agent_min_price'] > 0 ? $skuItem['agent_min_price'] : $product_price;
                }
                break;
            }
        }
        return $product_price;

    }

    public function checkBuyAuth($product, $user)
    {
        $trade = Setting::getItem('trade');
        $setting = json_decode($product['setting'], true);

        $buy_auth = $trade['buy_auth'];

        $buy_auth['buy_auth_id'] = empty($setting['buy_auth']['buy_auth_id']) ? $buy_auth['buy_auth_id'] : $setting['buy_auth']['buy_auth_id'];

        $buy_auth['show_price_id'] = empty($setting['buy_auth']['show_price_id']) ? $buy_auth['show_price_id'] : $setting['buy_auth']['show_price_id'];

        $buy_auth['tips'] = empty($setting['buy_auth']['tips']) ? $buy_auth['tips'] : $setting['buy_auth']['tips'];

        $buy_auth['linkUrl'] = empty($setting['buy_auth']['linkUrl']) ? $buy_auth['linkUrl'] : $setting['buy_auth']['linkUrl'];

        $buy_auth['can_buy'] = 1;


        if (empty($buy_auth['buy_auth_id']) || !empty($buy_auth['buy_auth_id']) && in_array($user['grade']['grade_id'], $buy_auth['buy_auth_id'])) {
            $buy_auth['can_buy'] = 1;
        } else {
            $buy_auth['can_buy'] = $buy_auth['is_open'] > 0 ? 0 : 1;
        }
        if (empty($buy_auth['show_price_id']) || !empty($buy_auth['show_price_id']) && in_array($user['grade']['grade_id'], $buy_auth['show_price_id'])) {
            $buy_auth['no_price'] = 0;
        } else {
            $buy_auth['no_price'] = $buy_auth['no_price'] > 0 ? 1 : 0;
        }

        // $product['buy_auth']=$buy_auth;
        return $buy_auth;
    }

    /* 
    * 记录商品浏览记录
    *
    */
    public function productBrowseRecords($user_info, $product_id)
    {
        $trade_setting = SettingModel::getItem('trade');

        if (!isset($trade_setting['open_view_product']) || empty($trade_setting['open_view_product'])) {
            return false;
        }
        if ($user_info && $product_id) {
            $browse_model = new UserBrowseRecordsModel();
            $data = [
                'user_id' => $user_info['user_id'],
                'type' => 'product',
                'data_id' => $product_id
            ];
            return $browse_model->saveBrowseRecords($data);
        }
        return false;
    }

    /**
     * 获取商品总数
     */
    public function getCount($user, $type = 'all')
    {
        if ($user === false) {
            return false;
        }
        // 筛选条件
        $filter = [];
        // 订单数据类型
        switch ($type) {
            case 'all':
                break;
            case 'supply';
                $filter['supply_id'] = $user['supply_id'];
                break;
        }
        return $this->where($filter)
            ->where('is_delete', '=', 0)
            ->count();
    }

    public function standup_and_downProduct($product_id, $product_status)
    {
        if (empty($product_id) && empty($product_status)) {
            $this->error = '缺少参数';
            return false;
        }
        if ($product_status == 10) {
            $data = array();
            $data['product_status'] = 10;
            $res = $this->where('product_id', '=', $product_id)->save($data);
            return 1;
        } else if ($product_status == 20) {
            $data = array();
            $data['product_status'] = 20;
            $res = $this->where('product_id', '=', $product_id)->save($data);
            return 2;
        }
    }

    /**
     * 合成主播图片
     * @Author   linpf
     * @DataTime 2020-11-12T15:58:26+0800
     * @param string $ids [商品ids]
     * @param string $user_id [用户id]
     * @return   [type]                            [description]
     */
    public function makeLivePoster($ids = '', $user_id = '')
    {
        if (empty($ids) || empty($user_id)) {
            return ['status' => false, 'msg' => '未选择商品或者未登陆'];
        }

        // 获取主播预告信息
        $notice_mod = new noticeMod();
        $anchor_mod = new AnchorMod();
        $img_mod = new imgMod();
        $file_mod = new UploadFileModel();

        $live_data = $notice_mod->where('status', 1)->order('create_time', 'desc')->find();

        if ($live_data) {
            // 获取主播数据
            $anchor_data = $anchor_mod->where('anchor_id', $live_data['anchor_id'])->find();
            if (empty($anchor_data)) {
                return ['status' => false, 'msg' => '该用户不是主播'];
            }

            $img = $live_data['img_url'];
            $backdrop = root_path() . '/runtime/' . time() . rand(1000, 99999) . '.png';

            // 下载海报图
            if (!empty($img)) {
                file_put_contents($backdrop, file_get_contents($img));
            }

            // 1. 下载背景图
            $background_img = root_path() . '/public/image/diy/code_back.jpg';

            // 实例化图像编辑器
            $editor = Grafika::createEditor(['Gd']);
            // 字体文件路径
            $fontPath = Grafika::fontsDir() . '/' . 'st-heiti-light.ttc';

            // 打开海报背景图
            $editor->open($backdropImage, $background_img);

            // 打开海报图
            $editor->open($qrcodeImage, $backdrop);

            // 重设海报图宽高
            $editor->resizeExact($qrcodeImage, 350, 350);

            // 海报图添加到背景图
            $qrcodeX = 50;
            $qrcodeY = 30;
            $fontSize = 16;
            $editor->blend($backdropImage, $qrcodeImage, 'normal', 1.0, 'top-left', $qrcodeX, $qrcodeY);

            // 写入主播昵称
            $editor->text($backdropImage, $anchor_data['name'], 20, 450, 100, new Color('#353535'), $fontPath);

            // 写入主播id
            $editor->text($backdropImage, 'ID:' . $anchor_data['anchor_data_id'], 16, 450, 150, new Color('#666666'), $fontPath);

            // 开播时间
            $time_str = '开播时间:' . $live_data['start_at'] . '一' . $live_data['end_at'];
            $editor->text($backdropImage, $time_str, 16, 450, 300, new Color('#666666'), $fontPath);

            $img_info = $this->getPosterPath();

            // 获取商品图片
            if (!empty($ids)) {
                $goods_id = explode(',', $ids);
                $goods_id[0] = str_replace('[', '', $goods_id[0]);
                $goods_id[count($goods_id) - 1] = str_replace(']', '', $goods_id[count($goods_id) - 1]);

                // if(is_array($ids)){
                //     $goods_id = $ids;
                // }else{
                //     $goods_id = explode(',', $ids);
                // }

                $X = 20;
                $Y = 500;
                $two_x = 20;
                $two_y = 900;
                if (count($goods_id) <= 6) {
                    $goodsData = $this->field('product_id,selling_point')->whereIn('product_id', $goods_id)->select();

                    foreach ($goodsData as $key => $value) {
                        $temp_img = root_path() . '/runtime/goods_' . $value['product_id'] . time() . rand(1000, 99999) . '.png';
                        $file_id = $img_mod->where('product_id', $value['product_id'])->value('image_id');

                        // 下载商品图片
                        $img = $file_mod->where('file_id', $file_id)->find();
                        $path = $img['file_url'] . '/' . $img['file_name'];

                        if ($key <= 2) {
                            if (!empty($path)) {
                                file_put_contents($temp_img, file_get_contents($path));

                                $editor->open($goodsImage, $temp_img);

                                $editor->resizeExact($goodsImage, 200, 200);

                                $editor->blend($backdropImage, $goodsImage, 'normal', 1.0, 'top-left', $X, $Y);

                                // 写入卖点
                                $selling_point = $this->wrapText($fontSize, 0, $fontPath, $value['selling_point'], 130, 2);
                                $editor->text($backdropImage, $selling_point, 20, $X + 20, 720, new Color('#353535'), $fontPath);
                                $X += 250;
                            }

                        } else {
                            file_put_contents($temp_img, file_get_contents($path));

                            $editor->open($goodsImage, $temp_img);

                            $editor->resizeExact($goodsImage, 200, 200);

                            $editor->blend($backdropImage, $goodsImage, 'normal', 1.0, 'top-left', $two_x, $two_y);

                            // 写入卖点
                            $selling_point = $this->wrapText($fontSize, 0, $fontPath, $value['selling_point'], 130);
                            $editor->text($backdropImage, $selling_point, 20, $two_x, 1150, new Color('#353535'), $fontPath);
                            $two_x += 250;
                        }

                        @unlink($temp_img);
                    }

                } else {
                    return ['status' => false, 'msg' => '最大支持6个商品'];
                }
            }

            // 保存图片
            $editor->save($backdropImage, $img_info['url']);
            $img_path = $this->getPosterUrl($img_info['name']);

            @unlink($backdrop);

            return !empty($img_path) ? ['status' => true, 'msg' => '获取成功', 'data' => $img_path] : ['status' => false, 'msg' => '生成失败'];
        } else {
            return ['status' => false, 'msg' => '该用户暂无任何审核通过的直播预告'];
        }

    }

    /**
     * 海报图文件路径
     */
    private function getPosterPath()
    {
        // 保存路径
        $tempPath = root_path('public') . 'temp' . '/' . self::$app_id . '/live_poster/';
        !is_dir($tempPath) && mkdir($tempPath, 0755, true);
        $name = $this->getPosterName();

        return ['url' => $tempPath . $name, 'name' => $name];
    }

    /**
     * 海报图文件名称
     */
    private function getPosterName()
    {
        return 'live_poster_' . md5(time() . rand(0, 999)) . '.png';
    }

    /**
     * 海报图url
     */
    private function getPosterUrl($name = '')
    {
        return \base_url() . 'temp/' . self::$app_id . '/live_poster/' . $name . '?t=' . time();
    }

    /**
     * 处理文字超出长度自动换行
     */
    private function wrapText($fontsize, $angle, $fontface, $string, $width, $max_line = null)
    {
        // 这几个变量分别是 字体大小, 角度, 字体名称, 字符串, 预设宽度
        $content = "";
        // 将字符串拆分成一个个单字 保存到数组 letter 中
        $letter = [];
        for ($i = 0; $i < mb_strlen($string, 'UTF-8'); $i++) {
            $letter[] = mb_substr($string, $i, 1, 'UTF-8');
        }
        $line_count = 0;
        foreach ($letter as $l) {
            $testbox = imagettfbbox($fontsize, $angle, $fontface, $content . ' ' . $l);
            // 判断拼接后的字符串是否超过预设的宽度
            if (($testbox[2] > $width) && ($content !== "")) {
                $line_count++;
                if ($max_line && $line_count >= $max_line) {
                    $content = mb_substr($content, 0, -1, 'UTF-8') . "...";
                    break;
                }
                $content .= "\n";
            }
            $content .= $l;
        }
        return $content;
    }

    /* 
    *获取商品直推分销佣金
    */
    public function getDirectCommission($user_info=[],$product=[],$product_price=0)
    {
        if(empty($user_info)||empty($product)){

            return false;
        }
        
        // 分销商基本设置
        $basic = agentSetting::getItem('basic',$product['app_id']);

        // 分销佣金设置
        $setting = agentSetting::getItem('commission', $product['app_id']);

        // 是否开启分销功能
        if (!$basic['is_open']) {
            return false;
        }

        if(!isset($basic['show_commission'])||empty($basic['show_commission'])){
            return false;
        }
        $is_agent =  agentUser::isAgentUser($user_info['user_id']);
        if(!$is_agent){
            return false;
        }
        // 判断是否开启商品单独分销
        if ($product['is_ind_agent'] == false) {

            if($product_price>0){
                return $product_price * ($setting['first_money'] * 0.01);
            }
            return  $product['product_price'] * ($setting['first_money'] * 0.01);
           
        }
        // 商品单独分销
        if ($product['agent_money_type'] == 10) {
            // 分销佣金类型：百分比
            if($product_price>0){
                return $product_price * ($product['first_money'] * 0.01);
            }
            return $product['product_price'] * ($product['first_money'] * 0.01);

        } else if($product['agent_money_type'] == 20){

            return $product['first_money'];
              
        } else if($product['agent_money_type'] == 30){
            //详细独立佣金
            $independent_commission = json_decode($product['independent_commission'],true);
           
            $first_money =!empty($first_user)?$independent_commission[$user_info['grade_id']]['first_money']:0;
                
            return  $first_money;
           
        }

    }
}