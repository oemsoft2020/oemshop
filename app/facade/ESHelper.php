<?php


namespace app\facade;

use think\Facade;

/**
 *
 * @method static selectIndex(array $params)
 * @method static isExistIndexByIndexName(string $index)
 * @method static createIndex(array $params)
 * @method static tableToEs(array $data, string $index, string $type = '_doc')
 * @method static deleteIndex(string $index)
 * @method static insertBatchDocument(array $params)
 * @method static insertDocument(array $params)
 * @method static deleteDocument(string $index,$id)
 * @method static updateDocument(array $params)
 * @method static selectDocument(array $params)
 * @method static getCount(array $params)
 *
 * ElasticSearchHelper的门面类
 * Class ESHelper
 * @package app\facade
 */
class ESHelper extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\common\library\elasticsearch\ElasticSearchHelper';
    }
}