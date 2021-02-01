<?php

namespace app\common\model\order;
use app\common\model\BaseModel;
/**
 * 订单导入结算记录模型
 */
class ImportLog extends BaseModel
{
    protected $name = 'import_log';
    protected $pk = 'id';
    protected $updateTime = false;

    /**
     * 添加抖音订单导入结算记录
     * @Author   linpf
     * @DataTime 2020-11-24T16:08:45+0800
     * @param    integer                  $status [description]
     * @param    string                   $msg    [description]
     * @param    string                   $ext    [description]
     */
    public function addLog($status = 1,$msg = '',$ext = '',$type = 1)
    {
    	$addData['status'] = $status;
    	$addData['content'] = $msg;
    	$addData['ext'] = $ext;
    	$addData['create_time'] = time();
    	$addData['type'] = $type;
    	$addData['app_id'] = self::$app_id;

    	return $this->insert($addData);
    }

    /**
    * 订单导入记录列表
    */
    public function getList($params = array())
    {        
    	$model = $this;

    	if(!empty($params['type'])){
    		$model = $model->where('type',$params['type']);
    	}

    	if(!empty($params['status'])){
    		$model = $model->where('status',$params['status']);
    	}

    	if(!empty($params['order_no'])){
    		$model = $model->where('content',$params['order_no']);
    	}

        // 获取列表数据
        return $model->paginate($params, false, [
            'query' => \request()->request()
        ]);
    }
}