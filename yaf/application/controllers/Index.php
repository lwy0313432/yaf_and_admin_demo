<?php
/**
 * @name IndexController
 * @author www
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends Yaf_Controller_Abstract {

	/** 
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/personal_loan/index/index/index/name/www 的时候, 你就会发现不同
     */
    public $actions=array(
        'index'=>'actions/web/Index.php',
    );
}
