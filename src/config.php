<?php
/**
 * Created by PhpStorm.
 * User: hefish
 * Date: 2015/12/26
 * Time: 下午10:52
 */


require_once __DIR__ . "/../lib/aliyun-oss-php-sdk-2.0.1.phar";

$config = array(
    "aliyun_oss" => array(
        "access_id" => "",
        "access_key" => "",
        "endpoint" => "",
        "bucket" => "cz001-www" ),

    "www_root" => "/var/www/html",

    "cache_root" => __DIR__ . "/cache",

    "site" => array(
        "www.cz001.com.cn" => array( "www_root" => "cz001", "index" => "index.htm"),
        "news.cz001.com.cn" => array( "www_root" => "news" , "index" => "index.htm"),
        "czphoto.cz001.com.cn" => array( "www_root" => "czphoto", "index"=>"index.htm"),
    ),

    "mime-type" => array(
        "htm" => "text/html",
        "html" => "text/html",
        "txt" => "text/plain",
        "jpg" => "image/jpg",
        "gif" => "image/gif",
        "png" => "image/png",
    ),

);

function class_loader($class) {
    $class_file = __DIR__ ."/" . preg_replace("/_/i", PATH_SEPARATOR, $class) . ".php";

    if (file_exists($class_file)) {
        require_once $class_file;
    }
}

spl_autoload_register("class_loader");


