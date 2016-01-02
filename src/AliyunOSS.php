<?php

/**
 * Created by PhpStorm.
 * User: hefish
 * Date: 2015/12/26
 * Time: 下午11:00
 */


use OSS\OssClient;
use OSS\Core\OssException;

class AliyunOSS
{

    static private $_oss_client=NULL;

    function __construct() {
        if (NULL == self::$_oss_client) {
            self::$_oss_client = self::get_oss_client();
        }
        if (NULL == self::$_oss_client ) throw new \Exception("cannot connect to oss service");
    }

    static public function get_oss_client() {
        global $config;
        try {
            $oss_client = new OssClient($config['aliyun_oss']['access_id'],
                                        $config['aliyun_oss']['access_key'],
                                        $config['aliyun_oss']['endpoint'] , false);
            return $oss_client;
        }
        catch(OssException $e) {
            printf(__FUNCTION__ . " creating OssClient instance: FAILED \n ");
            printf($e->getMessage()."\n");
            return NULL;
        }
    }

    public function get_object($object_name, $bucket = false) {
        if (false == $bucket) $bucket = self::OSS_BUCKET;

        $content = self::$_oss_client->getObject($bucket, $object_name);

        return $content;
    }

    public function put_object($file_path, $object_name, $bucket = false) {
        if (false == $bucket) $bucket = self::OSS_BUCKET;
        return self::$_oss_client->uploadFile($bucket, $object_name, $file_path );
    }

    public function put_content($content, $object_name, $bucket = false) {
        if (false == $bucket) $bucket = self::OSS_BUCKET;
        return self::$_oss_client->putObject($bucket, $object_name, $content);
    }
}