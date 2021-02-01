<?php

namespace app\api\model\plus\rank;

use app\common\model\plus\rank\ShareTeam as ShareTeamModel;
use app\api\model\user\User;

class ShareTeam extends ShareTeamModel
{
    // 团队详细信息
    public function teamDetail($user_id)
    {
        return $this->teamMember($user_id);
    }

    // 团员和等级关系
    public function teamMember($user_id)
    {
        $item_result =  $this->where('captain', $user_id)->field('values')->find();
        $result_arr = explode(';', $item_result['values']);
        $gold_total = 0;
        $slider_total = 0;
        $data['data'] = [];
        foreach ($result_arr as $k => $v) {
            if (!empty($v)) {
                $arr = explode('-', $v);
                if ($arr[1] == 1) {
                    $gold_total += 1;
                } elseif ($arr[1] == 2) {
                    $slider_total += 1;
                }
                $data['data'][$k]['user_id'] = $arr[0];
                $data['data'][$k]['differential'] = $arr[1];
                $data['data'][$k]['create_time'] = $arr[2];
                $detail = $this->teamMemberIdentify($arr[0]);
                $data['data'][$k]['nickName'] = $detail['nickName'];
                $data['data'][$k]['avatarUrl'] = $detail['avatarUrl'];
                $data['data'][$k]['grade'] = $detail['grade'];
                $data['data'][$k]['fans_total'] = $this->teamMemberFans($arr[0]);
            }
        }
       
        $data['gold_total'] = $gold_total;
        $data['slider_total'] = $slider_total;
        $data['member_total'] = count($data['data']);
        return $data;
    }

    // 查询团员信息
    public function teamMemberIdentify($uid)
    {
        $user = new User;
        $user_info =  $user->alias('user')->join('user_grade grade', 'user.grade_id=grade.grade_id')->where('user_id', $uid)->field('user.avatarUrl,user.nickName,grade.name')->find();
        $info['grade'] = $user_info['name'];
        $info['nickName'] = $user_info['nickName'];
        $info['avatarUrl'] = $user_info['avatarUrl'];
        return $info;
    }

    // 查询团员粉丝
    public function teamMemberFans($user_id)
    {
        $item_result =  $this->where('captain', $user_id)->field('values')->find();
        if (!$item_result) return $fans_total = 0;
        $res = explode(';', $item_result['values']);
        foreach($res as $k=>$v){
            if(empty($v)){
                unset($res[$k]);
            }
        }
        $fans_total = count($res);
        return $fans_total;
    }

    /**
     *  查询用户等级粉丝总量
     * 
     */
    public function queryGradTeam($uid, $grade)
    {
        $item_result = $this->teamMember($uid);
        $value = $grade == 'gold' ? 1 : 2;
        $data = [];
        
        foreach ($item_result['data'] as $k => $v) {
            if ($v['differential'] == $value) {
                $data[$k] = $v;
            }
        }
        return $data;
    }

    public function queryIdentify($uid, $identify)
    {
        $item_result = $this->teamMember($uid);
        $data = [];

        if($identify == '全部'){
            return $item_result['data'];
        }

        foreach ($item_result['data'] as $k => $v) {
            if ($v['grade'] == $identify) {
                $data[$k] = $v;
            }
        }
        return $data;
    }
}
