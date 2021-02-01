<?php

namespace app\common\model\plus\live;

use app\common\library\helper;
use app\common\model\BaseModel;
use app\common\model\plus\anchor\Anchor as AnchorMod;
use app\common\model\plus\live\AnchorCoin as AnchorCoinMod;

/**
 * 微信直播模型
 */
class AnchorNotice extends BaseModel
{
    protected $name = 'anchor_notice';
    protected $pk = 'notice_id';

    /**
     * 添加主播预告数据
     * @Author   linpf
     * @DataTime 2020-11-09T16:23:00+0800
     * @param    array                    $data [description]
     */
    public function addLiveData($data = array())
    {
        if(empty($data)){
            return ['status'=>false,'msg'=>'请提交数据'];
        }
       
        $anchor_mod = new AnchorMod();

        // 获取主播id
        if(!isset($data['user_id']) || empty($data['user_id'])){
            return ['status'=>false,'msg'=>'请先登陆'];
        }

        $anchor_id = $anchor_mod->where('user_id',$data['user_id'])->value('anchor_id');

        if(empty($anchor_id)){
            return ['status'=>false,'msg'=>'该用户不是主播'];
        }

        $addData = [
            'anchor_id' => $anchor_id,
            'user_id' => $data['user_id'],
            'start_at' => !empty($data['start_time']) ? str_replace(':','.',$data['start_time']) : 0,
            'end_at' => !empty($data['end_time']) ? str_replace(':','.',$data['end_time']) : 0,
            'img_url' => !empty($data['path']) ? $data['path'] : '',
            'app_id' => self::$app_id,
            'status' => 3
        ];

        if(isset($data['notice_id']) && !empty($data['notice_id'])){
            $res = $this->where('notice_id',$data['notice_id'])->update($addData);
        }else{
            $addData['create_time'] = time();
            $res = $this->insert($addData);
        }

        return $res ? ['status'=>true,'msg'=>'提交成功,请耐心等待审核'] : ['status' => false,'msg'=>'提交失败，请检查提交数据'];
    }

    /**
     * 后台获取主播预告列表
     */
    public function getList($params)
    {   
        $model = $this;
        $anchor_mod = new AnchorMod();
        
        $anchor_ids = [];

        if (!empty($params['search'])) {
            $anchor_ids = $anchor_mod->where('name', 'like', '%' . $params['search'] . '%')->column('anchor_id');
            if(!empty($anchor_ids)){
                $model = $model->whereIn('anchor_id',$anchor_ids);
            }else{
                return [];
            }
        }

        if(!empty($params['aduit_value'])){
            $model = $model->where('status',$params['aduit_value']);
        }
        
        $data = $model->order(['create_time' => 'desc'])->paginate($params, false, [
            'query' => request()->request()

        ])->each(function($item,$key)use($anchor_mod){

            $item->anchor_data_id = '';
            $item->mobile = '';
            $item->anchor_name = '';
            $item->image_url = '';

            if($item['anchor_id']){
                $anchor_info = $anchor_mod->where('anchor_id',$item['anchor_id'])->find();

                if(!empty($anchor_info)){
                    $item->anchor_data_id = $anchor_info['anchor_data_id'];
                    $item->mobile = $anchor_info['mobile'];
                    $item->anchor_name = $anchor_info['name'];
                    $item->image_url = $anchor_info['image_url'];
                }
            }

            if(!empty($item['start_at'])){
                $time_1 = explode('.', $item['start_at']);

                if(!isset($time_1[1])){
                    $time_1[1] = '00';
                }

                if($time_1[0] < 10){
                    $item->time_text = '0'.$time_1[0].':'.$time_1[1];
                }else{
                    $item->time_text = $time_1[0].':'.$time_1[1];
                }


                if(!empty($item['end_at'])){
                    $time_2 = explode('.', $item['end_at']);

                    if(!isset($time_2[1])){
                        $time_2[1] = '00';
                    }

                    if($time_2[0] < 10){
                        $item->time_text .= ' 一 0'.$time_2[0].':'.$time_2[1];
                    }else{
                        $item->time_text .= ' 一 '.$time_2[0].':'.$time_2[1];
                    }
                }
            }

            if($item['status'] == 1){
                $item->status_text = '审核通过';
            
            }elseif($item['status'] == 2){
                $item->status_text = '审核不通过';
            
            }else{
                $item->status_text = '待审核';
            }
        });
            
        return $data;
    }

