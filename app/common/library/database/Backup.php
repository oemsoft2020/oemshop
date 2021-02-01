<?php


namespace app\common\library\database;


use think\facade\Config;
use think\facade\Db;
use think\facade\Env;

/**
 * 数据库备份
 */
class Backup
{

    private $path;//备份文件保存路径
    private $originDbName;//要备份的数据库名(源数据库)
    private $targetDbName;//新数据库名(目标数据库)

    private $model;//数据库模型对象
    private $tables = array();//要备份的数据表

    private $database = []; //忽略的表

    /**
     * Backup constructor.
     * @param $path 文件保存路径
     * @param $targetDbName 备份后新的数据库名
     * @param $originDbName 要备份的数据库名(源)
     */
    function __construct($path, $targetDbName, $originDbName)
    {
        $this->path = $path;
        $this->originDbName = $originDbName;
        $this->targetDbName = str_replace('.', '_', $targetDbName);
        //初始化数据库
        $this->initModel();

        //设置目录权限
        $this->check_path();
        //获取要备份的表
        $this->getBackupTables();
        //忽略数据备份的表
        $this->ignoreTable();

    }

    /**
     * 实例化数据模型
     * @return \mysqli
     */
    function initModel()
    {
        $config = Config::get('database');

        $servername = $config['connections']['mysql']['hostname'];
        $username = $config['connections']['mysql']['username'];
        $password = $config['connections']['mysql']['password'];
        // 创建连接
        $model = new \mysqli($servername, $username, $password);
        // 检测连接
        if ($model->connect_error) {
            die("连接失败: " . $model->connect_error);
        }
        $this->model = $model;
    }

    /**
     * 备份数据库
     * @return false|int
     */
    public function backupAll()
    {
//        echo '运行前内存：' . round(memory_get_usage() / 1024 / 1024, 2) . 'MB', '';

        $filename = $this->path . $this->targetDbName . '.sql';

        file_exists($filename) && unlink($filename);

        $tableTitle = $this->genTitle();
        $data = null;
        $data = $tableTitle;
        if ($this->tables) {
            foreach ($this->tables as $table) {

                //拿相关 create table 数据
                $ctable = $this->get_create_table($table);

                //生成表结构
                $data .= $this->get_table_structure($ctable);
                file_put_contents($filename, $data, FILE_APPEND);

                unset($data);
                $data = '';

                //忽略的数据表
                if (!in_array($table, $this->database['table'])) {
                    // 表记录
                    $data = $this->get_table_records($table);
                }
                file_put_contents($filename, $data, FILE_APPEND);

                unset($data);
                $data = '';
            }
//            echo '运行后内存：' . round(memory_get_usage() / 1024 / 1024, 2) . 'MB', '';
        }

    }

    /**还原数据库
     * 还原方法  拆分sql语句,  因为之前保存到文件中的语句都是以 ;\r\n 结尾的, 所以...
     * @return bool
     */
    public function restore()
    {
        ini_set('max_execution_time', '0');//设置为0，就是不限制执行的时间。
        //创建新数据表
        $this->createDatabase();
        $res = mysqli_select_db($this->model, $this->targetDbName) or die("指定数据库打开失败");
        $this->model->query('set names utf8');
        $fileName=$this->path . DIRECTORY_SEPARATOR . $this->targetDbName . '.sql';
        foreach ($this->getFileData($fileName) as $key => $sqls) {
            if ($sqls) {
                $this->model->query($sqls);
            }
        }
        mysqli_close($this->model);
        return true;
    }


    function getFileData($filename)
    {

        if (!file_exists($filename)) {
            return false;
        }
        $str = fread($hd = fopen($filename, "rb"), filesize($filename));
        $sqls = explode(";\r\n", $str);//所以... 这里拆分sql
        fclose($hd);
        if ($sqls) {
            foreach ($sqls as $k => $sql) {
                yield $sql;
            }
        }
    }

    //创建数据库
    public function createDatabase()
    {
        $dbname = $this->targetDbName;
        // 创建数据库
        $sql = "CREATE DATABASE IF NOT EXISTS " . $dbname . " DEFAULT CHARSET utf8 COLLATE utf8_general_ci;";
        $this->model->query($sql) or die("创建数据库失败");
    }

    //备份文件相关
    public function getFileInfo()
    {
        $temp = array();
        if (is_dir($this->path)) {
            $handler = opendir($this->path);
            $num = 0;
            while ($file = readdir($handler)) {
                if ($file !== '.' && $file !== '..') {
                    $filename = $this->path . $file;
                    $temp[$num]['name'] = $file;
                    $temp[$num]['size'] = ceil(filesize($filename) / 1024);
                    $temp[$num]['time'] = date("Y-m-d H:i:s", filemtime($filename));
                    $temp[$num]['path'] = $filename;
                    $num++;
                }
            }
        }
        return $temp;
    }

