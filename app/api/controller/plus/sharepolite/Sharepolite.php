<?php

namespace app\api\controller\plus\sharepolite;

use app\api\controller\Controller;
use app\common\model\settings\Setting;
use app\api\model\plus\sharepolite\ShareList;
use app\api\model\plus\sharepolite\ShareRecord;
use Symfony\Component\VarExporter\Internal\Values;
use think\facade\Db;

/**
 * APi分享返利功能
 */

class Sharepolite extends Controller
{
  // 分享api

  public function getSetting(Setting $setting, ShareList $shareList)
  {
    // 查询分享总开关is_open
    $postdata = $this->postData();
    $where = [
      'app_id' => $postdata['app_id'],
      'key' => 'sharepolite'
    ];
    $result = $setting->where($where)->find()['values'];

    if ($result['is_open'] != 1) {
      $data = [
        'is_share' => 0,
        'msg' => '分享功能已关闭',
        'product_id' => $postdata['product_id'],
        'product_type' => $postdata['product_type']
      ];
      return $this->renderSuccess('', compact('data'));
    }

    $data = [
      'is_share' => 1,
      'msg' => '分享功能正常',
      'product_id' => $postdata['product_id'],
      'product_type' => $postdata['product_type']
    ];
    return $this->renderSuccess('', compact('data'));
  }

  // 判断商品是否为分享商品
  public function goodsHasShare()
  {
    $postdata = $this->postData();
    $where = [
      'goods_id' => $postdata['product_id'],
      'goods_type' => $postdata['product_type'],
      'app_id' => $postdata['app_id'],
    ];
    $shareList = new \app\api\model\plus\sharepolite\ShareList;
    $share_list_res = $shareList->where($where)->find();

    if (!$share_list_res) {
      $data = [
        'is_share' => 0,
        'msg' => '该商品非分享商品',
        'product_id' => $postdata['product_id'],
        'product_type' => $postdata['product_type']
      ];
      return $this->renderSuccess('', compact('data'));
    }

    $data = [
      'is_share' => 1,
      'msg' => '该商品是分享商品',
      'product_id' => $postdata['product_id'],
      'product_type' => $postdata['product_type']
    ];
    return $this->renderSuccess('', compact('data'));
  }

  public function goodShareSetting()
  {
    $postdata = $this->postData();
    $shareList = new \app\api\model\plus\sharepolite\ShareList;
    $where = [
      'goods_id' => $postdata['product_id'],
      'goods_type' => $postdata['product_type'],
      'app_id' => $postdata['app_id'],
    ];

    $sharelist_res  = $shareList->where($where)->field('share_list_id,setting')->find();

    if (!$sharelist_res) {
      $data = [
        'is_reward' => 0,
        'msg' => '该商品不为分享商品',
        'product_id' => $postdata['product_id'],
        'product_type' => $postdata['product_type']
      ];
      return $this->renderSuccess('该商品不为分享商品', compact('data'));
    }

    $setting_val = json_decode($sharelist_res['setting'], true);

    if ($setting_val['status'] == 0) {
      $data = [
        'is_reward' => 0,
        'msg' => '该商品已关闭分享功能',
        'product_id' => $postdata['product_id'],
        'product_type' => $postdata['product_type']
      ];
      return $this->renderSuccess('该商品已关闭分享功能', compact('data'));
    }

    $time =  date('Y-m-d', time());

    if (!(($time > $setting_val['effective_time'][0]) && ($time < $setting_val['effective_time'][1]))) {

      $data =  [
        'is_reward' => 0,
        'msg' => '活动时间过期',
        'product_id' => $postdata['product_id'],
        'product_type' => $postdata['product_type']
      ];
      return $this->renderSuccess('活动时间过期', compact('data'));
    }

    $sharerecord = new ShareRecord();

    $total = $sharerecord->where('share_list_id', $sharelist_res['share_list_id'])->count();

    if ($total > $setting_val['reward_total']) {
      $data = [
        'is_reward' => 0,
        'msg' => '商品分享次数已上限',
        'product_id' => $postdata['product_id'],
        'product_type' => $postdata['product_type']
      ];

      return $this->renderSuccess('', compact('data'));
    }

    $data =  [
      'msg' => '商品分享参数正常',
      'is_reward' => 1,
      'product_id' => $postdata['product_id'],
      'product_type' => $postdata['product_type']
    ];

    return $this->renderSuccess('', compact('data'));
  }

