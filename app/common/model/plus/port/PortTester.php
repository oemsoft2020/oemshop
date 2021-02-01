<?php

namespace app\common\model\plus\port;

use app\common\exception\BaseException;
use think\facade\Cache;
use app\common\model\BaseModel;

/**
 * 体验者模型
 */
class PortTester extends BaseModel
{
    protected $name = 'port_tester';
    protected $pk = 'port_tester_id';


}
