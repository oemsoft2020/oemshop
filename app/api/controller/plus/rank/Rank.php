<?php

namespace app\api\controller\plus\Rank;

use app\api\controller\Controller;
use app\api\model\plus\rank\ShareTeam as ShareteamModel;
  

/**
 * APi分享返利功能
 */

class Rank extends Controller
{
    // 个人团队详情
    public function teamDetail(){
        $postdata = $this->postData();
        $user_id = $postdata['user_id'];
        $teamModel = new ShareteamModel();
        $items = $teamModel->where('captain',$user_id)->field('values')->select();
    }
}
