<?php

/**
 * Created by PhpStorm.
 * User: hefish
 * Date: 2015/12/27
 * Time: 下午9:29
 */

require_once __DIR__ . "/../src/config.php";

class TestOSSWeb extends PHPUnit_Framework_TestCase
{

    public $server_name;
    public $request_uri;

    protected function setUp() {

    }

    public function testIndex() {
        $_SERVER = array();
        $_SERVER['SERVER_NAME'] = "news.cz001.com.cn";
        $_REQUEST = array();
        $_REQUEST['object'] = "2016-01/02/content_3194516.htm";

        $oos_web = new OSSWeb();
        $oos_web->index();
    }
}
