<?php


namespace app\shop\service\packing;


use app\common\library\database\Backup;
use app\common\library\file\File;
use think\facade\Config;

class PackingService
{
    //文件对象
    public $file;

    //站点名称
    public $siteName;

    public function __construct($siteName)
    {
        $this->file = new File();
        $this->siteName = $siteName;
    }

    /**
     * 代码文件复制拷贝
     * @param $originPath 源目录
     * @param $targetPath 目标目录
     */
    public function copy($originPath, $targetPath)
    {
        $res = $this->file->handle_dir($originPath, $targetPath);

        if (!$res) return false;

        //清理根目录文件
        $this->clearRootDir($targetPath);
        //清理public目录文件
        $this->clearPublic($targetPath);
        //生成.env配置文件
        $this->buildEnv($targetPath);

    }


    /**
     * 删除无效插件文件
     * @param $targetPath
     * @param $plusApps
     * @return array
     */
    public function file($targetPath, $plusApps)
    {
        $controller_path = $targetPath . '\\app\\shop\\controller\\plus';

        //所有应用
        $allApps = $this->file->dir_info($controller_path);

        //保留的插件
        $plusApp = $plusApps;

        foreach ($allApps as &$plus) {
            if ($plus == '.' || $plus == '..') continue;
            $plus = "/" . $plus;
            $path = $controller_path . $plus;

            if (is_dir($path)) {
                if (!in_array($plus, $plusApp)) {
                    $this->file->remove_dir($path);
                }
            }
        }

        return ['code' => 'success', 'msg' => '文件处理成功'];
    }


    public function data($path, $targetDbName, $originDbName)
    {
        $this->copyDatabase($path, $targetDbName, $originDbName);

    }

    private function copyDatabase($path, $targetDbName, $originDbName)
    {
        $backUpService=new Backup($path.DIRECTORY_SEPARATOR, $targetDbName, $originDbName);
        $backUpService->backupAll();
        $result=$backUpService->restore();

    }

    /**
     * 清理根目录文件
     * @param $path 目录文件
     */
    private function clearRootDir($path)
    {
        if (empty($path)) return false;

        $targetPath = $path;
        //要拷贝的文件
        $mustFiles = [
            'version.json',
            'think',
            'index.html',
            'index.php',
            'composer.json',
            '404.html',
        ];
        //要拷贝的目录
        $mustDir = [
            'app',
            'config',
            'public',
            'route',
            'vendor'
        ];

        $path = $this->file->check_path($targetPath);
        $files = $this->file->dir_info($path);
        foreach ($files as $file) {
            if ($file == "." || $file == "..") continue;

            //检查文件
            if (!is_dir($path . $file)) {
                if (!in_array($file, $mustFiles)) {
                    $this->file->unlink_file($path . $file);
                }
            } else {
                //检查目录
                if (!in_array($file, $mustDir)) {
                    try {
                        $this->file->remove_dir($path . $file);
                    } catch (\Exception $exception) {
                        dump($exception->getMessage());
                    }

                }
            }
        }
    }

    /**
     * 清理public目录下无关文件
     * @param $path
     * @return bool
     */
    private function clearPublic($path)
    {
        if (empty($path)) return false;

        $files = [
            '.htaccess', 'favicon.ico', 'index.php', 'router.php', 'robots.txt', 'kmd.php'
        ];
        $dirs = [
            'admin', 'agent', 'shop', 'h5', 'image'
        ];
        $targetPath = $path . '/public/';

        $path = $this->file->check_path($targetPath);
        $fileInfo = $this->file->dir_info($path);
        foreach ($fileInfo as $file) {

            if ($file == "." || $file == '..') continue;

            //检查文件
            if (!is_dir($path . $file)) {
                if (!in_array($file, $files)) {
                    $this->file->unlink_file($path . $file);
                }
            } else {
                //检查目录
                if (!in_array($file, $dirs)) {
                    try {
                        $this->file->remove_dir($path . $file);
                    } catch (\Exception $exception) {
                        dump($exception->getMessage());
                    }

                } else {
                    //子目录
                    foreach ($this->file->dir_info($path . $file . '/') as $v) {
                        if ($v == '.' || $v == '..') continue;

                        if ($v !== "static" && $v !== "index.html" && $v !== 'diy') {

                            $moveFile = $path . $file . '/' . $v;
                            if (is_dir($moveFile)) {
                                $this->file->remove_dir($moveFile);
                            } else {
                                $this->file->unlink_file($moveFile);
                            }

                        }
                    }
                }
            }
        }
    }

    /**
     * 生成配置文件
     * @param $path
     */
    private function buildEnv($path)
    {
        $path = $path . "\\.env";
        $config = Config::get('packing');

        $content = $config['envTemplate']['free'];

        $dbName = substr(str_replace('.', '_', $this->siteName), 0, 16);
        $dbPrefix = $config['database']['prefix'];

        $content = sprintf($content, $dbName, $dbPrefix);
        $this->file->write_file($path, $content);
    }
}