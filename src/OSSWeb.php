<?php
/**
 * Created by PhpStorm.
 * User: hefish
 * Date: 2015/12/26
 * Time: 下午11:08
 */

/**
 * 将web请求rewrite
 *
 * ^(.*)$ =>  /OSSWeb.php?object=$1
 */


class OSSWeb {
    protected $server, $request;
    protected $server_name;
    protected $request_uri;
    function __construct() {
        global $config;

        $this->server = $_SERVER;
        $this->request = $_REQUEST;

        $this->server_name = $this->server['SERVER_NAME'];
        if (isset($this->request['object'])) {
	    if ($this->request['object'] == "/") {
		$this->request_uri = "/".$config['site'][$this->server_name]['index'];
	    }
	    else 
            	$this->request_uri = $this->request['object'];
        }
        else {
            $this->request_uri = $config['site'][$this->server_name]['index'];
        }
    }

    public function index() {
        global $config;

        if (! in_array($this->server_name, array_keys($config['site']))) {
            header("Invalid hostname", true, 500);
            exit;
        }
        $site = $config['site'][$this->server_name];

        $res_path = sprintf("%s%s", $site['www_root'], $this->request_uri);
        $oss_file = new WebFile($config['aliyun_oss']['bucket'], $res_path);

        // read web file content
        $file_object = $oss_file->get_content();
        switch ($file_object['status']) {
            case 404:
                header("HTTP/1.1 404 Not Found", true, 404);
		print "resource:$res_path not found.";
                break;
            case 200:
                $pi = pathinfo($res_path); $ext = $pi['extension'];
                if (in_array($ext, array_keys($config['mime-type']))) {
                    $content_type = $config['mime-type'][$ext];
                }
                else {
                    $content_type = "application/octet-stream";
                }

                Header("Content-Type: {$content_type}");
                print $file_object['content'];
                break;
        }

    }
}

