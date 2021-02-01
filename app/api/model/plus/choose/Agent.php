<?php

namespace app\api\model\plus\choose;

use app\common\model\plus\agent\User as AgentModel;
use think\facade\Db;

use app\common\model\BaseModel;


/**
 * 选择分销商model
 */
Class Agent extends AgentModel{

    /**
     * 变更代理商后。增加代理商的一级会员数量
     */
    public function setIncAgent($user_id){

        $data=  $this->where('user_id',$user_id)->inc('first_num',1)->update();

    }
    /**
     * 变更代理商后。减少之前代理商的一级会员数量
     */
    public function setDecAgent($user_id){

        $data=  $this->where('user_id',$user_id)->dec('first_num',1)->update();

    }


}
