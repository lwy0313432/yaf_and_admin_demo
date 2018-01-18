<?php
/**
 * @name Bootstrap
 * @author www
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * @see http://www.php.net/manual/en/class.yaf-bootstrap-abstract.php
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf_Bootstrap_Abstract {

    public function _initConfig() {
        //把配置保存起来
        $arrConfig = Yaf_Application::app()->getConfig();
        Yaf_Registry::set('config', $arrConfig);
    }

    public function _initPlugin(Yaf_Dispatcher $dispatcher) {
        //注册一个插件
        $objSamplePlugin = new SamplePlugin();
        $dispatcher->registerPlugin($objSamplePlugin);
    }

    public function _initRoute(Yaf_Dispatcher $dispatcher) {
        //在这里注册自己的路由协议,默认使用简单路由
        $router = Yaf_Dispatcher::getInstance()->getRouter();
        $routeRules = Config::getCustomerRoute();
        foreach($routeRules as $key => $item){
            $route = new Yaf_Route_Regex($item['regex'],$item['control'],$item['param']);
            $router->addRoute($key, $route);
        }
    }

    /*
     *把service层加载进来。service下面如果新增目录，需要在修改/application/service/autoload.php里面，将目录新增进去
     */
    public function _initService(Yaf_Dispatcher $dispatcher){   
        Yaf_loader::import(APPLICATION_PATH . '/application/service/Autoload.php');
    }
    public function _initView(Yaf_Dispatcher $dispatcher) {
        //在这里注册自己的view控制器，例如smarty,firekylin
        if (php_sapi_name() == 'cli') {
            return false;
        }
        $smarty = new Smarty_Adapter(null, Config::getSmartyConf()); 
        Yaf_Registry::set('smarty', $smarty);
        $dispatcher->setView($smarty);
        Yaf_Dispatcher::getInstance()->autoRender(false);
    }
    public function _initErrorHandle($dispatcher){
        CErrorHandler::setCustomErrorHandler();
    }
}
