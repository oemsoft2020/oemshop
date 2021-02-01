<?php

namespace app\admin\controller;


use app\admin\model\Access as AccesscModel;
use think\facade\Db;
use think\facade\Env;

/**
 * 商家用户权限控制器
 */
class Access extends Controller
{
    /**
     * 权限列表
     */
    public function index()
    {
        $model = new AccesscModel;
        $list = $model->getList();
        return $this->renderSuccess('', $list);
    }

    /**
     * 添加权限
     */
    public function add()
    {
        $model = new AccesscModel;
        $data = $this->postData();

        if ($model->add($data)) {
            return $this->renderSuccess('添加成功', compact('model'));
        }
        return $this->renderError($model->getError() ?:'添加失败');
    }

    /**
     * 更新权限
     */
    public function edit()
    {
        $data = $this->postData();
        // 权限详情
        $model = AccesscModel::detail($data['access_id']);
        // 更新记录
        if ($model->edit($data)) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?:'更新失败');
    }

    /**
     * 删除权限
     */
    public function delete($access_id)
    {
        $model = new  AccesscModel();
        $num = $model->getChildCount(['parent_id' => $access_id]);
        if ($num > 0) {
            return $this->renderError('当前菜单下存在子权限，请先删除');
        }
        if ($model->remove($access_id)) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?:'删除失败');
    }

    /**
     * 权限状态
     */
    public function status($access_id, $status)
    {
        $model = AccesscModel::detail($access_id);
        if ($model->status($status)) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?:'修改失败');
    }

    /* 
    * 同步菜单
    */

    public function syncAccess()
    {   
        $data = $this->postData();
        if(empty($data['url'])){
            return $this->renderError('请求链接不可为空'); 
        }
        $baseurl = base_url();
        if($data['url']==$baseurl){
            return $this->renderError('同步地址与当前服务器相同,无法同步'); 
        }
        $url = $data['url']."index.php/api/auth.sync/syncAccess";
        $content = file_get_contents($url);
        $jsoncontent = json_decode($content,true);
        if($jsoncontent['code']==1){
            $lists = $jsoncontent['data']['list'];
             // 全新模式，清空数据表
            if($data['type']==1){
                $prefix =  Env::get('database.prefix', 'kmdshop_');
                $tablename = $prefix."shop_access";
                $sql = 'TRUNCATE '.$tablename;
                Db::execute($sql);
            }
            $result = $this->updateAccess($lists,0,$data['type']);
            if(empty($result)){
            
                return $this->renderError('同步失败'); 
            }   
        }
        return $this->renderSuccess('同步完成');
        
    }

    private function updateAccess($lists = [],$parent_id = 0,$type=0)
    {
        if(empty($lists)){
            return false;
        }
        $access_model = new  AccesscModel;
        foreach ($lists as $list) {
            //增量模式，按照上级要求用access_id
            $accessInfo = '';
            if($type==0){
                $where = [
                    'access_id'=>$list['access_id']
                ];
               $accessInfo =  $access_model->where($where)->find();
            }
            
           //$access_id = 0;
           if(empty($accessInfo)){
                $data = [
                    'access_id'=>$list['access_id'],
                    'name'=>$list['name'],
                    'path'=>$list['path'],
                    'parent_id'=>$list['parent_id'],
                    'sort'=>$list['sort'],
                    'icon'=>$list['icon'],
                    'redirect_name'=>$list['redirect_name'],
                    'is_route'=>$list['is_route'],
                    'is_menu'=>$list['is_menu'],
                    'alias'=>$list['alias'],
                    'is_show'=>$list['is_show'],
                    'plus_category_id'=>$list['plus_category_id'],
                    'remark'=>$list['remark'],
                    'app_id'=>$list['app_id']?$list['app_id']:0
                ];
                $newAccess = $access_model::create($data);
                //$access_id = $newAccess->access_id;
            }
            // if(empty($access_id)){
            //     $access_id= 0;
            // }
            if(!empty($list['children'])){
                $this->updateAccess($list['children'],$list['access_id'],$type);
            }
           
        }

        return 1;
    }

}