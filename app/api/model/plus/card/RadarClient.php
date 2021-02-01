<?php

namespace app\api\model\plus\card;

use app\common\model\plus\card\RadarClient as RadarClientModel;
use app\api\model\user\User;
use app\api\model\plus\card\Card as MingpianCard;
use app\common\model\plus\card\CardCount;
use think\facade\Db;

class RadarClient extends RadarClientModel
{
    /* 
    * 新增客户
    */
    public function addClient($uid, $obj_uid)
	{   
        if(!$obj_uid||!$uid){
            return false;
        }
		$ori = $this->where(array('user_id' => $uid, 'c_uid' => $obj_uid))->find();
		$res = 1;

		if (!$ori) {
			$userModel = new User();
			$user = $userModel->where(array('user_id' => $obj_uid))->find();
			$nick_name = isset($user['nickName']) && $user['nickName'] ? $user['nickName'] : '';
			$person_avatar = isset($user['avatarUrl']) && $user['avatarUrl'] ? $user['avatarUrl'] : '';
			$sex = isset($user['gender']) && $user['gender'] ? $user['gender'] : '';
			$tel = '';
			$company_name = '';
			$email = '';
			$position = '';
			$address = '';
			$latitude = 0;
            $longitude = 0;
            $supply_id = 0;
            $cardModel = new MingpianCard();
            $card_info = $cardModel->getSelfCard($obj_uid);
			
			if ($card_info) {
                $card =  $cardModel->detail($card_info['card_id']);
                $tel = $card['mobile'];
                if(isset($card['supply'])&&empty($card['supply'])){
                    $company_name = $card['supply']['name'];
                    $address = $card['supply']['address'];
                    $latitude = $card['supply']['latitude'];
                    $longitude = $card['supply']['longitude'];
                    $supply_id = $card['supply']['supply_id'];
                }
			}
			$data = array('user_id' => $uid, 'app_id'=>$this::$app_id,'c_uid' => $obj_uid, 'status' => 1, 'nick_name' => $nick_name, 'person_avatar' => $person_avatar, 'come_from' => '搜索', 'tel' => $tel, 'sex' => $sex, 'company_name' => $company_name, 'deal_time' => strtotime('+10 day'), 'deal_rate' => 0.59999999999999998, 'email' => $email, 'position' => $position, 'address' => $address, 'latitude' => $latitude, 'longitude' => $longitude,'supply_id'=>$supply_id);
			$res = $this->save($data);
		}

		return $res;
    }

    public function getListByParams($params,$user_id='')
    {   
        $model = $this;
        $where = [];
        if($user_id){
            $where = [
                ['user_id','=',$user_id]
            ];
        }

        if(isset($params['keywords'])&&!empty($params['keywords'])){

            $where[]= array('nick_name','like', '%' . $params['keywords'] . '%');
            $model =   $model->where($where);
        }
        if(isset($params['status'])&&!empty($params['status'])){
            
            $where[] =  ['status','=',$params['status']];
            $model =   $model->where($where);
        }

        if(!empty($params['supply_id'])){
            $where[] =  ['supply_id','=',$params['supply_id']];
            $model =   $model->where($where);
        }

        $list = $model->where($where)->paginate(15, false, [
                'query' => request()->request()
            ]);
        
        $cardCountModel =  new CardCount();
        foreach ($list as $key => &$item) {
            $where  = [
                'to_user_id'=>$user_id,
                'user_id'=>$item['user_id']
            ];
            $item['count'] =  $cardCountModel->where($where)->count();
        }
        unset($item);
        return $list;
    }

    public function getSeriesData($user_id='',$params=[])
    {
        $model = $this;
        $where = [];
        if(!empty($user_id)){

            $where[] = ['user_id','=',$user_id];
        }
        
        if(!empty($params['supply_id'])){
            
            $where[] = ['supply_id','=',$params['supply_id']];

        }
        if(!empty($params['create_time'])){

            $where[] = ['create_time','>',$params['create_time']];
        }
        $where3 = $where2 = $where;

        $count1 = $model->where($where)->count();
   
        $where2[]  = ['status','=',2];
        $count2 = $this->where($where2)->count();

        $where3[]  = ['status','=',3];
        $count3 = $model->where($where3)->count();

        $series = [
            [
                'name'=>'总用户数',
                'data'=>$count1?$count1:0,
            ],
            [
                'name'=>'跟进数量',
                'data'=>$count2?$count2:0,
            ],
            [
                'name'=>'成交数量',
                'data'=>$count3?$count3:0
            ]
            ];
        return $series;
    }

    /* 通过客户id获取客户 */
    public function getClient($client_id,$user_id)
    {
        $clientInfo = $this->find($client_id); 

        $radarLabelClientModel = new RadarLabelClient();
        $clientInfo['labelList'] =  $radarLabelClientModel->getLabelList($client_id,$user_id);
        
        return $this->formatData($clientInfo);
    }

    public function edit($client_id,$user_id,$params)
    {
        $clientInfo = $this->getClient($client_id,$user_id); 

        $clientInfo->save($params);
            
        return $this->formatData($clientInfo);
    }

    public function formatData($clientInfo)
    {
        if($clientInfo['status']==1){
            $clientInfo['value1']= '进行中';
        }elseif($clientInfo['status']==2){
            $clientInfo['value1'] = '跟进中';
        }elseif($clientInfo['status']==3){
            $clientInfo['value1'] = '已成交';
        }

        $clientInfo['date'] =0;
        $clientInfo['year']  =0;
        $clientInfo['month']  = 0;
        $clientInfo['day']  = 0;
        if(isset($clientInfo['deal_time'])&&!empty($clientInfo['deal_time'])){
            $clientInfo['date'] = date('Y-m-d',$clientInfo['deal_time']);
            $clientInfo['year']  = date('Y',$clientInfo['deal_time']);
            $clientInfo['month']  = date('m',$clientInfo['deal_time']);
            $clientInfo['day']  = date('d',$clientInfo['deal_time']);
        }

        return $clientInfo;
    }

    /* 
    * 获取星标客户数目
    */
    public function getStarCount($user_id)
    {   
        $where = [
            'user_id'=>$user_id,
            'is_star'=>1,
        ];
        $count = $this->where($where)->count();
        return $count?$count:0;
    }

    /* 通过uid获取客户 */
    public function getClientByUid($user_id,$c_uid)
    {
        $where =  [
            'user_id'=>$user_id,
            'c_uid'=>$c_uid
        ];
        $clientInfo = $this->where($where)->find(); 

        return $clientInfo;
    }

    /* 
    * 获取客户数目
    */
    public function getClientCount($params)
    {   

        $where = [];

        if(!empty($params['supply_id'])){

            $where[] = ['supply_id','=',$params['supply_id']];
        }

        if(!empty($params['create_time'])){

            $where[] = ['create_time','>',$params['create_time']];
        }

        if(!empty($params['status'])){

            $where[] = ['status','=',$params['status']];
        }
        $count = $this->where($where)->count();
        return $count?$count:0;
    }


    
}