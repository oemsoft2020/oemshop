<?php

namespace app\common\model\plus\port;

use app\common\exception\BaseException;
use think\facade\Cache;
use think\Model;

/**
 * 版本端口模型
 */
class PortVersion extends Model
{
    protected $name = 'port_version';
    protected $pk = 'port_version_id';


}
