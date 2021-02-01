<?php

namespace app\api\controller;

use app\api\model\App;
use app\api\model\page\Page as AppPage;
use app\api\model\settings\Setting as SettingModel;
use app\api\model\page\Page as PageModel;
use app\api\model\plus\card\Card;
use think\facade\Cache;
use app\common\model\product\Product as ProductModel;
use app\common\model\user\User as UserModel;
use app\api\model\user\User as ApiUserModel;
use app\common\library\easywechat\AppMp as WxModel;

/**
 * 页面控制器
 */
class Index extends Controller
{
    /**
     * 首页
     */
    public function index($page_id = null)
    {
        header('Access-Control-Allow-Origin: *');
        //访问自动上下架接口。判断商品的上下架状态
        $model = new ProductModel();
        $model->shelves();

        $data =  $this->postData();


        $user =  $this->getUser(false);
        // 页面元素
        $data = AppPage::getPageData($user, $page_id);

        // 集采预定页面商品跳转携带参数
        if(isset($data['page']['params']['title']) && $data['page']['params']['title'] == '集采预定'){
            foreach ($data['items'] as $key => $value) {
                $data['items'][$key]['is_booking'] = 1;
            }

        }else{
            foreach ($data['items'] as $key => $value) {
                $data['items'][$key]['is_booking'] = 0;
            }
            
        }

        if($data['page']['params']){
            $data['page']['params']['icon_show'] = isset($data['page']['params']['icon_show']) ? $data['page']['params']['icon_show'] : 0;
            $data['page']['params']['icon_link'] = isset($data['page']['params']['icon_link']) ? $data['page']['params']['icon_link'] : '';
            $data['page']['params']['img_url'] = isset($data['page']['params']['img_url']) ? $data['page']['params']['img_url'] : '';
        }

        $data['setting'] = array(
            'collection' => SettingModel::getItem('collection'),
            'officia' => SettingModel::getItem('officia'),
            'homepush' => SettingModel::getItem('homepush'),
            'homepopup' => SettingModel::getItem('homepopup')
        );
        //首页增加一个当前用户代理商信息
        $data['agent'] = [];
        if($user){
            $userModel = new UserModel();
            //查询自己是不是代理商
            $agent =  $userModel->alias('user')

                ->join('agent_user','user.user_id = agent_user.user_id')

                ->where('user.user_id',$user['user_id'])

                ->field('user.user_id,agent_user.real_name,agent_user.mobile,user.avatarUrl')

                ->find();
            //自己不是代理商，再去查找之前的代理商
            if(!$agent){

                $agent = $userModel->alias('user')

                    ->join('agent_user','user.user_id = agent_user.user_id')

                    ->where('user.user_id',$user['referee_id'])

                    ->field('user.user_id,agent_user.real_name,agent_user.mobile,user.avatarUrl')

                    ->find();
            }
            $data['agent'] = $agent;
        }
        return $this->renderSuccess('', $data);
    }

    // 公众号客服
    public function mpService()
    {
        $mp_service = SettingModel::getItem('mp_service');
        return $this->renderSuccess('', compact('mp_service'));
    }

    //底部菜单

    public function tabbar()
    {
        $postData = $this->postData(); 
        $page_model  = new PageModel();
        $page_info = $page_model->where('page_type','40')->where('is_delete',0)->find();
        $tabbar = [];
        if(!empty($page_info)){
           
            $jsonData = $page_info['page_data'];
            jsonRecursive($jsonData);
            $tabbar = $jsonData['items'];
        }
        if(!empty($postData['card_id'])){
            $cardModel = new Card();
            $where  = [
                'card_id'=>$postData['card_id']
            ];
            $cardInfo =  $cardModel->where($where)->find();
            if(empty($cardInfo['supply_id'])&&!empty($tabbar)){
                $unShowArr = [
                    'pages/website/website',
                    'pages/supply/detail/detail'
                ];
                $rowsNum = count($tabbar[0]['data']);
                $newTabbar = [];
                foreach ($tabbar[0]['data'] as $key => $item) {
                    if(in_array($item['linkUrl'],$unShowArr)){
                        $rowsNum--;
                        unset($tabbar[0]['data'][$key]);
                    }else{
                        $newTabbar[] = $item;
                    }
                }
                $tabbar[0]['style']['rowsNum'] = $rowsNum;
                $tabbar[0]['data'] = $newTabbar;
            }
        }
        return $this->renderSuccess('', compact('tabbar'));
    }

    /* 
    * 绑定上级
    */
    public function bindreferee()
    {
        $data = $this->postData();
        if(!isset($data['referee_id'])||!isset($data['user_id'])){
            return $this->renderError('不可绑定');
        }

        if($data['referee_id']==$data['user_id']){
            return $this->renderError('不可绑定');
        }

        $user_model = new UserModel();
        $res = $user_model->bindReferee($data['user_id'],$data['referee_id']);
        if($res){
            $msg="绑定成功";
        }else{
            $msg="不可绑定";
        }
        return $this->renderSuccess($msg);
    }

    /* 
    * 公众号邀请页面
    */
    public function inviter()
    {
        if (!$token = $this->request->param('token')) {
            
            return json($this->renderJson(100,'缺少必要的参数：token'));
        }
        
        if (!$user = ApiUserModel::getUser($token)) {

            return json($this->renderJson(100,'没有找到用户信息'));
        }

        return $this->renderSuccess('success',compact('user'));
    }

    /* 
    * 微官网
    */
    public function website($page_id = null,$supply_id = 0)
    {
        // 页面元素
        //查询微官网页面
        $user_info = $this->getUser(false);
        if(empty($page_id)){

            $page_id  = (new AppPage)->getWebSitePage($user_info,$supply_id);
        }  
        $data = AppPage::getPageData($user_info, $page_id);

        if($data['page']['params']){
            $data['page']['params']['icon_show'] = isset($data['page']['params']['icon_show']) ? $data['page']['params']['icon_show'] : 0;
            $data['page']['params']['icon_link'] = isset($data['page']['params']['icon_link']) ? $data['page']['params']['icon_link'] : '';
            $data['page']['params']['img_url'] = isset($data['page']['params']['img_url']) ? $data['page']['params']['img_url'] : '';
        }
        return $this->renderSuccess('', $data);
    }

    /**
     * 测试接口，调试获取公众号文章
     * @Author   linpf
     * @DataTime 2020-11-25T15:38:51+0800
     * @return   [type]                   [description]
     */
    public function test()
    {
        $app_id = $this->app_id;
        // $app_id = 'wx61110c4f90dd177f';

        $mod = new WxModel();
        $app = $mod::getApp($app_id);

        $type = 'news';
        $count = $app->material->stats();
        // dump($count);die;
        $res = $app->material->list($type, 0, $count['news_count']);
        dump($res);die;

    }

    /* 
    *获取app的信息
    */
    public function getAppInfo()
    {
        $appinfo = App::detail($this->app_id);

        return $this->renderSuccess('', compact('appinfo'));
    }
}
