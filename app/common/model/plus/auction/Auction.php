<?php

namespace app\common\model\plus\Auction;

use app\common\model\BaseModel;

/**
 * 文章模型
 */
class Auction extends BaseModel
{
    protected $name = 'auction';
    protected $pk = 'auction_id';

    /**
     * 关联文章封面图
     * @return \think\model\relation\HasOne
     */
    public function image()
    {
        return $this->hasOne('app\\common\\model\\file\\UploadFile', 'file_id', 'image_id');
    }


    /**
     * 详情
     * @param $auction_id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function detail($auction_id)
    {
        return self::with(['image'])->find($auction_id);
    }


}
