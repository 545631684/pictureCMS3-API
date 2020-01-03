<?php
namespace Home\Controller;
use Think\Controller;
use Org\Util\Tool;


class ControllerController extends Controller {

    public function __construct() 
    {
        parent::__construct();
		// 初始化工具类
		$this->tool = new Tool;
    }
}