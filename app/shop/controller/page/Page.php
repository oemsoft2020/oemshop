<?php

namespace app\shop\controller\page;

use app\shop\controller\Controller;
use app\shop\model\page\Page as PageModel;
use app\shop\model\page\PageCategory as PageCategoryModel;
use think\facade\Db;
/**
 * App页面管理
 */
class Page extends Controller
{
    /**
     * 页面列表
     */
    public function index()
    {
        $model = new PageModel;
        $list = $model->getList($this->postData());
        return $this->renderSuccess('', compact('list'));
    }

    /**
     * 新增页面
     */
    public function add()
    {
        $model = new PageModel;
        if ($this->request->isGet()) {
            return $this->renderSuccess('', [
                'defaultData' => $model->getDefaultItems(),
                'jsonData' => ['page' => $model->getDefaultPage(), 'items' => []],
                'opts' => [
                    'catgory' => [],
                ]
            ]);
        }
        // 接收post数据
        $post = $this->postData();
        if (!$model->add($post['Parmens'])) {
            return $this->renderError($model->getError() ?:'添加失败');
        }
        return $this->renderSuccess('添加成功');
    }

    /**
     * 首页编辑
     * @return \think\response\Json
     */
    public function home(){
        return $this->edit();
    }
    /**
     * 编辑页面
     */
    public function edit($page_id = null)
    {
        $model = $page_id > 0 ? PageModel::detail($page_id) : PageModel::getHomePage();
        if ($this->request->isGet()) {
            $jsonData = $model['page_data'];
            jsonRecursive($jsonData);
            return $this->renderSuccess('', [
                'defaultData' => $model->getDefaultItems(),
                'jsonData' => $jsonData,
                'opts' => [
                    'catgory' => [],
                ],
                'isempty'=>empty($jsonData)?1:0
            ]);
        }

        // 接收post数据
        $post = $this->postData('Parmens');
        if (!$model->edit($post)) {
            return $this->renderError($model->getError() ?:'更新失败');
        }
        return $this->renderSuccess('更新成功');
    }

    /**
     * 删除页面
     */
    public function delete($page_id)
    {
        // 帮助详情
        $model = PageModel::detail($page_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?:'删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 分类模板
     */
    public function category()
    {
        $model = PageCategoryModel::detail();
        if($this->request->isGet()){
            return $this->renderSuccess('', compact('model'));
        }
        if ($model->edit($this->postData())) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /* 
    * 用户界面编辑
    */
    public function user()
    {
        $model = new PageModel;
        if ($this->request->isGet()) {
            $page_info = $model->where('page_type', '30')->where('is_delete',0)->find();
            $jsonData = $page_info['page_data'];
            if($jsonData){
                jsonRecursive($jsonData);
            }
            return $this->renderSuccess('', [
                'defaultData' => $model->getDefaultItems(),
                'jsonData' =>$jsonData?$jsonData: ['page' => $model->getDefaultPage(), 'items' => []],
                'opts' => [
                    'catgory' => [],
                ],
                'isempty'=>empty($jsonData)?1:0
            ]);
        }
        // 接收post数据
        $post = $this->postData();
        if (!$model->editUser($post['Parmens'])) {
            return $this->renderError($model->getError() ?:'添加失败');
        }
        return $this->renderSuccess('添加成功');
    }
    /* 
    * 用户界面编辑
    */
    public function supply()
    {
        $model = new PageModel;
        if ($this->request->isGet()) {
            $page_info = $model->where('page_type', '50')->where('is_delete',0)->find();
            $jsonData = $page_info['page_data'];
            if($jsonData){
                jsonRecursive($jsonData);
            }
            return $this->renderSuccess('', [
                'defaultData' => $model->getDefaultItems(),
                'jsonData' =>$jsonData?$jsonData: ['page' => $model->getDefaultPage(), 'items' => []],
                'opts' => [
                    'catgory' => [],
                ],
                'isempty'=>empty($jsonData)?1:0
            ]);
        }
        // 接收post数据
        $post = $this->postData();
        if (!$model->editSupply($post['Parmens'])) {
            return $this->renderError($model->getError() ?:'添加失败');
        }
        return $this->renderSuccess('添加成功');
    }
    
    /* 
    *底部菜单编辑
    */
    public function tabbar()
    {
        $model = new PageModel;
        $base_url  = base_url();
        $defaultData  = ['tabBar' => [
            'name' => '底部菜单',
            'type' => 'tabBar',
            'style' => ['background' => '#ffffff', 'rowsNum' => 4],
            'data' => [
                [
                    'imgUrl' => $base_url . 'image/diy/navbar/01.png',
                    'selectImgUrl'=>$base_url . 'image/diy/navbar/01.png',
                    'imgName' => 'icon-1.png',
                    'linkUrl' => '',
                    'text' => '底部菜单1',
                    'color' => '#666666'
                ],
                [
                    'imgUrl' => $base_url . 'image/diy/navbar/02.png',
                    'selectImgUrl'=>$base_url . 'image/diy/navbar/02.png',
                    'imgName' => 'icon-2.jpg',
                    'linkUrl' => '',
                    'text' => '底部菜单2',
                    'color' => '#666666'
                ],
                [
                    'imgUrl' => $base_url . 'image/diy/navbar/03.png',
                    'selectImgUrl'=>$base_url . 'image/diy/navbar/03.png',
                    'imgName' => 'icon-3.jpg',
                    'linkUrl' => '',
                    'text' => '底部菜单3',
                    'color' => '#666666'
                ],
                [
                    'imgUrl' => $base_url . 'image/diy/navbar/04.png',
                    'selectImgUrl'=>$base_url . 'image/diy/navbar/04.png',
                    'imgName' => 'icon-4.jpg',
                    'linkUrl' => '',
                    'text' => '底部菜单4',
                    'color' => '#666666'
                ]
            ]
        ]];
        if ($this->request->isGet()) {
            $page_info = $model->where('page_type', '40')->where('is_delete',0)->find();
            $jsonData = $page_info['page_data'];
            if($jsonData){
                jsonRecursive($jsonData);
            }
            return $this->renderSuccess('', [
                'defaultData' => $defaultData,
                'jsonData' =>$jsonData?$jsonData: ['page' => $model->getDefaultPage(), 'items' => []],
                'opts' => [
                    'catgory' => [],
                ],
                'isempty'=>empty($jsonData)?1:0
            ]);
        }
        // 接收post数据
        $post = $this->postData();
        if (!$model->editTabbar($post['Parmens'])) {
            return $this->renderError($model->getError() ?:'添加失败');
        }
        return $this->renderSuccess('添加成功');
    }

    /* 
    * 微官网页面
    * page_type =50 官网
    */
    public function website()
    {
        $model = new PageModel;
        if ($this->request->isGet()) {
          
            $page_info =  $model->getWebSitePage();
            $jsonData = $page_info['page_data'];
            if($jsonData){
                jsonRecursive($jsonData);
            }
            return $this->renderSuccess('', [
                'defaultData' => $model->getDefaultItems(),
                'jsonData' =>$jsonData?$jsonData: ['page' => $model->getDefaultPage(), 'items' => []],
                'opts' => [
                    'catgory' => [],
                ],
                'isempty'=>empty($jsonData)?1:0
            ]);
        }
        // 接收post数据
        $post = $this->postData();
        if (!$model->editWebSite($post['Parmens'])) {
            return $this->renderError($model->getError() ?:'添加失败');
        }
        return $this->renderSuccess('添加成功');
    }
}
