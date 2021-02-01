<?php

namespace app\api\model\user;

use app\api\model\plus\agent\Setting as AgentSettingModel;
use app\api\model\plus\friends\Friends;
use app\api\model\plus\task\Task;
use app\api\model\settings\Setting;
use think\facade\Cache;
use app\common\exception\BaseException;
use app\common\model\user\User as UserModel;
use app\api\model\plus\agent\Referee as RefereeModel;
use app\common\library\easywechat\AppWx;
use app\common\model\user\Grade as GradeModel;

/**
 * 用户模型类
 */
class User extends UserModel
{
    private $token;

    /**
     * 隐藏字段
     */
    protected $hidden = [
        'open_id',
        'is_delete',
        'app_id',
        'create_time',
        'update_time'
    ];

    /**
     * 获取用户信息
     */
    public static function getUser($token)
    {
        $userId = Cache::get($token);
        return self::where(['user_id' => $userId])->with(['address', 'addressDefault', 'grade'])->find();
    }

    /**
     * 用户登录
     */
    public function login($post)
    {
        // 微信登录 获取session_key
        $app = AppWx::getApp();
        $session = $app->auth->session($post['code']);
        // 自动注册用户
        $refereeId = isset($post['referee_id']) ? $post['referee_id'] : null;
        $userInfo = json_decode(htmlspecialchars_decode($post['user_info']), true);

        $iv = urldecode($post['iv']);
        $encrypted_data = urldecode($post['encrypted_data']);
        $decryptedData = $app->encryptor->decryptData($session['session_key'], $iv, $encrypted_data);

        // session_key 写入缓存
        Cache::set('session_key', $session['session_key'], 0);

        $reg_source = $post['source'];
        $user_id = $this->register($session['openid'], $userInfo, $refereeId, $decryptedData, $reg_source);
        // 生成token (session3rd)
        $this->token = $this->token($session['openid']);
        // 记录缓存, 7天
        Cache::tag('cache')->set($this->token, $user_id, 86400 * 7);


        return $user_id;
    }

    /**
     * 获取用户手机号
     * @param $post
     * @return bool
     * @throws BaseException
     * @throws \EasyWeChat\Kernel\Exceptions\DecryptException
     * @author Peng
     */
    public function phone($post)
    {
        $session_key = Cache::get('session_key');
        Cache::delete('session_key');
        $app = AppWx::getApp();
        $mall = $app->encryptor->decryptData($session_key, urldecode($post['iv']), urldecode($post['encrypted_data']));
        $phone = $mall["phoneNumber"];
        $model = $this;
        $db = $model->where(["app_id" => $post["app_id"], "user_id" => $post["user_id"]])->update(["mobile" => $phone]);
        if ($db !== false) {
            return true;
        }
        return false;
    }

    /**
     * 判断用户是否有手机号
     * @param $user_id
     * @return bool
     */
    public function isTruePhone($user_id)
    {
        $mobile = (new UserModel())->where('user_id', $user_id)->value('mobile');
        if ($mobile) {
            return true;
        }
        return false;
    }

    /**
     * 判断后台配置是否需要获取手机号
     * @return bool
     */
    public function isPhone()
    {
        try {
            $setting = Setting::getItem('signadv', self::$app_id);
            if (isset($setting["is_phone"]) && $setting["is_phone"] == 0) {
                return true;
            }
        } catch (\Exception $e) {
        }
        return false;
    }

    /**
     * 用户登录
     */
    public function bindMobile($post)
    {
        // 微信登录 获取session_key
        Cache::delete('session_key');
        $app = AppWx::getApp();
        $session = $app->auth->session($post['code']);
        $iv = urldecode($post['iv']);
        $encrypted_data = urldecode($post['encrypted_data']);
        $decryptedData = $app->encryptor->decryptData($session['session_key'], $iv, $encrypted_data);
        return $this->save([
            'mobile' => $decryptedData['phoneNumber']
        ]);
    }

    /**
     * 完善用户信息
     * @param $post
     * @return bool
     */
    public function saveInfo($post)
    {
        $data = [
            'name' => $post['name'],
            'gender' => $post['gender'],
            'birthday' => strtotime($post['birthday'])
        ];
        $res = $this->save($data);
        if ($res) {
            $friendModel = new Friends();
            $friendDetail = $friendModel->where('my_user_id', $this['user_id'])->where('user_id', $this['user_id'])->find();
            if ($friendDetail) {
                $friendData = [
                    'name' => $post['name'],
                    'birthday' => strtotime($post['birthday'])
                ];
                $friendModel->where('my_user_id', $this['user_id'])->where('user_id', $this['user_id'])->update($friendData);
            }
            $vars = Setting::getItem('task');
            if (isset($vars['is_open_task']) && !empty($vars['is_open_task'])) {
                $logModel = new Task();
                $logModel->saveTaskLog(1, $this['user_id']);
            }
        }
        return true;
    }

    /**
     * 获取token
     */
    public function getToken()
    {
        return $this->token;
    }


