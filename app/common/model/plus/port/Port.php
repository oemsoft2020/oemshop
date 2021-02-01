<?php

namespace app\common\model\plus\port;

use app\common\exception\BaseException;
use think\facade\Cache;
use app\common\model\BaseModel;

/**
 * 端口模型
 */
class Port extends BaseModel
{
    protected $name = 'port';
    protected $pk = 'port_id';


}
