<?php

/**
 * Created by PhpStorm.
 * User: hefish
 * Date: 2016/1/2
 * Time: 17:24
 */


require_once "config.php";

class CMSSyncer
{
    private $_pid_file ;

    function __construct()
    {
        $pi = pathinfo(\$argv[0]);
        $this->_pid_file = __DIR__ . "/" . $pi['filename']. ".pid";
    }

    public function start()
    {
        global $config;
    }

    public function go_loop()
    {
        global $config;

        while(true) {
            /**
             * 1. get last log time
             * 2. read log file
             * 3. skip lines to last log time
             * 4. read lines
             * 5. parse updated files
             * 6. upload to aliyun oss
             * 7. goto 1
             */
            $last_time = $this->get_last_log_time();

            //parse cmslog
            $publish_log_file = $config['cms40']['publish_log'];
            if (! file_exists($publish_log_file)) {
                $this->log("publish log file: $publish_log_file not found. \n");
                exit();
            }

            $fp = fopen($publish_log_file, "r");
            $this->skip_cmslog($fp, $last_time['last_cmslog_time']);
            $last_cmslog_time = $this->parse_cmslog($fp);


        }
    }

    /**
     * parse cmslog file for updated files.
     *
     * @param resouce $fp file pointer for cmslog file
     * @return int returns lastest time of updated file or now.
     */
    public function parse_cmslog($fp) {
        $m = array();

        while(! feof($fp)) {
            $line = fread($fp, 1024);
            $m = $this->split_cmslog_line($line);
            if ($m === null) continue;

            if (isset($m['cms_file'])) $this->upload_to_oss($m['cms_file']);
        }

        if (isset($m['cms_time'])) {
            return $m['cms_time'];
        }
        else {
            return time();
        }
    }

    public function upload_to_oss($cms_file) {
        global $config;

        $real_path = sprintf("%s%s", $config['cms40']['publish_root'], $cms_file );
        if (! file_exists($real_path)) {
            $this->log("upload failed: $real_path not exists \n ");
            return ;
        }

        $st = stat($real_path);
        //TODO: 未完.

    }

    /**
     * skip cmslog lines that had been processed in previous operation
     *
     * @param resouce $fp file pointer for cmslog file
     * @param float   $last_cmslog_time   last processed time in float
     */
    public function skip_cmslog($fp, $last_cmslog_time) {

        while (! feof($fp)) {
            $line = fread($fp, 1024);
            $m = $this->split_cmslog_line($line);
            if ($m === null) continue;

            if ($m['cms_time'] > $last_cmslog_time ) {
                break;
            }
        }

        return ;
    }

    /**
     * split cmslog line into several parts.
     *
     * @param string $line  cmslog line in string
     * @return array|null   array consists of cms_time and cms_file or null if split failed
     */
    public function split_cmslog_line($line) {
        global $config;

        $m = preg_split("/[\s,]+/", $line);
        if (count($m) < 9) {
            $this->log(" invalid cmslog line: $line \n");
            return null;
        }
        $d = $m[0]; //date
        $t = $m[1]; //time
        $microsecond = $m[2];
        $publish_root = addcslashes($config['cms40']['publish_root'], "/");
        $r = preg_match("/.*{$publish_root}(.*)$/", $m[8], $f);
        if (! $r) {
            $this->log("invalid cmslog item: {$m[8]} \n");
            return null;
        }
        $cms_file = $f[1]; //cms40 file

        $time_click = strtotime("$d $t") + $microsecond/1000.0;

        return array(
            "cms_time" => $time_click,
            "cms_file" => $cms_file,
        );
    }

    public function get_last_log_time() {
        global $config;

        $pid_file = $config['cms40']['import_pid_file'];
        if (! file_exists($pid_file)) {
            //create pid file
            $data = array(
                "pid" => posix_getpid(),
                "last_cmslog_time" => 0.0,
                "last_sftp_time" => 0.0,
            );
            $this->put_last_log_time($data);
        }
        else {
            $data = unserialize(file_get_contents($pid_file));
        }


        return $data;
    }

    public function put_last_log_time(array $data) {
        global $config;

        $pid_file = $config['cms40']['import_pid_file'];
        $data['pid'] = posix_getpid();
        file_put_contents($pid_file, serialize($data));
    }

    public function log($str) {
        global $config;

        $log_file = sprintf($config['cms40']['sync_logger'], date("Y-m-d"));
        $fp = fopen($log_file, "a");
        $time_stamp = date("Y-m-d H:i:s");
        $line = sprintf("%s %s", $time_stamp, $str);
        fwrite($fp, $line);
        fclose($fp);
    }
}





$o = new CMSSyncer();
$o->start();
