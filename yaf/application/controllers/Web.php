<?php                                                                                                                      
/**
 *  * @describe:
 *   * @author: liuwy(liuwy@yindou.com)
 *    * */
class WebController extends Yaf_Controller_Abstract{
    public $actions=array(
        'contest_join'=>'actions/web/Contest_join.php',
        'pay_show'=>'actions/web/Pay_show.php',
        'index'=>'actions/web/Index.php',
    );
}
/* vim:set ts=4 sw=4 et fdm=marker: */

