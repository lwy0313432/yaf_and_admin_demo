<?php
/**
 * @package yindou
 * @brief  model 中间件 操作
 * @author weixiaotong <weixt@yindou.com>
 * @date 2016-04-10
 * @encoding UTF-8
 * @copyright (c) yindou
 */

class Model_Relation extends Model_Abstract
{
    private $table_name='';


    public function __construct($table_name, $pk, $db_conf_name)
    {
        $this->table_name=$table_name;
        parent::__construct($table_name, $pk, $db_conf_name);
    }

    
    public function Insert($data)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }
        $ret=$this->add($data);
        if (!$ret) {
            WLog::notice('[error]'.$this->getError().'[sql]' . $this->getLastSql());
        }
        return $ret;
    }
    
    public function Update($arr_where, $data)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }
        if (empty($arr_where) || !is_array($arr_where)) {
            return false;
        }
        $ret = $this->where($arr_where)->save($data);
        if (!$ret) {
            WLog::notice('[error]'.$this->getError().'[sql]' . $this->getLastSql());
        }
        return $ret;
    }
    
    public function Del($arr_where)
    {
        if (empty($arr_where) || !is_array($arr_where)) {
            return false;
        }
        $ret=$this->delete($arr_where);
        if (!$ret) {
            WLog::notice('[error]'.$this->getError().'[sql]' . $this->getLastSql());
        }
        return $ret;
    }
    //直接传输sql
    public function Fetch($sql, $master=true)
    {
        if (empty($sql)) {
            return false;
        }
           //if(php_sapi_name() != "cli") return false; //命令行下不执行
        $query=$this->query($sql, $master);
        if (!$query) {
            WLog::notice('[error]'.$this->getError().'[sql]' . $this->getLastSql());
        }
        return $query;
    }
    
    //开启事务
    public function start_trans()
    {
        $this->startTrans();
    }
    //提交事务
    public function commit_trans()
    {
        $this->commit();
    }
    //回滚事务
    public function rollback_trans()
    {
        $this->rollback();
    }
}
