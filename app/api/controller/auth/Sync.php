<?php

namespace app\api\controller\auth;

use app\api\controller\Controller;
use app\shop\model\shop\Access;
use app\KmdController;

class Sync extends KmdController
{
    public function syncAccess()
    {
        $accessModel =  new Access();
        $list = $accessModel->getList();
        return $this->renderSuccess('success',compact('list'));
    }

}