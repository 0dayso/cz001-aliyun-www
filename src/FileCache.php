<?php

/**
 * Created by PhpStorm.
 * User: hefish
 * Date: 2015/12/26
 * Time: 下午11:05
 */



class FileCache
{
    public $cache_dir;
    private $_ttl = 3600;

    function __construct($cache_dir) {
        $this->cache_dir = $cache_dir;
    }

    public function get_hash_path($key) {
        if (empty($key)) {
            return null;
        }
        $checksum = md5($key);
        return "{$checksum[0]}/{$checksum[1]}/{$checksum}.cache";
    }

    public function get($key) {
        // $key is a file path
        assert(! empty($this->cache_dir));
        assert(! empty($key));
        $cache_file = sprintf("%s/%s", $this->cache_dir, $this->get_hash_path($key));

        if (file_exists($cache_file)) {
            $st = stat($cache_file);
            if ($st['ctime'] + $this->_ttl < time()) {
                //失效了
                return false;
            }
            else {
                $value = file_get_contents($cache_file);
                return $value;
            }
        }
        else {
            return false;
        }
    }

    public function put($key, $value) {
        assert(! empty($this->cache_dir));
        assert(! empty($key));
        $cache_file = sprintf("%s/%s", $this->cache_dir, $this->get_hash_path($key));
        $cache_file_path = dirname($cache_file);
        if (!file_exists($cache_file_path)) {
            //建目录
            mkdir($cache_file_path, 0777, true);
        }
        file_put_contents($cache_file, $value);
        return true;
    }

    public function invalidate($key) {
        //让$key失效，同时删除文件
        $cache_file = sprintf("%s/%s", $this->cache_dir, $this->get_hash_path($key));
        unlink($cache_file);
        return true;
    }
}