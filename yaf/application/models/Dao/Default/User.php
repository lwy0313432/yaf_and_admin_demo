<?php
/**
 * @describe:user表
 * @author: weixt(weixt@yindou.com)
 * */

/* vim:set ts=4 sw=4 et fdm=marker: */

class Dao_Default_UserModel extends Model_Relation{
    protected $db_conf_name ='db_default';
    protected $table_name   ='user';
    protected $pk='id';
    public function __construct()
    {
        parent::__construct($this->table_name, $this->pk, $this->db_conf_name);
    }
}
