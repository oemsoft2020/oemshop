<?php

namespace app\shop\model\plus\vision;


use app\common\model\plus\vision\Vision as VisionModel;
use app\common\model\settings\Region as RegionModel;
use app\common\exception\BaseException;
use think\facade\Config;

/**
 * 视力数据模型
 */
class Vision extends VisionModel
{
    /**
     * @param $data array 查询条件
     *@param $params  array 查询条件
     * @return mixed
     */
    public function getList($data,$params)
    {
        $model = new static();
        if(!empty($params)) {
            //省级账号
            if (isset($params['province_id']) && $params['province_id'] > 0) {
                $model = $model->where('province_id', $params['province_id']);
            }
            //市级账号
            if (isset($params['city_id']) && $params['city_id'] > 0) {
                $model = $model->where('city_id', $params['city_id']);
            }
            //区级账号
            if (isset($params['region_id']) && $params['region_id'] > 0) {
                $model = $model->where('region_id', $params['region_id']);
            }
            //学校账号
            if (isset($params['school_name']) && !empty($params['school_name'])) {
                $model = $model->where('school_name', 'like', '%' . trim($params['school_name']) . '%');
            }
            //年级账号
            if (isset($params['grade_name']) && !empty($params['grade_name'])) {
                $model = $model->where('grade_name', 'like', '%' . trim($params['grade_name']) . '%');
            }
            //班级账号
            if (isset($params['class_name']) && !empty($params['class_name'])) {
                $model = $model->where('class_name', 'like', '%' . trim($params['class_name']) . '%');
            }
        }
        //检索：用户手机号
        if (!empty($data['mobile'])) {
            $model = $model->where('mobile', 'like','%' . trim($data['mobile']) . '%');
        }
         //检索：学校名称
//        if (!empty($data['school_name'])) {
//
//            $model = $model->where('school_name', 'like','%' . trim($data['school_name']) . '%');
//
//        }
        //检索：学生身份证
        if (!empty($data['id_card'])) {

            $model = $model->where('id_card', 'like','%' . trim($data['id_card']) . '%');

        }
        //检索：学生姓名
        if (!empty($data['student_name'])) {
            $model = $model->where('student_name', 'like','%' . trim($data['student_name']) . '%');
        }
        if (!empty($data["province_id"]) && $data["province_id"] != ''){
            $model = $model->where('province_id',$data["province_id"]);
            if (!empty($data["city_id"]) && $data["city_id"] != ''){
                $model = $model->where('city_id',$data["city_id"]);
                if (!empty($data["region_id"]) && $data["region_id"] != ''){
                    $model = $model->where('region_id',$data["region_id"]);
                }
            }
        }
        $list=$model->where('is_delete', '=', 0)
            ->order(['vision_id' => 'desc'])
            ->paginate($data, false, [
                'query' => request()->request()
            ]);

        /* 新增地区 */
        foreach ($list as $k => $v){
            if($v['test_time']>0){
                $list[$k]['test_time']= date("Y-m-d",$v['test_time']);

            }
            if ($v['province_id']>0){
                $list[$k]["province_name"] = RegionModel::getNameById($v["province_id"]);

            }else{
                $list[$k]["province_name"] = "未知";
            }
            if ($v['city_id']>0){
                $list[$k]["city_name"] = RegionModel::getNameById($v["city_id"]);

            }else{
                $list[$k]["city_name"] = "未知";
            }
            if ($v['region_id']>0){
                $list[$k]["region_name"] = RegionModel::getNameById($v["region_id"]);
            }else{
                $list[$k]["region_name"] = "未知";
            }
        }
        return $list;

    }


    /**
     * 省级信息
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException

     */
    public function getProvinceList()
    {
        $list = \think\facade\Db::table('kmdshop_region')->where('level',1)->field('id,name')->select();
        return $list;

    }


    /**
     * 区级信息
     * @param $data
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCityList($data)
    {
        $list = \think\facade\Db::table('kmdshop_region')->where('pid',$data["province_id"])->field('id,name')->select();
        return $list;
    }

    /**
     * 区级信息
     * @param $data
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRegionList($data)
    {
        $list = \think\facade\Db::table('kmdshop_region')->where('pid', $data["region_id"])->field('id,name')->select();

        return $list;
    }


     /**
     * 导入数据
     * @param $savename
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */

    public function putExcel($savename){

        set_time_limit(0);
        date_default_timezone_set('PRC');
        $upload_path = Config::get('filesystem.disks.public.root');
        $path = $upload_path."/".$savename;
        $bPage = 0;
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($path);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType); //实例化阅读器对象。
        $spreadsheet = $reader->load($path);  //将文件读取到到$spreadsheet对象中

        $currSheet = $spreadsheet->getSheet(0);
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

        $columnH = $currSheet->getHighestColumn(); // 取得总列数
        $rowCnt = $currSheet->getHighestRow();   //获取总行数

        $columnCnt = array_search($columnH, $cellName);

        $header = array();
        $data = array();

