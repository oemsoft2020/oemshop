<?php

namespace app\api\controller\plus\sharepolite;

use app\api\controller\Controller;
use app\api\model\plus\sharepolite\ShareRecord;
use app\api\model\user\User;

/**
 * 红包管理
 */

class Redpage extends Controller
{
    public function myRedPage()
    {
        $postdata = $this->postData();

        // 分享红包
        $shareRecord = new ShareRecord;
        $user = new User;
        $where = [
            ['sharer_id|father_id', '=', $postdata['user_id']]
        ];

        $share_redpage = $shareRecord->alias('sharerecord')->join('share_reward_type reward', 'sharerecord.reward_type=reward.reward_type_id')->field('sharerecord.reward_type,reward.name as redpagetype,sharerecord.father_id,sharerecord.sharer_id,sharerecord.reader_id,sharerecord.promoter_money,interlocutor_money,sharerecord.create_time')->where($where)->select();
        $data = [];
        foreach ($share_redpage as $k => $v) {
            $user_info =  $user->alias('user')->where('user_id', $v['reader_id'])->join('user_grade grade', 'user.grade_id=grade.grade_id')->field('grade.name as gradename,user.nickName,user.avatarUrl')->find();
            $data[$k]['nickName'] = $user_info['nickName'];
            $data[$k]['avatarUrl'] = $user_info['avatarUrl'];
            $data[$k]['gradename'] = $user_info['gradename'];
            $data[$k]['money'] =  $v['sharer_id'] == $postdata['user_id'] ? number_format($v['promoter_money'], 2) : number_format($v['interlocutor_money'], 2);
            $data[$k]['create_time'] =  $v['create_time'];
            $data[$k]['redpagetype'] =  $v['redpagetype'];
        }
        // 代理红包

        // 佣金红包

        // 总金额

        // 总数量
        return $this->renderSuccess('红包信息查询成功', compact('data'));
    }
}