    /**
     * 生成用户认证的token
     */
    private function token($openid)
    {
        $app_id = self::$app_id;
        // 生成一个不会重复的随机字符串
        $guid = \getGuidV4();
        // 当前时间戳 (精确到毫秒)
        $timeStamp = microtime(true);
        // 自定义一个盐
        $salt = 'token_salt';
        return md5("{$app_id}_{$timeStamp}_{$openid}_{$guid}_{$salt}");
    }

    /**
     * 自动注册用户
     */
    private function register($open_id, $data, $refereeId = null, $decryptedData = [], $reg_source = '')
    {
        //通过unionid查询用户是否存在
        $user = null;
        if (isset($decryptedData['unionId']) && !empty($decryptedData['unionId'])) {
            $data['union_id'] = $decryptedData['unionId'];
            $user = self::detailByUnionid($decryptedData['unionId']);
        }
        if (!$user) {
            // 通过open_id查询用户是否已存在
            $user = self::detail(['open_id' => $open_id]);
        }
        if ($user) {
            $model = $user;
        } else {
            $model = $this;
            $data['referee_id'] = $refereeId;
            $data['reg_source'] = 'wx';
            //添加一个pid字段绑定用户关系--湖南社区团购项目使用
            //$data['pid'] = $refereeId;

            //默认等级
            $data['grade_id'] = GradeModel::getDefaultGradeId();

        }
        $this->startTrans();
        try {
            // 保存/更新用户记录
            if (!$model->save(array_merge($data, [
                'open_id' => $open_id,
                'app_id' => self::$app_id
            ]))
            ) {
                throw new BaseException(['msg' => '用户注册失败']);
            }
            if (!$user && $refereeId > 0) {
                // 记录推荐人关系
                RefereeModel::createRelation($model['user_id'], $refereeId);
                //更新用户邀请数量
                (new UserModel())->where('user_id', '=', $refereeId)->inc('total_invite')->update();
                //注册添加用户消息记录
                $news_data = [
                    'title' => '欢迎注册成为会员',
                    'content' => '欢迎注册成为会员'
                ];
                UserNews::add($news_data, $model['user_id']);
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw new BaseException(['msg' => $e->getMessage()]);
        }
        return $model['user_id'];
    }

    /**
     *统计被邀请人数
     */
    public function getCountInv($user_id)
    {
        return $this->where('referee_id', '=', $user_id)->count('user_id');
    }

    /**
     * 签到更新用户积分
     */
    public function setPoints($user_id, $days, $sign_conf, $sign_date)
    {
        $rank = $sign_conf['ever_sign'];
        if ($sign_conf['is_increase'] == 'true') {
            if ($days >= $sign_conf['no_increase']) {
                $days = $sign_conf['no_increase'] - 1;
            }
            $rank = ($days - 1) * $sign_conf['increase_reward'] + $rank;
        }
        //是否奖励
        if (isset($sign_conf['reward_data'])) {
            $arr = array_column($sign_conf['reward_data'], 'day');
            if (in_array($days, $arr)) {
                $key = array_search($days, $arr);
                if ($sign_conf['reward_data'][$key]['is_integral'] == 'true') {
                    $rank = $sign_conf['reward_data'][$key]['integral'] + $rank;
                }
            }
        }
        // 新增积分变动明细
        $this->setIncPoints($rank, '用户签到：签到日期' . $sign_date);
        return $rank;
    }

    /**
     * 个人中心菜单列表
     */
    public static function getMenus()
    {
        $menus = [
            'address' => [
                'name' => '收货地址',
                'path' => '/pages/user/address/address',
                'icon' => 'icon-dizhi1'
            ],
            'coupon' => [
                'name' => '领券中心',
                'path' => '/pages/coupon/coupon',
                'icon' => 'icon-youhuiquan1'
            ],
            'my_coupon' => [
                'name' => '我的优惠券',
                'path' => '/pages/user/my-coupon/my-coupon',
                'icon' => 'icon-youhuiquan-'
            ],
            'agent' => [
                'name' => '分销中心',
                'path' => '/pages/agent/index/index',
                'icon' => 'icon-fenxiao1'
            ],
            'bargain' => [
                'name' => '我的砍价',
                'path' => '/pages/user/my-bargain/my-bargain',
                'icon' => 'icon-kanjia'
            ],
        ];
        // 判断分销功能是否开启
        if (AgentSettingModel::isOpen()) {
            $menus['agent']['name'] = AgentSettingModel::getAgentTitle();
        } else {
            unset($menus['agent']);
        }
        return $menus;
    }

    /**
     * peng
     * 根据手机号获取用户信息
     * @param $phone
     * @return array|null|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setPhone($phone, $app_id)
    {
        $model = new UserModel();
        return $model->where(["mobile" => $phone, "app_id" => $app_id, "is_delete" => 0])->find();
    }

    // 生成个人编码
    public static function makeMyCode($user_id, $grade_code = '')
    {
        if (!empty($user_id)) {
            $length = strlen((string)$user_id);
            $prefix = '';
            $length = 10 - $length;
            for ($i = 0; $i < $length; $i++) {
                $prefix .= '0';
            }

            return $grade_code . $prefix . $user_id;
        } else {
            return '';
        }

    }
}
