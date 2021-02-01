<?php

namespace app\shop\model\plus\transfer;

use app\common\model\plus\transfer\Transfer as TransferModel;

/**
 * 赠送模型
 */
class Transfer extends TransferModel
{
    /**
     * 获取赠送列表
     */
    public function getList($params)
    {

          return  $this->where('is_delete', '=', 1)
            ->order([ 'time' => 'desc','transfer_id'=>'desc'])
            ->paginate($params, false, [
                'query' => request()->request()
            ]);

    }
    /**
     * 获取赠送记录，一条赠送记录，一条接收记录
     */

    public function add($data= array())
    {
        $data['app_id'] = 10001;
        $data['user_id'] = 111;
        $data['time'] = time();
        $data['status'] = 1;
       $result = $this->insert($data);

        if($result) {


          $id=  $this->getLastInsID();

            $data['status'] =2;
            $data['pid'] =$id;
            return $this->insert($data);
        }


        return $this->save($data);



    }

    /*
     *赠送记录详情
     * **/
    public static function detail($article_id)
    {
        return self::find($article_id);
    }

    /*
         *删除记录详情
         * **/
    public  function delect($article_id)
    {
      $result =  self::find($article_id);
//      return  $result;


    }


    /**
     * 软删除
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 2]);
    }

    /**
     * 编辑
     */
    public function edit($data)
    {
        return $this->save($data);
    }


}