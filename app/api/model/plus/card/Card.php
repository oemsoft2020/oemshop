<?php

namespace app\api\model\plus\card;

use app\common\model\plus\card\Card as CardModel;
use app\common\model\plus\card\CardCount;
use app\common\model\product\Label as LabelModel;
use app\common\model\user\UserThumbsUp as UserThumbsUpModel;
use app\api\model\product\Product as ProductModel;
use app\api\model\plus\card\CardCategory as CardCategoryModel;
use app\common\model\plus\card\CardCategory;
use app\common\model\settings\Region;
use think\facade\Db;

class Card extends CardModel
{
    /**
     * 隐藏字段
     */
    protected $hidden = [
        'is_delete',
        'create_time',
        'update_time',
    ];

    public function detail($card_id,$user_id = 0)
    {
        $model = $this;
        $info =  $model->with(['user','supply','cardCount.user'=>function($query){
            $where = [
                'type'=>'1',
                'sign'=>'view',
            ];
            return $query->where($where)->where('user_id','<>',0)->group('user_id')->limit(7);
        }])->find($card_id);
        $info['images'] = []; 
        $info['supplyArr'] = [];
        if(!empty($info['images_str'])){
          $info['images']  =  explode(',',$info['images_str']);
        }
        $info['labelList'] = [];
        $userThumbsList = [];

        if($user_id){
            //标签点赞
            $where = [
                ['type','=','kmd_label'],
                ['card_id','=',$card_id],
                ['user_id','=',$user_id]
            ];
            $userThumbsList =  UserThumbsUpModel::where($where)->column('card_id,obj_user_id,data_id','data_id');

            // 是否点靠谱
            $where = [
                'user_id'=>$user_id,
                'card_id'=>$card_id,
                'sign'=>'praise',
                'type'=>3
            ];
            $info['isThumbs'] = CardCount::where($where)->count();
        }
        if(!empty($info['kmd_label_ids'])){
           
            $info['labelList']  =  LabelModel::where('kmd_label_id','in',explode(',',$info['kmd_label_ids']))->select();
            $where = [
                ['data_id','in',explode(',',$info['kmd_label_ids'])],
                ['type','=','kmd_label'],
                ['card_id','=',$card_id]
            ];
            $thumbsup_list = UserThumbsUpModel::where($where)->group('data_id')->column('card_id,data_id,count(user_thumbsup_id) as thumbscount','data_id');

            foreach ($info['labelList'] as &$label) {
                $label['clicked'] = 0;
                $label['count'] = 0; 
                if(isset($thumbsup_list[$label['kmd_label_id']])){
                        $label['count'] = $thumbsup_list[$label['kmd_label_id']]['thumbscount'];
                }
                if(isset($userThumbsList[$label['kmd_label_id']])){
                    $label['clicked'] = 1;
                }
            }
            unset($label);
        }
        if(!empty($info['category_id'])){

            $cardCategoryModel = new CardCategory();
            $info['category'] = $cardCategoryModel->find($info['category_id']);
        }

        if(!empty($info['region_id'])||!empty($info['city_id'])||!empty($info['province_id'])){
            
            $province = Region::getNameById($info['province_id']);
            $city = Region::getNameById($info['city_id']);
            $region = $info['region_id'] == 0 ? '': Region::getNameById($info['region_id']);
            $info['region_address'] = $province.','.$city.','.$region;
        }

        $info['productList'] =[];
        if(!empty($info['supply_id'])){
             // 整理请求的参数
            $param = [
                'supply_id'=>$info['supply_id'],
                'product_status' => 10,
                'list_rows'=>2,
            ];
            // 获取列表数据
            $model = new ProductModel;
            $productList = $model->getList($param)->toArray();
            $info['productList'] = $productList['data'];
        }
        $info['card_type'] = 'cardType0';

        // 点击靠谱数
        $where = [
            'card_id'=>$card_id,
            'sign'=>'praise',
            'type'=>3
        ];
        $info['thumbs']  = CardCount::where($where)->count();
        //浏览记录数
        $where = [
            'card_id'=>$card_id,
            'sign'=>'view',
            'type'=>1
        ];
        $info['peopleview'] =CardCount::where($where)->group('user_id')->count();
        $info['active_time'] = [];
        if(isset($info['start_time'])&&isset($info['end_time'])){
            $info['active_time'] = [
                date('Y-m-d H:i', $info['start_time']),
                date('Y-m-d H:i', $info['end_time']),
            ];
        }
        if(!empty($info['more_supply'])){
            $info['supplyArr'] = array_filter(explode(';',$info['more_supply']));
        }
       
        return $info;
    }

    /* 获取自身的名片 */
    public function getSelfCard($user_id)
    {
        $model = $this;

        if(empty($user_id)){
            return false;
        }

        $info =  $model->where('status',1)->where('is_delete',0)->where('user_id',$user_id)->order('create_time','desc')->find();
        return $info;
    }

    /* 获取默认名片 */
    public function getDefaultCard()
    {
        $model = $this;

        $info =  $model->where('status',1)->where('is_delete',0)->where('isdefault',1)->order('create_time','desc')->find();
        return $info;
    }

    /* 
    *获取名片列表
    */
    public function getList($param)
    {
        $param = array_merge([
            'card_category_id'=>0,
            'sub_card_category_id'=>0,
            'keywords'=>'',
            'province_id'=>0,
            'city_id'=>0,
            'region_id'=>0,
            'supply_id'=>0

        ],$param);
        $where  = [
            ['is_delete','=',0],
           
        ];
        $model  = $this;
        $cardCategoryModel = new CardCategoryModel();
        if(!isset($param['status'])){
            $where[] = ['status','=',1];
        }else{
            $where[] = ['status','=',$param['status']];
        }

        if(!empty($param['card_category_id'])&&empty($param['sub_card_category_id'])){
            $where[] = [
                'category_id','=',$param['card_category_id']
            ];
            $arr = $cardCategoryModel->getSubCategoryId($param['card_category_id']);
            $model = $model->where('category_id', 'IN', $arr);
        }
        if(!empty($param['sub_card_category_id'])){
           
            $arr = $cardCategoryModel->getSubCategoryId($param['sub_card_category_id']);
            $model = $model->where('category_id', 'IN', $arr);
            
        }
        if(!empty($param['province_id'])){
            $model = $model->where('province_id', '=', $param['province_id']);
        }
        if(!empty($param['city_id'])){
            $model = $model->where('city_id', '=', $param['city_id']);
        }
        if(!empty($param['region_id'])){
            $model = $model->where('region_id', '=', $param['region_id']);
        }
        if(!empty($param['keywords'])){
            $model = $model->where('name|mobile', 'like', '%'.$param['keywords'].'%');
        }

        if(!empty($param['supply_id'])){
            $model = $model->where('supply_id', '=', $param['supply_id']);
        }

        $order = [
            'istop'=>'desc',
            'displayorder'=>'asc',
            'create_time'=>'desc',
        ];
        
        $lists = $model->with(['supply'])->where($where)->order($order)->paginate(15, false, [
            'query' => request()->request()
        ]);
        return $lists;
    }

    /* 
    * 获取名片信息
    */
    public function getCardInfo($user_id)
    {
        $model = $this;

        if(empty($user_id)){
            return false;
        }

        $info =  $model->where('user_id',$user_id)->where('is_delete',0)->order('create_time','desc')->find();
        return $info;
    }
}
