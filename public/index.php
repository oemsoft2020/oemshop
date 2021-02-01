<?php
if (version_compare(PHP_VERSION, '7.1', '<')) {
    echo '您的PHP版本小于7.1,当前版本为 ' . PHP_VERSION;
    die;
}
require __DIR__ . './kmd.php';