  // 用户创建分享记录

  public function joinShare()
  {
    $postdata  = $this->postData();
    $sharerecord = new  ShareRecord;
    $shareList = new ShareList;

    $where = [
      'goods_id' => $postdata['product_id'],
      'goods_type' => $postdata['product_type'],
      'app_id' => $postdata['app_id'],
    ];
    $share_list_res = $shareList->where($where)->field('share_list_id,setting')->find();

    $share_list_setting = json_decode($share_list_res['setting'], true);

    // 活动过期或分享开关已关闭
    if ($share_list_setting['status'] == 0) {
      $data = [
        'msg' => '活动已结束',
        'product_id' => $postdata['product_id'],
        'product_type' => $postdata['product_type'],
        'share_record_id' => 0,
      ];
      return $this->renderSuccess('', compact('data'));
    }

    if (!isset($postdata['from_user_id']) && !isset($postdata['to_user_id']) && empty($postdata['from_user_id']) && $postdata['to_user_id']) {
      $data = [
        'msg' => '分享人与接收人关联不能为空',
        'product_id' => $postdata['product_id'],
        'product_type' => $postdata['product_type'],
        'share_record_id' => 0,
      ];
      return $this->renderSuccess('', compact('data'));
    }

    $where = [
      'sharer_id' => $postdata['from_user_id'],
      'reader_id' => $postdata['to_user_id'],
      'share_list_id' => $share_list_res['share_list_id'],
    ];

    // 查看用户是否曾经已经获得过红包
    $has_reward = $sharerecord->where($where)->find();

    if ($has_reward != null) {
      $data = [
        'msg' => '您已获得红包',
        'product_id' => $postdata['product_id'],
        'product_type' => $postdata['product_type'],
        'share_record_id' => 0,
      ];
      return $this->renderSuccess('', compact('data'));
    }

    if ($postdata['from_user_id'] == $postdata['to_user_id']) {
      $data = [
        'msg' => '请勿自己分享给自己',
        'product_id' => $postdata['product_id'],
        'product_type' => $postdata['product_type'],
        'share_record_id' => 0,
      ];
      return $this->renderSuccess('请勿自己分享给自己', compact('data'));
    }

    $where = [];
    $data = [];
    $money_area = $shareList->getMoney($share_list_res['share_list_id']);

    $where = [
      'sharer_id' => $postdata['from_user_id'],
      'reader_id' => $postdata['to_user_id'],
      'share_list_id' => $share_list_res['share_list_id'],
      'create_time' => time(),
      'reward_type' => $share_list_setting['reward_type'],
      'app_id' => $postdata['app_id'],
      'promoter_id' => $postdata['from_user_id'],
      'promoter_money' => number_format($money_area['first_reward'], 2),
      'interlocutor_id' => 0,
      'interlocutor_money' => 0,
      'father_id' => 0,
      'last_record_id' => 0
    ];

    $shareTeam = new \app\api\model\plus\sharepolite\ShareTeam;
    //创建分享记录
    if ($postdata['share_record_id'] != 0) {
      $record_res = $sharerecord->where('share_record_id', $postdata['share_record_id'])->field('sharer_id')->find();
      $where['interlocutor_id'] = $record_res['sharer_id'];
      $where['interlocutor_money'] = number_format($money_area['second_reward'], 2);
      $where['father_id'] = $record_res['sharer_id'];
      $where['last_record_id'] = $postdata['share_record_id'];

      //一級返紅包
      $rebate = new \app\api\model\plus\agent\User;

      $first_where = [
        'user_id' => $postdata['from_user_id'],
        'app_id' => $postdata['app_id'],
      ];

      $firest_money_res = $rebate->where($first_where)->inc('money')->update();


      //二級返紅包
      $second_where = [
        'user_id' => $record_res['sharer_id'],
        'app_id' => $postdata['app_id'],
      ];

      $second_money_res = $rebate->where($second_where)->inc('money')->update();

      if ($firest_money_res && $second_money_res) {
        $money_msg = '成功返利到分享中心';
      } else {
        $money_msg = '返利过程出错';
      }
    } else {

      //一級返紅包
      $rebate = new \app\api\model\plus\agent\User;

      $redpage_where = [
        'user_id' => $postdata['from_user_id'],
      ];
      $money = number_format($money_area['first_reward'], "2");
      $update_agent =  $rebate->where($redpage_where)->inc('money', $money)->update();

      if ($update_agent) {
        $money_msg = '成功返利到分享中心';
      } else {
        $money_msg = '返利过程出错';
      }
    }

    $share_record_id = $sharerecord->insertGetId($where);

    if ($share_record_id) {
      $data = [
        'money_msg' => $money_msg,
        'msg' => '为好友助力成功',
        'product_id' => $postdata['product_id'],
        'product_type' => $postdata['product_type'],
        'share_record_id' => $share_record_id,
      ];
    } else {
      $data = [
        'msg' => '绑定分享关系失败',
        'product_id' => $postdata['product_id'],
        'product_type' => $postdata['product_type'],
      ];
    }

    return $this->renderSuccess('分享成功', compact('data'));
  }

