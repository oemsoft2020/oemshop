<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/4
 * Time: 0:47
 */

namespace app\common\model\plus\signadv;


use app\common\model\BaseModel;

class Signadv extends BaseModel
{
    protected $name = 'kmd_signin_adv';
    protected $pk = 'id';

    public function getCreatedAttr($value){
        return date('Y-m-d H:i:s',(int)$value);
    }

    /**
     * 错误信息
     * @param string $msg
     * @param array $data
     * @param string $type
     * @return array
     */
    public function errorFault($msg='',$data=[],$type='error'){
        return ["code" => -1,"msg" => $msg,"type" => $type,'data' => $data];
    }

    /**
     * 成功信息
     * @param string $msg
     * @param array $data
     * @param string $type
     * @return array
     */
    public function successCorrect($msg='',$data=[],$type='success'){
        return ["code" => 0,"msg" => $msg,"type" => $type,'data' => $data];
    }
}