        for ($_row = 1; $_row <= 1; $_row++) {  //读取内容
            for ($_column = 0; $_column <= $columnCnt; $_column++) {
                $cellId = $cellName[$_column] . $_row;
                $cellValue = $currSheet->getCell($cellId)->getValue();
                if ($cellValue instanceof PHPExcel_RichText) {   //富文本转换字符串
                    $cellValue = $cellValue->__toString();
                }

                $header[$_row][$cellName[$_column]] = $cellValue;
            }
        }
        for ($_row = 0; $_row <= $rowCnt; $_row++) {  //读取内容
            for ($_column = 0; $_column <= $columnCnt; $_column++) {
                $cellId = $cellName[$_column] . $_row;
                $cellValue = $currSheet->getCell($cellId)->getValue();
                $cellValue = $currSheet->getCell($cellId)->getCalculatedValue();  #获取公式计算的值
                if ($cellValue instanceof PHPExcel_RichText) {   //富文本转换字符串
                    $cellValue = $cellValue->__toString();
                }

                $data[$_row][$cellName[$_column]] = $cellValue;
            }
        }
        if($data){
            return $res = $this->exportEquipmentData($data);
        }else{
            return ['state'=>0,'msg'=>'导入数据为空'];
        }

    }


    // 处理导入的数据
    public function exportEquipmentData($datas)
    {
        try {
            if ($datas) {

                /**二维数组去重,开始**/
                $rAr = array_unique($datas, SORT_REGULAR);

                $data = array_values($rAr);

                unset($data[0]);//去除表头
                unset($data[1]);//去除表头

                $this->startTrans();//开启事物

                /**二维数组去重,结束**/
                foreach ($data as $key => $value) {
                    $visionData['province_name'] = $value['A'];//省
                    $visionData['city_name'] = $value['B'];//市
                    $visionData['region_name'] = $value['C'];//区
                    if(!empty($value['A'])){
                        $visionData["province_id"] = RegionModel::getIdByName($value['A'],1);//省id

                    }
                    if($value['B']!=''){
                        $visionData["city_id"] = RegionModel::getIdByName($value['B'],2,$visionData['province_id']);//市id

                    }

                    if($visionData['region_name']!=''){
                        $visionData["region_id"] = RegionModel::getIdByName($value['C'],3,$visionData['city_id']);//区id

                    }
                    $visionData['school_name'] = $value['D'];//学校名称
                    $visionData['grade_name'] = $value['E'];//年级
                    $visionData['class_name'] = $value['F'];//班级名称
                    $visionData['student_name'] = $value['G'];//学生姓名
                    $visionData['birthday'] = $value['H'];//学生生日

                    if($value['I']=='男'){
                        $visionData['gender'] = 1;//性别：男
                    }
                    else if($value['I']=='女'){
                        $visionData['gender'] = 0; //性别：女
                    }
                    $visionData['id_card'] = $value["J"];//学生身份证
                    $visionData['student_number'] = $value['K'];//学籍号（全国）
                    $visionData['residential_address'] = $value['L'];//居住地址
                    $visionData['parents_name'] = $value['M'];//家长姓名
                    $visionData['mobile'] = $value['N'];//家长手机号
                    if($value['O']!=''){
                        $visionData['test_time'] =strtotime($value['O']);//检测日期
                    }
                    $visionData['left_eyesight'] = $value['P'];//左眼视力（裸眼）
                    $visionData['right_eyesight'] = $value['Q'];//右眼视力（裸眼）
                    $visionData['double_eyesight'] = $value['R'];//双眼视力（裸眼）



                    $visionArr[] = $visionData;
                }

                // 依据身份证判断是更新还是新增操作
                if($visionArr){

                    foreach ($visionArr as $key => $value) {
                        $vision_id = $this->where('is_delete','=','0')
                           ->where('id_card',$value['id_card'])
                            ->value('vision_id');
                        if(empty($vision_id)){
                            /* 添加数据*/


                            $result = $this->saveData($value);

                        }else{
                            // 更新原来的数据
                            $result = $this->updataData($value,$vision_id);


                        }
                    }
                }

                if($result){
                    $this->commit();
                    return ['state'=>1,'msg'=>'导入成功'];
                }else{
                    $this->rollback();
                    $returnData['state'] = 0;

                    $returnData['msg'] = "导入失败,请检查是否重复导入数据";

                    return $returnData;
                }

            }

        }catch (Exception $exception) {

            $this->rollback();
            $returnData['state'] = 0;
            $returnData['msg'] = "添加数据异常;" . $exception->getMessage();
            return $returnData;
        }
    }

    public function saveData($data = array())

    {
       
        $data['app_id'] = self::$app_id;
        $data['create_time'] = time();
        $res = $this->insert($data,true);
        return $res;

    }
    public function updataData($data = array(),$vision_id)
    {
        $res = $this->where(['vision_id' => $vision_id])->update($data);
        return $res;
    }

    /**
     * 更新记录
     */
    public function edit($data)
    {

        $where['vision_id'] = $data['vision_id'];
        unset($data['vision_id']);
        return self::update($data, $where);
    }

    /**
     * 删除记录 (软删除)
     */
    public function setDelete($where)
    {
        return self::update(['is_delete' => 1], $where);
    }






}