    //删除文件
    public function delFile($file)
    {
        if (file_exists($file)) {
            return unlink($file);
        }
        return false;
    }

    //sql 文件开头部分  可以省略 但 SET FOREIGN_KEY_CHECKS=0 最好有
    private function genTitle()
    {
        $time = date("Y-m-d H:i:s", time());
        $str = "/*************************\r\n";
        $str .= " * {$time} \r\n";
        $str .= " ************************/\r\n";
        $str .= "SET FOREIGN_KEY_CHECKS=0;\r\n";
        return $str;
    }

    /**
     * 获取所有要备份的数据库表
     */
    private function getBackupTables()
    {
        $sql = 'show tables';
        if ($data = Db::query($sql)) {
            foreach ($data as $val) {
                $this->tables[] = $val['Tables_in_' . $this->originDbName];
            }
        }
    }

    /**
     * 获取数据表的创建结构
     * @param $table
     * @return array
     */
    private function get_create_table($table)
    {
        $sql = "show create table $table";
        $arr = Db::query($sql);
        $arr = $arr[0];
        foreach ($arr as &$value) {
            //处理表前缀
            $value = $this->databasePrefix($value);
        }
        return array_values($arr);
    }

    /**处理数据前缀
     * @param $string
     * @return string
     */
    private function databasePrefix($params)
    {
        $originPrefix = Env::get('database.prefix');
        $targetPrefix = Config::get('packing.database.prefix') ?: $originPrefix;

        if ($originPrefix) {
            if (is_string($params)) {
                return str_replace($originPrefix, $targetPrefix, $params);
            }

            if (is_array($params)) {
                foreach ($params as &$value) {
                    $value = str_replace($originPrefix, $targetPrefix, $value);
                }
                return $params;
            }
        }
    }

    /**
     * 生成表结构
     * @param $ctable
     * @return string
     */
    private function get_table_structure($ctable)
    {
        $str = "-- ----------------------------\r\n";
        $str .= "-- Table structure for `{$ctable[0]}`\r\n";
        $str .= "-- ----------------------------\r\n";
        $str .= "DROP TABLE IF EXISTS `{$ctable[0]}`;\r\n" . $ctable[1] . ";\r\n\r\n";
        return $str;
    }

    //表记录的sql语句拼接  当还原的时候  就是逐条插入记录 到对应的表
    private function get_table_records($table)
    {
        $sql = null;
        $sql = "select * from {$table}";
        if ($data = Db::query($sql)) {
            $str = "-- ----------------------------\r\n";
            $str .= "-- Records of " . $this->databasePrefix($table) . " \r\n";
            $str .= "-- ----------------------------\r\n";
            foreach ($this->getTableData($data) as $k => $val) {
                if ($val) {
                    $valArr = array();
                    foreach ($this->getDataValue($val) as $v) {
                        //$keyArr[] = "`".$k."`";
                        //对单引号和换行符进行一下转义
                        $valArr[] = "'" . str_replace(array("'", "\r\n"), array("\'", "\\r\\n"), $v) . "'";
                    }
                    $values = implode(', ', $valArr);
                    $valArr = null;
                    $table = $this->databasePrefix($table);
                    $str .= "INSERT INTO `{$table}` VALUES ($values);\r\n";//省略了字段名称
                }

            }
            unset($data);
            $data = null;
            $str .= "\r\n";
            return $str;
        }
        return '';
    }

    private function getTableData($data)
    {
        if (!is_array($data)) {
            return false;
        }
        foreach ($data as $val) {
            yield $val;
        }
    }

    private function getDataValue($value)
    {
        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $val) {
            yield $val;
        }

    }

    private function check_path()
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }
    }

    /**
     * 不拷贝数据的数据表
     */
    private function ignoreTable()
    {
        //原数据库前缀
        $originPrefix = Env::get('database.prefix');
        //忽略的数据库
        $ignore = Config::get('packing.database');
        foreach ($ignore as $key => $value) {
            $this->database[$key] = [];
            if (!is_array($value)) continue;

            foreach ($value as $k => $val) {

                $key == 'table' && array_push($this->database[$key], $originPrefix . $val);

                if ($key == 'key') {
                    $this->database[$key][$originPrefix . $k][] = $val;
                }
            }
        }
//        dump($this->database);
    }


}