  // 获取某人的红包总金额和明细

  public function getMoney($user_id, ShareRecord $sharerecord)
  {
    $where = [
      'sharer_id' => $user_id,
      'share_grade' => 1,
    ];
    $promoter_money = $sharerecord->where($where)->field('promoter_money')->select()->toArray();

    $where = [
      'interlocutor_id' => $user_id,
      'share_grade' => 2
    ];
    $interlocutor_money = $sharerecord->where($where)->field('interlocutor_money')->select()->toArray();
    $data['promoter_money'] = $promoter_money;
    $data['interlocutor_money'] = $interlocutor_money;
    $data['total'] = array_sum($data['promoter_money'] = $promoter_money) + array_sum($data['interlocutor_money']);
    return $this->renderSuccess('', compact('data'));
  }

  // 查询分享商品参与成员

  public function getJoinMember(ShareRecord $sharerecord, ShareList $sharelist)
  {
    $postdata = $this->postData();
    $where = [
      'goods_id' => $postdata['product_id'],
      'goods_type' => $postdata['product_type'],
      'app_id' => $postdata['app_id']
    ];
    $share_list_res = $sharelist->where($where)->field('share_list_id')->find();
    $data =  $sharerecord->getJoinMember($share_list_res['share_list_id']);
    return $this->renderSuccess('查询参与成员信息成功', compact('data'));
  }

  // 获取购买商品成员

  public function getBuyMember()
  {
    $postdata = $this->postData();
    // $order = new \app\api\model\plus\agent\Order;
    $order = new \app\api\model\order\OrderProduct;
    $user = new \app\api\model\user\User;
    $where = [
      'product_id' => $postdata['product_id'],
      'app_id' => $postdata['app_id'],
    ];
    $data['data'] = [];
    $buy_product = $order->where($where)->field('user_id,total_pay_price,create_time')->select();

    foreach ($buy_product as $k => $v) {
      $user_info = $user->where('user_id', $v['user_id'])->field('nickName,avatarUrl')->find();
      $data['data'][$v['user_id']]['nickName'] = $user_info['nickName'];
      $data['data'][$v['user_id']]['avatarUrl'] = $user_info['avatarUrl'];
      $data['data'][$v['user_id']]['pay_money'] = $v['total_pay_price'];
      $data['data'][$v['user_id']]['create_time'] = $v['create_time'];
    }
    $data['buy_member_total'] = count($data['data']);
    return $this->renderSuccess('', compact('data'));
  }

