<?php

namespace app\api\model\plus\sharepolite;

use app\common\model\plus\sharepolite\ShareRecord as ShareRecordModel;

class ShareRecord extends ShareRecordModel {
    public function getJoinMember( $share_list_id ) {
        $where = [];
        $where = [
            ['share_list_id', '=', $share_list_id],
            ['promoter_id', '<>', 0],
        ];

        $record_res = $this->where( $where )->select()->toArray();
        if ( !$record_res ) {
            return $data = [
                'total'=>0,
                'member'=>0,
            ];
        }

        // 获取该商品所有成员
        foreach ( $record_res as $k=>$v ) {
            $res['member'][] = $v['sharer_id'];
            $res['member'][] = $v['reader_id'];
        }
        $res['member'] = array_unique( $res['member'] );
        foreach ( $res['member'] as $k=>$v ) {
            $user = new \app\api\model\user\User;
            $user_info = $user->where( 'user_id', $v )->field( 'nickName,avatarUrl' )->find();

            $data['member'][$k]['nickName'] = $user_info['nickName'];
            $data['member'][$k]['avatarUrl'] = $user_info['avatarUrl'];
        }

        $data['total'] = count( $data['member'] )?count( $data['member'] ): 0;
        return $data;
    }

    public function moneyTobalance( $user_id, $new_money ) {
        $user = new \app\api\model\user\User;
        $where = [
            'user_id'=>$user_id,
        ];
        $user_info = $user->where( $where )->field( 'balance' )->find();

        $balance = $user_info['balance'];
        $new_balance = $balance + $new_money;
     
        $res = $user->where( $where )->update( ['balance'=> $new_balance] );
        if ( $res ) {
            return $data = '用户红包成功返到余额';
        } else {
            return $data = '用户红包返到余额失败';
        }
    }
}
