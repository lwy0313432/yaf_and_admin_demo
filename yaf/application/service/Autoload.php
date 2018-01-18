<?php
/**
 * @describe:
 * @author: liuwy(liuwy@yindou.com)
 * */

/* vim:set ts=4 sw=4 et fdm=marker: */
spl_autoload_register(function($class){
    $part = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    $dirs = array(
        __DIR__,
        __DIR__ . '/account',
    );  
    foreach ($dirs as $dir) {
        $file = $dir . DIRECTORY_SEPARATOR . $part;
        if (is_readable($file)) {
            Yaf_loader::import($file);
            return;
        }   
    }   
});
