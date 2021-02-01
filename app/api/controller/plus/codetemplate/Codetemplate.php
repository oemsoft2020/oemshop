<?php

namespace app\api\controller\plus\codetemplate;

use app\api\controller\Controller;
use app\api\model\plus\codetemplate\CodeTemplate as CodeTemplateModel;
use app\api\model\plus\storage\Storage;

/**
 * 二维码模板控制器
 */
class Codetemplate extends Controller
{
    /**
     * 二维码模板列表
     */
    public function index()
    {
        $model = new CodeTemplateModel;
        $list = $model->getList($this->getData());
        return $this->renderSuccess('', compact('list'));
    }

    public function detail($code_template_id)
    {
        $detail = CodeTemplateModel::detail($code_template_id);
        return $this->renderSuccess('', compact('detail'));
    }

    public function getDetail($storage_id,$code_template_id)
    {
        $storageModel = new Storage();
        $storage = $storageModel::storagedetail($storage_id);
        $detail = CodeTemplateModel::detail($code_template_id);
        return $this->renderSuccess('',compact('detail','storage'));

    }

}