<?php

namespace app\api\controller\plus\card;

use app\api\controller\Controller;
use app\api\model\plus\card\RadarClient;
use app\api\model\plus\card\RadarFollow;
use app\api\model\plus\card\RadarLabel;
use app\api\model\plus\card\RadarLabelClient;
use app\common\model\plus\card\CardCount as CardCountModel;

class Client extends Controller
{

    /* 
    * 客户数据
    */
    public function getClientList()
    {
        $params  =  $this->postData();
        $clientModel = new RadarClient();
        $radarLabelModel = new RadarLabel();

        $user_info = $this->getUser();
        $list = $clientModel->getListByParams($params,$user_info['user_id']);
        $series = $clientModel->getSeriesData($user_info['user_id']);

        $Pie = [
            'series'=>$series,
        ];
        $starCount = $clientModel->getStarCount($user_info['user_id']);
        $tagsCount = $radarLabelModel->getTagsCount($user_info['user_id']);
        return $this->renderSuccess("成功", compact('list','Pie','starCount','tagsCount'));
    }

    /* 
    * 搜索记录
    */
    public function getRecordtList()
    {
        $params  =  $this->postData();
        $clientModel = new RadarClient();

        $user_info = $this->getUser();
        $list = $clientModel->getListByParams($params,$user_info['user_id']);
        return $this->renderSuccess("成功", compact('list'));
    }

    /* 
    * 标签数据
    */
    public function getLabelList()
    {
        $params  =  $this->postData();
        $labelModel = new RadarLabel();

        $user_info = $this->getUser();
        $labelList = $labelModel->getListByParams($params,$user_info['user_id']);

        return $this->renderSuccess("成功", compact('labelList'));
    }
    /* 
    * 获取标签详情
    */
    public function getLabelData()
    {
        $params  =  $this->postData();
        if(!isset($params['label_id'])||empty($params['label_id'])){
            return $this->renderError("标签ID不可为空");
        }
        $labelModel = new RadarLabel();
        $labelClientModel = new RadarLabelClient();

        $user_info = $this->getUser();
        $labelInfo = $labelModel->find($params['label_id']);
        $customList = $labelClientModel->getListByParams($params,$user_info['user_id']);

        return $this->renderSuccess("成功", compact('customList','labelInfo'));
    }

    /* 
    * 保存标签修改
    */

    public function saveLabelData()
    {
        $params  =  $this->postData();
        if(!isset($params['label_id'])){
            return $this->renderError("标签ID不可为空");
        }
        $labelModel = new RadarLabel();
        

        $user_info = $this->getUser();
        $labelInfo = $labelModel->find($params['label_id']);
        $data = [
            'name'=>$params['name'],
        ];
        $labelInfo->save($data);

        return $this->renderSuccess("成功", compact('labelInfo'));
        
    }


    /* 
    *  客户详情
    */

    public function getClientDetail($client_id)
    {
        if(empty($client_id)){
            return $this->renderError('获取client_id失败');
        }
        $radarClientModel = new  RadarClient();
        $user_info = $this->getUser();
        $clientInfo = $radarClientModel->getClient($client_id,$user_info['user_id']);
        
        return $this->renderSuccess('成功',compact('clientInfo'));
    }

    /* 
    * 设置成交时间
    */

    public function setClientDate($client_id)
    {

        if(empty($client_id)){
            return $this->renderError('获取client_id失败');
        }
        $params = $this->postData();
        if(!isset($params['deal_time'])||empty($params['deal_time'])){
            return $this->renderError('请先选择成交时间');
        }
        $radarClientModel = new  RadarClient();
        $radarFollowModel = new RadarFollow();
       
        $data = [
            'deal_time'=>strtotime($params['deal_time'])
        ];
        $user_info = $this->getUser();
        
        $clientInfo = $radarClientModel->edit($client_id,$user_info['user_id'],$data);
        $data = [
            'user_id'=> $user_info['user_id'],
            'c_uid'=> $clientInfo['c_uid'],
            'type'=>1,
            'content'=>"设置成交时间为：".$params['deal_time']
        ];
        $radarFollowModel->addFollow($data);
        return $this->renderSuccess('成功',compact('clientInfo'));
    }

    /* 获取浏览记录 */
    public function getCustomViewList($client_id)
    {

        $card_count  = new  CardCountModel();

        $data = $this->postData();

        $radarClientModel = new  RadarClient();
        $user_info = $this->getUser();
        $clientInfo = $radarClientModel->getClient($client_id,$user_info['user_id']);

        $viewList =  $card_count->timeStatis($user_info['user_id'],$data,$clientInfo['c_uid']);
        return  $this->renderSuccess('success', compact('viewList'));
    }

    /* 获取跟进记录 */

    public function getfollowViewList($client_id)
    {
        
        $radarFollowModel = new RadarFollow();

        $data = $this->postData();

        $radarClientModel = new  RadarClient();
        $user_info = $this->getUser();
        $clientInfo = $radarClientModel->getClient($client_id,$user_info['user_id']);

        $followList =  $radarFollowModel->getFollowList($user_info['user_id'],$clientInfo['c_uid']);
        return  $this->renderSuccess('success', compact('followList'));
    }

