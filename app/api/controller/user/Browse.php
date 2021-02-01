<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\common\model\user\UserBrowseRecords;
use app\api\model\user\User as UserModel;
use app\common\model\plus\supply\Supply;
use app\common\model\product\Product;
use think\facade\Db;

/**
 * 用户浏览记录
 */
class Browse extends Controller
{

    /**
     * 优惠券列表
     */
    public function lists()
    {

        $model = new  UserBrowseRecords();
        $user_info = $this->getUser();
        $params = $this->postData();
        $list = $model->getList($user_info['user_id'],$params);

        if($params['data_type']=='product'){
            $product = new Product();
            foreach ($list as $key => &$item) {
                $item['product']  = $product->with(['image.file','supply'])->find($item['product_id']);
            }
        }elseif ($params['data_type']=='supply') {
            $supply = new Supply();
            foreach ($list as $key => &$item) {
                $item['supply']  = $supply->detail($item['data_id']);
            }
        }
        unset($item);
        
        return $this->renderSuccess('', compact('list'));
    }

    
}