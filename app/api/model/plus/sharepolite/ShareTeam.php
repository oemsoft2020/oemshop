<?php

namespace app\api\model\plus\sharepolite;

use app\common\model\plus\sharepolite\ShareTeam as ShareTeamModel;

class ShareTeam extends ShareTeamModel
 {
    public function joinTeam( $captain, $member, $grade_diff, $app_id ) {
        $team_list = $this->where( 'captain', $captain )->find();
        if ( !$team_list ) {
            $where = [
                'captain' => $captain,
                'app_id'=>$app_id,
                'values' => $member . '-' . $grade_diff . '-' . date( 'Y/m/d', time() ) . ';',
            ];
            $res = $this->insert( $where );
        } else {
            // 检测是否已成为该团成员
            $exp_res = explode( ';', $team_list['values'] );
            foreach ( $exp_res as $k => $v ) {
                $arr = explode( '-', $v );
                if ( $arr[0] == $member ) {
                    return $res = 3;
                }
            }

            $values = $team_list['values'];
            $str = $member . '-' . $grade_diff . '-' . date( 'Y/m/d', time() ) . ';';
            $values = $values . $str;
            $where = [
                'captain'=> $captain,
                'app_id'=> $app_id,
            ];
            $res = $this->where( $where )->update( ['values' => $values] );
        }
        return $res;
    }

    // 递归查询上级id，形成他的队员

    public function joinAllTeam( $last_record_id, $member, $app_id, &$array = [], $grade_diff = 2 ) {
        $sharerecord = new \app\api\model\plus\sharepolite\ShareRecord;
        
        $sharerecord_res = $sharerecord->where( 'share_record_id', $last_record_id )->field( 'sharer_id,last_record_id' )->find();
        $captain = $sharerecord_res['sharer_id'];
        $captain_res = $this->where( 'captain', $captain )->find();
        if ( $captain_res ) {
            $arr = ( explode( ';', $captain_res['values'] ) );

            foreach ( $arr as $k => $v ) {
                if ( explode( '-', $v )[0] == $member ) {
                    $member_exits = 1;
                }
            }

            if ( isset( $member_exits ) && $member_exits == 1 ) {
                return 0;
            }
            $newMember = $member . '-' . $grade_diff . '-' . date( 'Y/m/d', time() ) . ';';
            $values = $captain_res['values'] . $newMember;
            $where = [
                'captain' => $captain,
                'app_id' => $app_id,
            ];
            $this->where( $where )->update( ['values' => $values] );
        } else {
            $where = [
                'captain' => $captain,
                'values' => $member . '-' . $grade_diff . '-' . date( 'Y/m/d', time() ) . ';',
                'app_id' => $app_id,
            ];
            $this->insert( $where );
        }

        if ( $sharerecord_res['last_record_id'] == 0 ) {
            return $array;
        }
        $grade_diff = $grade_diff + 1;
        $this->joinOtherTeam( $sharerecord_res['last_record_id'], $member, $app_id, $array, $grade_diff );
    }
}
