<?php
if (version_compare(PHP_VERSION, '7.1', '<')) {
    echo '您的PHP版本小于7.1,当前版本为 ' . PHP_VERSION;
    die;
}
$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

require_once __DIR__ . '/public/kmd.php';