    /* 
    *保存跟进记录
    */
    public function saveFollow($client_id)
    {
        $data = $this->postData();

        $radarClientModel = new  RadarClient();
        $radarFollowModel = new RadarFollow();

        $user_info = $this->getUser();
        $clientInfo = $radarClientModel->getClient($client_id,$user_info['user_id']);
        $content  = isset($data['content'])?$data['content']:'';
        $data = [
            'user_id'=> $user_info['user_id'],
            'c_uid'=> $clientInfo['c_uid'],
            'type'=>1,
            'content'=>$content
        ];
        $radarFollowModel->addFollow($data);
        return  $this->renderSuccess('记录新增成功');
    }

    /* 
    *客户操作
    */
    public function customOperate($client_id)
    {
        $data = $this->postData();

        if(empty($client_id)){
            return  $this->renderError('找不到该客户');
        }

        $radarClientModel = new  RadarClient();
        $radarFollowModel = new RadarFollow();

        $user_info = $this->getUser();
        $clientInfo = $radarClientModel->getClient($client_id,$user_info['user_id']);
        if(empty($clientInfo)){
            return  $this->renderError('找不到该客户');
        }
        $clientInfo->save($data);

        $content  = isset($data['content'])?$data['content']:'';
        $data = [
            'user_id'=> $user_info['user_id'],
            'c_uid'=> $clientInfo['c_uid'],
            'type'=>1,
            'content'=>$content
        ];
        $radarFollowModel->addFollow($data);
        return  $this->renderSuccess('操作成功');
    }

    /* 
    *  编辑客户信息
    */
    public function editClient($client_id)
    {
        $data = $this->postData();


        $radarClientModel = new  RadarClient();
        $radarFollowModel = new RadarFollow();

        $user_info = $this->getUser();

        
        $clientInfo = $radarClientModel->edit($client_id,$user_info['user_id'],$data);
        $data = [
            'user_id'=> $user_info['user_id'],
            'c_uid'=> $clientInfo['c_uid'],
            'type'=>1,
            'content'=>"编辑客户信息"
        ];

        $radarFollowModel->addFollow($data);
        return $this->renderSuccess('成功',compact('clientInfo'));
    }

    /* 
    * 获取星标客户
    */
    public function getStarClient()
    {
        $params  =  $this->postData();
    
        $labelClientModel = new RadarLabelClient();

        $user_info = $this->getUser();
        
        $params['is_star'] =1;

        $customList = $labelClientModel->getListByParams($params,$user_info['user_id']); 
        return $this->renderSuccess('成功',compact('customList'));
    }

    /* 
    *  名片标签
    */
    public function getAllTags($client_id)
    {
        $radarLabelModel = new RadarLabel();
        $radarLabelClientModel = new RadarLabelClient();

        $user_info = $this->getUser();
    
        $tagsList = $radarLabelModel->getAllTags($user_info['user_id']); 
        $lists = $radarLabelClientModel->getClientTags($client_id,$user_info['user_id']);
        return $this->renderSuccess('成功',compact('tagsList','lists'));
    }

    /* 
    * 选择添加标签
    */
    public function selectClick($client_id)
    {

        $data  =  $this->postData();

        $radarLabelModel = new RadarLabel();
        $radarLabelClientModel = new RadarLabelClient();

        if(!isset($data['radar_label_id'])||empty($data['radar_label_id'])){

            return $this->renderError('请选择标签');
        }
        if(empty($client_id)){
            return $this->renderSuccess('请选择客户');
        }

        $type = isset($data['type'])?$data['type']:'add';

        $user_info = $this->getUser();

        $res =  $radarLabelClientModel->operateLabelClient($user_info['user_id'],$client_id,$data['radar_label_id'],$type);
        if($res){
           
            $radarLabelModel->updateLabelCount($data['radar_label_id'],$user_info['user_id']);
        }
        
        return $this->renderSuccess('成功');
    }

    /* 
    * 新增标签
    */

    public function addLabel($client_id)
    {
        $data  =  $this->postData();

        $radarLabelModel = new RadarLabel();
        $radarLabelClientModel = new RadarLabelClient();

        if(empty($client_id)){
            return $this->renderSuccess('请选择客户');
        }
        if(empty($data['name'])){
            return $this->renderSuccess('请输入标签名称');
        }

        $user_info = $this->getUser();
        $params = [
            'user_id'=>$user_info['user_id'],
            'name'=>isset($data['name'])?$data['name']:''
        ];
        $radar_label_id = $radarLabelModel->addLabel($params);
        if(empty($radar_label_id)){
            return $this->renderSuccess('标签添加失败');
        }
        $res =  $radarLabelClientModel->operateLabelClient($user_info['user_id'],$client_id,$radar_label_id);
        if($res){
           
            $radarLabelModel->updateLabelCount($radar_label_id,$user_info['user_id']);
        }
        
        return $this->renderSuccess('成功'); 
    }
    
}
