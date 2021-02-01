<?php

namespace app\api\controller\plus\rank;

use app\api\controller\Controller;
use app\api\model\plus\rank\ShareTeam as ShareteamModel;

/**
* 我的团队
*/

class Team extends Controller
 {
    // 团队详情

    public function teamDetail()
 {
        $postdata = $this->postData();
        $user_id = $postdata['user_id'];
        $teamModel = new ShareteamModel();
        $teamDetail = $teamModel->teamDetail( $user_id );
        $fans_total = array_column( $teamDetail['data'], 'fans_total' );
        array_multisort( $fans_total, SORT_DESC, $teamDetail['data'] );

        return $this->renderSuccess( '查询团队成员成功', compact( 'teamDetail' ) );
    }

    public function queryGradTeam() {
        $teamModel = new ShareteamModel();
        $postdata = $this->postData();
        $data = $teamModel->queryGradTeam( $postdata['user_id'], $postdata['grade'] );
        $fans_total = array_column( $data, 'fans_total' );
        array_multisort( $fans_total, SORT_DESC, $data );

        return $this->renderSuccess( '查询团队成员成功', compact( 'data' ) );
    }

    public function queryIdentify() {
        $teamModel = new ShareteamModel();
        $postdata = $this->postData();
        $data = $teamModel->queryIdentify( $postdata['user_id'], $postdata['identify'] );
        $fans_total = array_column( $data, 'fans_total' );
        array_multisort( $fans_total, SORT_DESC, $data );
        
        return $this->renderSuccess( '查询团队成员成功', compact( 'data' ) );

    }
}
