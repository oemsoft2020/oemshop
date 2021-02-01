<?php

namespace app\shop\model\page;

use app\common\model\page\Page as PageModel;
use app\shop\model\app\App;

/**
 * 微信小程序diy页面模型
 */
class Page extends PageModel
{
    /**
     * 获取列表
     */
    public function getList($params)
    {
        return $this->where(['is_delete' => 0])
            ->where('page_type','<>',10)
            ->order(['create_time' => 'desc'])
            ->hidden(['page_data'])
            ->paginate($params, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 获取所有自定义页面
     */
    public function getLists()
    {
        return $this->where(['is_delete' => 0])
            ->where(['page_type' => 20])
            ->hidden(['page_data'])
            ->order(['create_time' => 'desc'])
            ->select();
    }

    /**
     * 新增页面
     */
    public function add($data)
    {
        // 删除app缓存
        App::deleteCache();
        return $this->save([
            'page_type' => 20,
            'page_name' => $data['page']['params']['name'],
            'page_data' => $data,
            'app_id' => self::$app_id
        ]);
    }

    /**
     * 更新页面
     */
    public function edit($data)
    {
        // 删除app缓存
        App::deleteCache();
        // 保存数据
        return $this->save([
                'page_name' => $data['page']['params']['name'],
                'page_data' => $data
            ]) !== false;
    }

    public function setDelete(){
        return $this->save(['is_delete' => 1]);
    }

    public function editUser($data)
    {
        // 删除app缓存
        App::deleteCache();
        $model = $this;
        $model =  $model->where('page_type', '30')->find();
        if(empty($model)){
            return $this->save([
                'page_type' => 30,
                'page_name' => $data['page']['params']['name'],
                'page_data' => $data, 
                'app_id' => self::$app_id
            ]);
        }else{
            return  $model->save([
                'page_name' => $data['page']['params']['name'],
                'is_delete' =>0,
                'page_data' => $data,
            ]);
        }
    }
    public function editSupply($data)
        {
            // 删除app缓存
            App::deleteCache();
            $model = $this;
            $model =  $model->where('page_type', '50')->find();
            if(empty($model)){
                return $this->save([
                    'page_type' => 50,
                    'page_name' => $data['page']['params']['name'],
                    'page_data' => $data, 
                    'app_id' => self::$app_id
                ]);
            }else{
                return  $model->save([
                    'page_name' => $data['page']['params']['name'],
                    'is_delete' =>0,
                    'page_data' => $data,
                ]);
            }
        }

    /* 
    * 底部菜单
    */
    public function editTabbar($data)
    {
        // 删除app缓存
        App::deleteCache();
        $model = $this;
        $model =  $model->where('page_type', '40')->find();
        if(empty($model)){
            return $this->save([
                'page_type' => 40,
                'page_name' => $data['page']['params']['name'],
                'page_data' => $data, 
                'app_id' => self::$app_id
            ]);
        }else{
            return  $model->save([
                'page_name' => $data['page']['params']['name'],
                'is_delete' =>0,
                'page_data' => $data,
            ]);
        }
    }

    public function getWebSitePage()
    {
        $model = $this;

        if (!empty(self::$supply_id)) {
            $model = $model->where('supply_id', '=', self::$supply_id);
        }else{
            $model = $model->where('supply_id', '=', 0);
        }
        $page_info = $model->where('page_type', '50')->where('is_delete',0)->find();
        return $page_info;
    }

    public function editWebSite($data)
    {
        // 删除app缓存
        App::deleteCache();
        $model = $this;
        
        $websiteData =  [
            'page_type' => 50,
            'page_name' => $data['page']['params']['name'],
            'page_data' => $data, 
            'app_id' => self::$app_id
        ];
        if (!empty(self::$supply_id)) {

            $model = $model->where('supply_id', '=', self::$supply_id);
            $websiteData['supply_id'] = self::$supply_id;

        }else{

            $model = $model->where('supply_id', '=',0); 
        }
        
        $model =  $model->where('page_type', '50')->find();
        
        if(empty($model)){
            return $this->save($websiteData);
        }else{
            return  $model->save([
                'page_name' => $data['page']['params']['name'],
                'is_delete' =>0,
                'page_data' => $data,
            ]);
        }
    }
}
