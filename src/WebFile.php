<?php

/**
 * Created by PhpStorm.
 * User: hefish
 * Date: 2015/12/26
 * Time: 下午11:07
 */


use OSS\Core\OssException;

class WebFile
{
    public $file_path;
    public $file_bucket;
    public $status; //200 -ok, 404 - not found
    public $cache;

    function __construct($bucket, $file_path) {
        global $config;
        $this->file_bucket = $bucket;
        $this->file_path = $file_path;
        $this->cache = new FileCache($config['cache_root']);

    }
    public function get_cache_file_name() {
        $cache_file_name = $this->cache->get_hash_path($this->file_path);
        if (file_exists($cache_file_name)) {
            return $cache_file_name;
        }
    }

    public function get_content() {
        $content = $this->cache->get($this->file_path);

        if ($content == null) {
            //不在缓存里，从阿里云oss获取
            $oss = new AliyunOSS();

            $file_object = array();
            try {
                $content = $oss->get_object($this->file_path, $this->file_bucket);
                $file_object = array('status'=>200, 'content'=>$content);
            }
            catch(OssException $e) {
                if (404 == $e->getHTTPStatus()) {
                    //resource not found
                    $content = "";
                    $file_object = array('status'=>404, 'content'=>$content);
                }
                else {
                    $file_object = NULL;
                }
            }

            //缓存之:
            $this->cache->put($this->file_path, serialize($file_object));

            return $file_object;
        }
        else {
            //缓存里有
            $file_object = unserialize($content);
            return $file_object;
        }
    }
}
