<?php
namespace app\api\model\plus\sharepolite;

use app\common\model\plus\sharepolite\ShareList as ShareListModel;

class ShareList extends ShareListModel {
    public function getMoney( $share_list_id ) {
        // 获取该分享商品参数( 返利额度 )
        $setting =  $this->where( 'share_list_id', $share_list_id )->field( 'setting' )->find();
        $money = json_decode( $setting['setting'], true );
        // 返利金额范围
        $first_money = [$money['reward_min_first'], $money['reward_max_first']];
        $second_money = [$money['reward_min_second'], $money['reward_max_second']];
        $first_reward = $this->rewardArea( $first_money );
        $second_reward = $this->rewardArea( $second_money );
        
        return [
            'first_reward'=>$first_reward,
            'second_reward'=>$second_reward,
        ];
    }

    // 生成指定范围的金额

    public function rewardArea( $val ) {
        $type = '';
        foreach ( $val as $k => $v ) {
            if ( is_float( $v ) ) {
                $type = 'float';
                $val[$k] = $v * 100;
            }
        }
        if ( $type == 'float' ) {
            $reward_val = mt_rand( $val[0], $val[1] ) / 100;
        } else {
            $reward_val = mt_rand( $val[0], $val[1] );
        }
        return $reward_val;
    }
}