    /**
     * 前端获取主播预告列表
     * @Author   linpf
     * @DataTime 2020-11-10T10:28:13+0800
     * @param    [type]                   $params [description]
     * @return   [type]                           [description]
     */
    public function getLiveData($params = array())
    {
        if(empty($params)){
            return false;
        }

        $model = $this;

        if($params['dataType'] == 1 && !empty($params['user_id'])){
            $model = $model->where('user_id',$params['user_id']);
        }else{
            $model = $model->where('status',1);
        }

        $timeFun = function($time){
            if($time == '全部' || empty($time)){
                return '';
            }

            $val = explode('-', $time);
            $start_at = $val[0];
            $end_at = $val[1];

            return $dateVal = [$start_at,$end_at];      
        };

        if(isset($params['time']) && !empty($params['time'])){
            $time_area = $timeFun($params['time']);
            if(!empty($time_area)){
                $model = $model->where('start_at','>=',$time_area[0])->where('end_at','<=',$time_area[1]);
            }
        }

        $anchor_mod = new AnchorMod();

        $data = $model->select()->each(function($item)use($anchor_mod){
            if(!empty($item['anchor_id'])){
                $anchor_obj = $anchor_mod->where('anchor_id',$item['anchor_id'])->find();
                $item->anchor_name = $anchor_obj->name;
                $item->anchorId = $anchor_obj->anchor_data_id;
            }else{
                $item->anchor_name = '';
                $item->anchorId = '';
            }

            $now_hour = (double)date('H',time());
            if($item->start_at > $now_hour){
                $item->notice_msg = '预告中';
            }else{
                $item->notice_msg = '直播中';
            }

            if($item->status == 1){
                $item->status_text = '审核通过';
            }elseif($item->status == 2){
                $item->status_text = '审核不通过';
            }else{
                $item->status_text = '待审核';
            }
        });

        return $data;
    }

    /**
     * 获取直播预告详情
     * @Author   linpf
     * @DataTime 2020-11-10T15:48:09+0800
     * @return   [type]                   [description]
     */
    public function getLiveInfo($id = '',$user_id = '')
    {
        if(empty($user_id)){
            return false;
        }

        $anchor_mod = new AnchorMod();
        $coin_mod = new AnchorCoinMod();

        if(empty($id)){
            // 获取主播信息
            $anchor_info = $anchor_mod->where('user_id',$user_id)->find();

            if($anchor_info){
                $info['anchor_name'] = $anchor_info['name'];
                $info['anchor_data_id'] = $anchor_info['anchor_data_id'];
                $info['anchor_img'] = $anchor_info['image_url'];
            }else{
                $info['anchor_name'] = '';
                $info['anchor_data_id'] = '';
                $info['anchor_img'] = '';
            }

            $info['coin'] = [];
        }else{
            $info = $this->where('notice_id',$id)->find();

            if($info){
                // 处理时间
                if(!empty($info['start_at'])){
                    $time_1 = explode('.', $info['start_at']);

                     if(!isset($time_1[1])){
                        $time_1[1] = '00';
                    }

                    if($time_1[0] < 10){
                        $info['startTime'] = '0'.$time_1[0].':'.$time_1[1];
                    }else{
                        $info['startTime'] = $time_1[0].':'.$time_1[1];
                    }
                }

                if(!empty($info['end_at'])){
                    $time_2 = explode('.', $info['end_at']);

                    if(!isset($time_2[1])){
                        $time_2[1] = '00';
                    }

                    if($time_2[0] < 10){
                        $info['endTime'] = '0'.$time_2[0].':'.$time_2[1];
                    }else{
                        $info['endTime'] = $time_2[0].':'.$time_2[1];
                    }
                }

                $anchor_info = $anchor_mod->where('anchor_id',$info['anchor_id'])->find();

                if($anchor_info){
                    $info['anchor_name'] = $anchor_info['name'];
                    $info['anchor_data_id'] = $anchor_info['anchor_data_id'];
                    $info['anchor_img'] = $anchor_info['image_url'];
                }else{
                    $info['anchor_name'] = '';
                    $info['anchor_data_id'] = '';
                    $info['anchor_img'] = '';
                }

                // 获取当前用户打卡记录
                $map['notice_id'] = $id;
                $map['user_id'] = $user_id;
                $info['coin'] = $coin_mod->where($map)->find();
            }
        }

        return $info;
    }

}
