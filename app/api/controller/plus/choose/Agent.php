<?php

namespace app\api\controller\plus\choose;

use app\api\controller\Controller;
use app\common\model\user\User as userModel;
use app\api\model\plus\article\Category as CategoryModel;
use app\api\model\plus\agent\User as userModel1;
use app\common\model\plus\agent\Referee as RefereeModel;
use app\common\model\plus\agent\User as AgentModel;
use app\api\model\plus\choose\Agent as AgentUserModel;




/**
 * 选择分销商
 */
class Agent extends Controller
{

    /**
     * 选择供应商列表
     */
    public function index($category_id = 0)
    {
        $model = new userModel;
        //查询当前用户id跟当前用户的referee_id，这两个用户id的人不显示在列表中
        $user = $this->getuser();

        $id = $model->where('user_id','=',$user['user_id'])->field('referee_id')->find();

        $array[]=$user['user_id'];
        //当前代理商信息
        $AgentModel = new AgentModel();

        $userData = $AgentModel->where('user_id',$user['user_id'])->find();
        //如果是代理商-->true;
        if($userData){
            $userData = 1 ;
        }else{
            $userData = 2;
        }

        //当前代理商信息

//        $agent = $AgentModel->alias('agent')
//            ->join('user','agent.user_id=user.user_id')
//            ->where('agent.user_id','=',$id['referee_id'])
//            ->field('agent.user_id,agent.mobile,user.nickName,user.avatarUrl')->find();



        $agent = $model->alias('agent')

            ->join('agent_referee' ,'  agent_referee.agent_id = agent.user_id','left')

            ->where('agent_referee.user_id','=',$user['user_id'])

            ->join('agent_user',' agent.user_id = agent_user.user_id','left')

            ->field('agent.nickName, agent.avatarUrl,agent_user.real_name,agent_user.mobile,agent.user_id')

            ->find();



        $list = $AgentModel->alias('agent')

            ->join('user','agent.user_id=user.user_id')

            ->where('agent.user_id','<>',$agent['user_id'])

            ->order('user.user_id','desc')

            ->field('user.user_id,user.nickName, user.avatarUrl,agent.mobile,agent.real_name')

            ->paginate($this->getData(), false, [
                'query' => request()->request()
            ]);
        return $this->renderSuccess('', compact('list','agent','userData'));
    }



    /**
     * 修改供应商
     */

    public function choose(){
        // 当前用户信息
        $post = $this->getData();
        $user = $this->getUser();
        $userModel = new userModel();
        $RefereeModel = new RefereeModel();
        $res = $RefereeModel ->where('level',1)->where('user_id',$user['user_id'])->find();


        if(!$res){
//            halt(123);
            $array=[
                'user_id'=>$user['user_id'],
                'agent_id'=>$post['user_id'],//传过来的分销商ID
                'create_time'=>time(),//传过来的分销商ID
                'app_id'=>$post['app_id'],
                'level'=>1
            ];
            $RefereeModel->save($array);
            $AgentModel = new AgentUserModel();
            $AgentModel->setIncAgent($post['user_id']);
            //之前分销商用户表减少一级用户人数
            $AgentModel->setDecAgent($res['agent_id']);
        }else{
            //分销商关系表修改分销商关系
            $RefereeModel->where('user_id','=',$user['user_id'])->update(['agent_id'=>$post['user_id']]);
            //分销商用户表增加一级用户人数
            $AgentModel = new AgentUserModel();
            $AgentModel->setIncAgent($post['user_id']);
            //之前分销商用户表减少一级用户人数
            $AgentModel->setDecAgent($res['agent_id']);
        }
        //用户表修改推荐人
        $userModel->where('user_id','=',$user['user_id'])->update(['referee_id'=>$post['user_id']]);

        return $this->renderSuccess('', compact('data'));

    }



    /**
     * 选择供应商列表
     */
    public function index1()

    {
        $params = $this->getData();
        $model = new userModel1();
        $AgentModel = new AgentModel();
        $data = $AgentModel->alias('agent')
            ->join('user','agent.user_id=user.user_id')
            ->order('user_id','desc')
            ->field('user.user_id,user.nickName, user.avatarUrl,agent.mobile,agent.real_name')
            ->paginate($this->getData(), false, [
                'query' => request()->request()
            ]);




        return $this->renderSuccess('', compact('data'));

    }


    /**
     * 天气接口
     */
    public function tian(){
//        halt($this->getData());
        $data= $this->getData();
        $url="http://wthrcdn.etouch.cn/weather_mini?city=".$data['city'];
        $str = file_get_contents($url);  //调用接口获得天气数据
        //这一步很重要
        $result= gzdecode($str);   //解压
        //end
        $result = json_decode($result,true);
        $data = $this->today($result);
        return $this->renderSuccess('', compact('data'));
    }

    /**
     * 取出天气接口今天的数据
     */

    public function today($list){

        $data['high']=mb_substr($list['data']['forecast'][0]['high'],2,4);
        $data['low']= mb_substr($list['data']['forecast'][0]['low'],2,3);
        $data['type']= $list['data']['forecast'][0]['type'];
        $time = '';
        $time = date('m',time()).'月';
        $time .= date('d',time());
        $data['date'] = $time;
        $data['type'] = $this->str($data['type']);

        return $data;


    }

    /**
     * 取出天气接口今天天气的状态
     */
    public function str($str)
    {
        $a = '';


        foreach (mb_str_split($str) as $i=> $c)
        {

            if($c == '雨' || $c == '阴' || $c== '晴' ||  $c == '云' ||  $c == '雪')  {

                $a = $c;
                return $a;
            }

        }
        $a = '阴';
        return  $a;


    }



}