  public function getRecommandGoods()
  {
    $sharelist = new \app\api\model\product\Product;
  }

  public function getIntegral()
  {
    $postdata = $this->postData();
    $pointsLog = new \app\api\model\user\PointsLog;
    $sharerecord = new \app\api\model\plus\sharepolite\ShareRecord;
    $user = new \app\api\model\user\User;
    $type = 'article';
    $article_id = $postdata['article_id'];
    $settingvalues = Setting::getItem('article');

    if (empty($settingvalues['share_article_point'])) {
      $data = [
        'msg' => '未开启设置积分设置',
      ];
      return $this->renderSuccess('未开启设置积分设置', compact('data'));
    }

    $get_integral = $settingvalues['share_article_point'];

    $where = [
      'user_id' => $postdata['from_user_id'],
      'value' => $get_integral,
      'describe' => '分享文章得积分',
      'create_time' => time(),
      'app_id' => $postdata['app_id'],
    ];

    //查询之前是否获得积分
    $share_record = $sharerecord->where('type',$type)->where('article_id',$postdata['article_id'])->where('sharer_id', $postdata['from_user_id'])->where('reader_id', $postdata['to_user_id'])->find();

    if ($share_record) {
      $data = [
        'msg' => '用户已获得积分',
        'article_id' => $postdata['article_id'],
      ];
      return $this->renderSuccess('用户已获得积分', compact('data'));
    }

    if ($postdata['from_user_id'] == $postdata['to_user_id']) {
      $data = [
        'msg' => '请勿分享给自己',
        'article_id' => $postdata['article_id'],
      ];
      return $this->renderSuccess('请勿分享给自己', compact('data'));
    }

    $where = [
      'sharer_id' => $postdata['from_user_id'],
      'reader_id' => $postdata['to_user_id'],
      'promoter_id' => $postdata['from_user_id'],
      'promoter_money' => 0,
      'interlocutor_id' => 0,
      'interlocutor_money' => 0,
      'reward_type' => 2,
      'create_time' => time(),
      'share_list_id' => 0,
      'father_id' => 0,
      'article_id'=>$article_id,
      'type'=>'article',
      'app_id' => $postdata['app_id'],
    ];
    $insert_res = $sharerecord->insert($where);

    if (!$insert_res) {

      $data = [
        'msg' => '形成分享记录失败',
        'article_id' => $postdata['article_id'],
      ];
      return $this->renderSuccess('形成分享记录失败', compact('data'));
    }

    $add = [
      'user_id' => $postdata['from_user_id'],
      'value' => $get_integral,
      'describe' => '分享文章得积分',
      'create_time' => time(),
      'app_id' => $postdata['app_id'],
    ];
    // 记录用户获得积分
    $create_point_log = $pointsLog->insert($add);
    if (!$create_point_log) {
      $data = [
        'msg' => '用户获得积分失败',
        'article_id' => $postdata['article_id'],
      ];
      return $this->renderSuccess('', compact('data'));
    }

    // 累计积分添加获得的积分
    $points = $user->where('user_id', $postdata['from_user_id'])->inc('points', $get_integral)->update();
    $total_points = $user->where('user_id', $postdata['from_user_id'])->inc('total_points', $get_integral)->update();

    if ($create_point_log && $total_points && $points) {
      $data = [
        'msg' => '用户获得积分成功',
        'article_id' => $postdata['article_id'],
      ];
    } else {
      $data = [
        'msg' => '用户获得积分失败',
        'article_id' => $postdata['article_id'],
      ];
    }
    return $this->renderSuccess('', compact('data'));
  }
}
