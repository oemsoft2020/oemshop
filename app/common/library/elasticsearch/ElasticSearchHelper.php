<?php


namespace app\common\library\elasticsearch;


class ElasticSearchHelper
{
    private $client;

    /**
     * ElasticSearchHelper constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->client = ElasticSearch::getInstance();
    }

    /**
     * 获取文档总数
     * @param array $params
     * @return array|callable
     */
    public function getCount(array $params){
        return $this->client->count($params);
    }

    /**
     * 更新一条记录
     * @param array $params
     * @return array|callable
     */
    public function updateDocument(array $params){
        return $this->client->update($params);
    }

    /**
     * 插入单条记录
     * @param array $params
     * @return array|callable
     */
    public function insertDocument(array $params){
        return $this->client->index($params);
    }

    /**
     * 批量插入文档
     * @param array $params
     * @return array|callable
     */
    public function insertBatchDocument(array $params){
        if (!empty($params['body'])) {
            return $this->client->bulk($params);
        }
    }

    /**
     * @param string $index
     * @param $id
     * @return array|callable
     */
    public function deleteDocument(string $index,$id){
        $params = [
            'index' => $index,
            'id' => $id
        ];
        return $this->client->delete($params);
    }

    /**
     * 搜索
     * @param array $params
     * @return array|callable
     */
    public function selectDocument(array $params){
        return $this->client->search($params);
    }

    /**
     * 查找索引
     * @param array $params
     * @return array
     */
    public function selectIndex(array $params){
        return $this->client->indices()->getSettings($params);
    }

    /**
     * 根据索引名称判断索引是否存在
     * @param string $index
     * @return bool
     */
    public function isExistIndexByIndexName(string $index){
        $params = ['index' => $index];
        return $this->client->indices()->exists($params);
    }

    /**
     * 删除索引库
     * @param string $index
     * @return array
     */
    public function deleteIndex(string $index){
        $params = ['index' => $index];
        return $this->client->indices()->delete($params);
    }

    /**
     * 创建索引库
     * @param array $params
     * @return array|callable
     */
    public function createIndex(array $params){
        return $this->client->indices()->create($params);
    }

    // 把数据同步到es
    public function tableToEs(array $data, string $index, string $type = '_doc'){
        $params['index'] = $index;
        $params['type'] = $type;
        $params['body'] = $data;
        return $this->client->bulk($params);
    }

}