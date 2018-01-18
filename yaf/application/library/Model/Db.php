<?php

/**
 * @package yindou
 * @brief Db数据库中间层类
 * @author weixiaotong
 * @date 2016-3-29
 * @encoding UTF-8
 * @copyright (c) yindou
 */

class Model_Db
{
    // 数据库类型
    protected $dbType     = 'MYSQL';
    protected $db         = null;
    
    // 是否自动释放查询结果
    protected $autoFree   = false;
    // 当前操作所属的模型名
    protected $model      = '_yindou_';
    // 是否使用永久连接
    protected $pconnect   = false;
    // 当前SQL指令
    protected $queryStr   = '';
    protected $modelSql   = array();
    // 最后插入ID
    protected $lastInsID  = null;
    // 返回或者影响记录数
    protected $numRows    = 0;
    // 返回字段数
    protected $numCols    = 0;
    // 事务指令数
    protected $transTimes = 0;
    // 错误信息
    protected $error      = '';
    // 数据库连接ID 支持多个连接
    protected $linkID     = array();
    // 当前连接ID
    protected $_linkID    = null;
    // 当前查询ID
    protected $queryID    = null;
    // 是否已经连接数据库
    protected $connected  = false;
    // 数据库连接参数配置
    protected $config     = '';
    // 数据库表达式
    protected $comparison = array('eq'=>'=','neq'=>'<>','gt'=>'>','egt'=>'>=','lt'=>'<','elt'=>'<=','notlike'=>'NOT LIKE','like'=>'LIKE','in'=>'IN','notin'=>'NOT IN');
    // 查询表达式
    protected $selectSql  = 'SELECT%DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%%LIMIT% %UNION%%COMMENT%';
    // 参数绑定
    protected $bind       = array();
    
    protected $options    = array();
    
    protected $master     =array();
    
    protected $slave      =array();
    
    protected $master_type ='w';
    
    protected $slave_type  ='r';
    
    private static $affair=array(); //如果启动 事务，就 让所有的 sql 走主库，
    
    private $db_conf_name='';
    
    /**
     * 预留
     */
    public function __construct($db_conf_name)
    {
        $this->db_conf_name=$db_conf_name;
    }

    /**
     * 链接主库
     */
    public function useMaster()
    {
        $this->db=Common_Multi::getInstance($this->db_conf_name, $this->master_type);
    }
    /**
     * 链接从库
     */
    public function useSlave()
    {
        $this->db=Common_Multi::getInstance($this->db_conf_name, $this->slave_type);
    }
    
    /**
     * 数据库调试 记录当前SQL
     * @access protected
     */
    protected function debug()
    {
        $this->modelSql[$this->model]   =  $this->queryStr;
        $this->model  =   '_yindou_';
        // 记录操作结束时间
        if (Model_Config::getDbGlobalConf('db_sql_log')) {
            WLog::debug('[DEBUG SQL]'.$this->queryStr, array());
        }
    }
    
    /**
     * 初始化数据库连接
     * @access protected
     * @param boolean
     * @return void
     */
    protected function initConnect()
    {
        $this->_linkID = $this->connect();
    }
    

    /**
     * 设置锁机制  暂 支持 innodb  有 主键锁行，无主键锁表、、慎用
     * @access protected
     * @return string
     */
    protected function parseLock($lock=false)
    {
        if (!$lock) {
            return '';
        }
        return ' FOR UPDATE ';
    }

    /**
     * set分析
     * @access protected
     * @param array $data
     * @return string
     */
    protected function parseSet($data)
    {
        foreach ($data as $key=>$val) {
            if (is_array($val) && 'exp' == $val[0]) {
                $set[]  =   $this->parseKey($key).'='.$val[1];
            } elseif (is_scalar($val) || is_null($val)) { // 过滤非标量数据
              if (Model_Config::getDbGlobalConf('db_bind_param') && 0 !== strpos($val, ':')) {
                  $name   =   md5($key);
                  $set[]  =   $this->parseKey($key).'=:'.$name;
                  $this->bindParam($name, $val);
              } else {
                  $set[]  =   $this->parseKey($key).'='.$this->parseValue($val);
              }
            }
        }
        return ' SET '.implode(',', $set);
    }

     /**
     * 参数绑定
     * @access protected
     * @param string $name 绑定参数名
     * @param mixed $value 绑定值
     * @return void
     */
    protected function bindParam($name, $value)
    {
        $this->bind[':'.$name]  =   $value;
    }

     /**
     * 参数绑定分析
     * @access protected
     * @param array $bind
     * @return array
     */
    protected function parseBind($bind)
    {
        $bind           =   array_merge($this->bind, $bind);
        $this->bind     =   array();
        return $bind;
    }

    /**
     * 字段名分析
     * @access protected
     * @param string $key
     * @return string
     */
    protected function parseKey(&$key)
    {
        return $key;
    }
    
    /**
     * value分析
     * @access protected
     * @param mixed $value
     * @return string
     */
    protected function parseValue($value)
    {
        if (is_string($value)) {
            $value =  '\''.$this->escapeString($value).'\'';
        } elseif (isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp') {
            $value =  $this->escapeString($value[1]);
        } elseif (is_array($value)) {
            $value =  array_map(array($this, 'parseValue'), $value);
        } elseif (is_bool($value)) {
            $value =  $value ? '1' : '0';
        } elseif (is_null($value)) {
            $value =  'null';
        }
        return $value;
    }
    
    /**
     * bind的field分析
     * @access protected
     * @param mixed $value
     * @return string
     */
    protected function parseBindFiled($name)
    {
        $bindField = md5($name);
        if (isset($this->bind[":$bindField"])) {
            return $this->parseBindFiled($bindField);
        }
        return $bindField;
    }

    /**
     * field分析
     * @access protected
     * @param mixed $fields
     * @return string
     */
    protected function parseField($fields)
    {
        if (is_string($fields) && strpos($fields, ',')) {
            $fields    = explode(',', $fields);
        }
        if (is_array($fields)) {
            // 完善数组方式传字段名的支持
            // 支持 'field1'=>'field2' 这样的字段别名定义
            $array   =  array();
            foreach ($fields as $key=>$field) {
                if (!is_numeric($key)) {
                    $array[] =  $this->parseKey($key).' AS '.$this->parseKey($field);
                } else {
                    $array[] =  $this->parseKey($field);
                }
            }
            $fieldsStr = implode(',', $array);
        } elseif (is_string($fields) && !empty($fields)) {
            $fieldsStr = $this->parseKey($fields);
        } else {
            $fieldsStr = '*';
        }
        //TODO 如果是查询全部字段，并且是join的方式，那么就把要查的表加个别名，以免字段被覆盖
        return $fieldsStr;
    }

    /**
     * table分析
     * @access protected
     * @param mixed $table
     * @return string
     */
    protected function parseTable($tables)
    {
        if (is_array($tables)) {// 支持别名定义
            $array   =  array();
            foreach ($tables as $table=>$alias) {
                if (!is_numeric($table)) {
                    $array[] =  $this->parseKey($table).' '.$this->parseKey($alias);
                } else {
                    $array[] =  $this->parseKey($table);
                }
            }
            $tables  =  $array;
        } elseif (is_string($tables)) {
            $tables  =  explode(',', $tables);
            array_walk($tables, array(&$this, 'parseKey'));
        }
        $tables = implode(',', $tables);
        return $tables;
    }

    /**
     * where分析
     * @access protected
     * @param mixed $where
     * @return string
     */
    protected function parseWhere($where)
    {
        $whereStr = '';
        if (is_string($where)) {
            
            // 直接使用字符串条件
            $whereStr = $where;
        } else { // 使用数组表达式
            $operate  = isset($where['_logic'])?strtoupper($where['_logic']):'';
            if (in_array($operate, array('AND','OR','XOR'))) {
                // 定义逻辑运算规则 例如 OR XOR AND NOT
                $operate    =   ' '.$operate.' ';
                unset($where['_logic']);
            } else {
                // 默认进行 AND 运算
                $operate    =   ' AND ';
            }
            foreach ($where as $key=>$val) {
                $whereStr .= '( ';
                if (is_numeric($key)) {
                    $key  = '_complex';
                }
                if (0===strpos($key, '_')) {
                    // 解析特殊条件表达式
                    $whereStr   .= $this->parseYdWhere($key, $val);
                } else {
                    // 查询字段的安全过滤
                    if (!preg_match('/^[A-Z_\|\&\-.a-z0-9\(\)\,]+$/', trim($key))) {
                        throw new Exception('_EXPRESS_ERROR_:'.$key);
                    }
                    // 多条件支持
                    $multi  = is_array($val) &&  isset($val['_multi']);
                    $key    = trim($key);
                    if (strpos($key, '|')) { // 支持 name|title|nickname 方式定义查询字段
                        $array =  explode('|', $key);
                        $str   =  array();
                        foreach ($array as $m=>$k) {
                            $v =  $multi?$val[$m]:$val;
                            $str[]   = '('.$this->parseWhereItem($this->parseKey($k), $v).')';
                        }
                        $whereStr .= implode(' OR ', $str);
                    } elseif (strpos($key, '&')) {
                        $array =  explode('&', $key);
                        $str   =  array();
                        foreach ($array as $m=>$k) {
                            $v =  $multi?$val[$m]:$val;
                            $str[]   = '('.$this->parseWhereItem($this->parseKey($k), $v).')';
                        }
                        $whereStr .= implode(' AND ', $str);
                    } else {
                        $whereStr .= $this->parseWhereItem($this->parseKey($key), $val);
                    }
                }
                $whereStr .= ' )'.$operate;
            }
            $whereStr = substr($whereStr, 0, -strlen($operate));
        }
        return empty($whereStr)?'':' WHERE '.$whereStr;
    }

    
    //neq 不等于   eq 相等  gt大于 egt 大于等于  lt 小于 elt 小于等于
    // where子单元分析
    protected function parseWhereItem($key, $val)
    {
        $whereStr = '';
        if (is_array($val)) {
            if (is_string($val[0])) {
                if (preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT)$/i', $val[0])) { // 比较运算
                    if (Model_Config::getDbGlobalConf('db_bind_param')) {
                        $bindField = $this->parseBindFiled($key);
                        $whereStr .= $key.' '.$this->comparison[strtolower($val[0])].' :'.$bindField;
                        $this->bindParam($bindField, $val[1]);
                    } else {
                        $whereStr .= $key.' '.$this->comparison[strtolower($val[0])].' '.$this->parseValue($val[1]);
                    }
                } elseif (preg_match('/^(NOTLIKE|LIKE)$/i', $val[0])) {// 模糊查找
                    if (is_array($val[1])) {
                        $likeLogic  =   isset($val[2])?strtoupper($val[2]):'OR';
                        if (in_array($likeLogic, array('AND','OR','XOR'))) {
                            $likeStr    =   $this->comparison[strtolower($val[0])];
                            $like       =   array();
                            foreach ($val[1] as $item) {
                                if (Model_Config::getDbGlobalConf('db_bind_param')) {
                                    $bindField = $this->parseBindFiled($key);
                                    $like[] = $key.' '.$likeStr.' :'.$bindField;
                                    $this->bindParam($bindField, $item);
                                } else {
                                    $like[] = $key.' '.$likeStr.' '.$this->parseValue($item);
                                }
                            }
                            $whereStr .= '('.implode(' '.$likeLogic.' ', $like).')';
                        }
                    } else {
                        if (Model_Config::getDbGlobalConf('db_bind_param')) {
                            $bindField = $this->parseBindFiled($key);
                            $whereStr .= $key.' '.$this->comparison[strtolower($val[0])].' :'.$bindField;
                            $this->bindParam($bindField, $val[1]);
                        } else {
                            $whereStr .= $key.' '.$this->comparison[strtolower($val[0])].' '.$this->parseValue($val[1]);
                        }
                    }
                } elseif ('exp'==strtolower($val[0])) { // 使用表达式
                    $whereStr .= ' ('.$key.' '.$val[1].') ';
                } elseif (preg_match('/IN/i', $val[0])) { // IN 运算
                    if (isset($val[2]) && 'exp'==$val[2]) {
                        $whereStr .= $key.' '.strtoupper($val[0]).' '.$val[1];
                    } else {
                        if (is_string($val[1])) {
                            $val[1] =  explode(',', $val[1]);
                        }
                        $zone      =   implode(',', $this->parseValue($val[1]));
                        $whereStr .= $key.' '.strtoupper($val[0]).' ('.$zone.')';
                    }
                } elseif (preg_match('/BETWEEN/i', $val[0])) { // BETWEEN运算
                    $data = is_string($val[1])? explode(',', $val[1]):$val[1];
                    if (Model_Config::getDbGlobalConf('db_bind_param')) {
                        $bindField = $this->parseBindFiled($key);
                        $this->bindParam($bindField, $data[0]);
                        $bindField2 = $this->parseBindFiled($key);
                        $this->bindParam($bindField2, $data[1]);
                        $whereStr .=  ' ('.$key.' '.strtoupper($val[0]).' :'.$bindField.' AND :'.$bindField2.' )';
                    } else {
                        $whereStr .=  ' ('.$key.' '.strtoupper($val[0]).' '.$this->parseValue($data[0]).' AND '.$this->parseValue($data[1]).' )';
                    }
                } else {
                    throw new Exception('_EXPRESS_ERROR_ :'.$val[0]);
                }
            } else {
                $count = count($val);
                $rule  = isset($val[$count-1]) ? (is_array($val[$count-1]) ? strtoupper($val[$count-1][0]) : strtoupper($val[$count-1])) : '' ;
                if (in_array($rule, array('AND','OR','XOR'))) {
                    $count  = $count -1;
                } else {
                    $rule   = 'AND';
                }
                for ($i=0;$i<$count;$i++) {
                    $data = is_array($val[$i])?$val[$i][1]:$val[$i];
                    if ('exp'==strtolower($val[$i][0])) {
                        $whereStr .= '('.$key.' '.$data.') '.$rule.' ';
                    } else {
                        $whereStr .= '('.$this->parseWhereItem($key, $val[$i]).') '.$rule.' ';
                    }
                }
                $whereStr = substr($whereStr, 0, -4);
            }
        } else {
            //对字符串类型字段采用模糊匹配
            if (Model_Config::getDbGlobalConf('db_like_fields') &&
                    preg_match('/('.Model_Config::getDbGlobalConf('db_like_fields').')/i', $key)) {
                $val  =  '%'.$val.'%';
                if (Model_Config::getDbGlobalConf('db_bind_param')) {
                    $bindField = $this->parseBindFiled($key);
                    $whereStr .= $key.' LIKE :'.$bindField;
                    $this->bindParam($bindField, $val);
                } else {
                    $whereStr .= $key.' LIKE '.$this->parseValue($val);
                }
            } else {
                if (Model_Config::getDbGlobalConf('db_bind_param')  && !isset($this->options['bind'][":".trim($key, '`')])) {
                    $bindField = $this->parseBindFiled($key);
                    $whereStr .= $key.' = :'.$bindField;
                    $this->bindParam($bindField, $val);
                } else {
                    $whereStr .= $key.' = '.$this->parseValue($val);
                }
            }
        }
        return $whereStr;
    }

    /**
     * 特殊条件分析
     * @access protected
     * @param string $key
     * @param mixed $val
     * @return string
     */
    protected function parseYdWhere($key, $val)
    {
        $whereStr   = '';
        switch ($key) {
            case '_string':
                // 字符串模式查询条件
                $whereStr = $val;
                break;
            case '_complex':
                // 复合查询条件
                $whereStr   =   is_string($val)? $val : substr($this->parseWhere($val), 6);
                break;
            case '_query':
                // 字符串模式查询条件
                parse_str($val, $where);
                if (isset($where['_logic'])) {
                    $op   =  ' '.strtoupper($where['_logic']).' ';
                    unset($where['_logic']);
                } else {
                    $op   =  ' AND ';
                }
                $array   =  array();
                if (Model_Config::getDbGlobalConf('db_bind_param')) {
                    foreach ($where as $field=>$data) {
                        $bindField = $this->parseBindFiled($key);
                        $array[] = $this->parseKey($field).' = :'.$bindField;
                        $this->bindParam($bindField, $data);
                    }
                } else {
                    foreach ($where as $field=>$data) {
                        $array[] = $this->parseKey($field).' = '.$this->parseValue($data);
                    }
                }
                $whereStr   = implode($op, $array);
                break;
        }
        return $whereStr;
    }

    /**
     * limit分析
     * @access protected
     * @param mixed $lmit
     * @return string
     */
    protected function parseLimit($limit)
    {
        return !empty($limit)?   ' LIMIT '.$limit.' ':'';
    }

    /**
     * join分析
     * @access protected
     * @param array $join
     * @return string
     */
    protected function parseJoin($join)
    {
        $joinStr = '';
        if (!empty($join)) {
            $joinStr    =   ' '.implode(' ', $join).' ';
        }
        return $joinStr;
    }

    /**
     * order分析
     * @access protected
     * @param mixed $order
     * @return string
     */
    protected function parseOrder($order)
    {
        if (is_array($order)) {
            $array   =  array();
            foreach ($order as $key=>$val) {
                if (is_numeric($key)) {
                    $array[] =  $this->parseKey($val);
                } else {
                    $array[] =  $this->parseKey($key).' '.$val;
                }
            }
            $order   =  implode(',', $array);
        }
        return !empty($order)?  ' ORDER BY '.$order:'';
    }

    /**
     * group分析
     * @access protected
     * @param mixed $group
     * @return string
     */
    protected function parseGroup($group)
    {
        return !empty($group)? ' GROUP BY '.$group:'';
    }

    /**
     * having分析
     * @access protected
     * @param string $having
     * @return string
     */
    protected function parseHaving($having)
    {
        return  !empty($having)?   ' HAVING '.$having:'';
    }

    /**
     * comment分析
     * @access protected
     * @param string $comment
     * @return string
     */
    protected function parseComment($comment)
    {
        return  !empty($comment)?   ' /* '.$comment.' */':'';
    }

    /**
     * distinct分析
     * @access protected
     * @param mixed $distinct
     * @return string
     */
    protected function parseDistinct($distinct)
    {
        return !empty($distinct)?   ' DISTINCT ' :'';
    }

    /**
     * union分析
     * @access protected
     * @param mixed $union
     * @return string
     */
    protected function parseUnion($union)
    {
        if (empty($union)) {
            return '';
        }
        if (isset($union['_all'])) {
            $str  =   'UNION ALL ';
            unset($union['_all']);
        } else {
            $str  =   'UNION ';
        }
        foreach ($union as $u) {
            $sql[] = $str.(is_array($u)?$this->buildSelectSql($u):$u);
        }
        return implode(' ', $sql);
    }

    /**
     * 插入记录
     * @access public
     * @param mixed $data 数据
     * @param array $options 参数表达式
     * @param boolean $replace 是否replace
     * @return false | integer
     */
    public function insert($data, $options=array(), $replace=false)
    {
        $this->useMaster(); //加载主库配置
        $this->options = $options;
        $values  =  $fields    = array();
        $this->model  =   $options['model'];
        foreach ($data as $key=>$val) {
            if (is_array($val) && isset($val[0]) && 'exp' == $val[0]) {
                $fields[]   =  $this->parseKey($key);
                $values[]   =  $val[1];
            } elseif (is_scalar($val) || is_null($val)) { // 过滤非标量数据
              $fields[]   =  $this->parseKey($key);
                if (Model_Config::getDbGlobalConf('db_bind_param') && 0 !== strpos($val, ':')) {
                    $name       =   md5($key);
                    $values[]   =   ':'.$name;
                    $this->bindParam($name, $val);
                } else {
                    $values[]   =  $this->parseValue($val);
                }
            }
        }
        $sql   =  ($replace?'REPLACE':'INSERT').' INTO '.$this->parseTable($options['table']).' ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
        $sql   .= $this->parseLock(isset($options['lock'])?$options['lock']:false);
        $sql   .= $this->parseComment(!empty($options['comment'])?$options['comment']:'');
        return $this->db->execute($sql, $this->parseBind(!empty($options['bind'])?$options['bind']:array()));
    }

    /**
     * 通过Select方式插入记录
     * @access public
     * @param string $fields 要插入的数据表字段名
     * @param string $table 要插入的数据表名
     * @param array $option  查询数据参数
     * @return false | integer
     */
    public function selectInsert($fields, $table, $options=array())
    {
        $this->options = $options;
        $this->model  =   $options['model'];
        if (is_string($fields)) {
            $fields    = explode(',', $fields);
        }
        array_walk($fields, array($this, 'parseKey'));
        $sql   =    'INSERT INTO '.$this->parseTable($table).' ('.implode(',', $fields).') ';
        $sql   .= $this->buildSelectSql($options);
        return $this->db->execute($sql, $this->parseBind(!empty($options['bind'])?$options['bind']:array()));
    }

    /**
     * 更新记录
     * @access public
     * @param mixed $data 数据
     * @param array $options 表达式
     * @return false | integer
     */
    public function update($data, $options)
    {
        $this->useMaster();
        $this->options = $options;
        $this->model  =   $options['model'];
        $sql   = 'UPDATE '
            .$this->parseTable($options['table'])
            .$this->parseSet($data)
            .$this->parseWhere(!empty($options['where'])?$options['where']:'')
            .$this->parseOrder(!empty($options['order'])?$options['order']:'')
            .$this->parseLimit(!empty($options['limit'])?$options['limit']:'')
            .$this->parseLock(isset($options['lock'])?$options['lock']:false)
            .$this->parseComment(!empty($options['comment'])?$options['comment']:'');
        return $this->db->execute($sql, $this->parseBind(!empty($options['bind'])?$options['bind']:array()));
    }

    /**
     * 删除记录
     * @access public
     * @param array $options 表达式
     * @return false | integer
     */
    public function delete($options=array())
    {
        $this->useMaster();
        $this->options = $options;
        $this->model  =   $options['model'];
        $sql   = 'DELETE FROM '
            .$this->parseTable($options['table'])
            .$this->parseWhere(!empty($options['where'])?$options['where']:'')
            .$this->parseOrder(!empty($options['order'])?$options['order']:'')
            .$this->parseLimit(!empty($options['limit'])?$options['limit']:'')
            .$this->parseLock(isset($options['lock'])?$options['lock']:false)
            .$this->parseComment(!empty($options['comment'])?$options['comment']:'');
        return $this->db->execute($sql, $this->parseBind(!empty($options['bind'])?$options['bind']:array()));
    }

    /**
     * 查找记录
     * @access public
     * @param array $options 表达式
     * @return mixed
     */
    public function select($options=array())
    {
        //判断是否启动 事务
        if (!empty(self::$affair)) {
            $this->useMaster();
        } else {
            $this->useSlave();
        }
        $this->options = $options;
       
        $this->model  =   $options['model'];
        $sql        =   $this->buildSelectSql($options);
        $result     =   $this->db->query($sql, $this->parseBind(!empty($options['bind'])?$options['bind']:array()));
        return $result;
    }

    /**
     * 生成查询SQL
     * @access public
     * @param array $options 表达式
     * @return string
     */
    public function buildSelectSql($options=array())
    {
        if (isset($options['page'])) {
            // 根据页数计算limit
            if (strpos($options['page'], ',')) {
                list($page, $listRows) =  explode(',', $options['page']);
            } else {
                $page = $options['page'];
            }
            $page    =  $page?$page:1;
            $listRows=  isset($listRows)?$listRows:(is_numeric($options['limit'])?$options['limit']:20);
            $offset  =  $listRows*((int)$page-1);
            $options['limit'] =  $offset.','.$listRows;
        }
        $sql  =     $this->parseSql($this->selectSql, $options);
        $sql .=     $this->parseLock(isset($options['lock'])?$options['lock']:false);
        return $sql;
    }

    /**
     * 替换SQL语句中表达式
     * @access public
     * @param array $options 表达式
     * @return string
     */
    public function parseSql($sql, $options=array())
    {
        $sql   = str_replace(
            array('%TABLE%','%DISTINCT%','%FIELD%','%JOIN%','%WHERE%','%GROUP%','%HAVING%','%ORDER%','%LIMIT%','%UNION%','%COMMENT%'),
            array(
                $this->parseTable($options['table']),
                $this->parseDistinct(isset($options['distinct'])?$options['distinct']:false),
                $this->parseField(!empty($options['field'])?$options['field']:'*'),
                $this->parseJoin(!empty($options['join'])?$options['join']:''),
                $this->parseWhere(!empty($options['where'])?$options['where']:''),
                $this->parseGroup(!empty($options['group'])?$options['group']:''),
                $this->parseHaving(!empty($options['having'])?$options['having']:''),
                $this->parseOrder(!empty($options['order'])?$options['order']:''),
                $this->parseLimit(!empty($options['limit'])?$options['limit']:''),
                $this->parseUnion(!empty($options['union'])?$options['union']:''),
                $this->parseComment(!empty($options['comment'])?$options['comment']:'')
            ), $sql);
        return $sql;
    }
    
    /**
     * 启动事务
     * @access public
     * @return void
     */
    public function startTrans()
    {
        $this->useMaster();
        array_push(self::$affair, 'w');
        return  $this->db->startTrans();
    }

    /**
     * 提交事务
     * @access public
     * @return boolean
     */
    public function commit()
    {
        $this->useMaster();
        array_push(self::$affair, '');
        return $this->db->commit();
    }

    /**
     * 事务回滚
     * @access public
     * @return boolean
     */
    public function rollback()
    {
        $this->useMaster();
        array_push(self::$affair, '');
        return $this->db->rollback();
    }
    
    public function query($sql, $master)
    {
        if ($master) {
            $this->useMaster();
        } else {
            $this->useSlave();
        }
        $result     =   $this->db->query($sql, array());
        return $result;
    }

    /**
     * 获取最近一次查询的sql语句
     * @param string $model  模型名
     * @access public
     * @return string
     */
    public function getLastSql($model='')
    {
        return $model?$this->modelSql[$model]:$this->db->queryStr;
    }

    /**
     * 获取最近插入的ID
     * @access public
     * @return string
     */
    public function getLastInsID()
    {
        return $this->db->getLastInsertId();
    }

    /**
     * 获取最近的错误信息
     * @access public
     * @return string
     */
    public function getError()
    {
        return $this->db->error;
    }

    /**
     * SQL指令安全过滤
     * @access public
     * @param string $str  SQL字符串
     * @return string
     */
    public function escapeString($str)
    {
        return addslashes($str);
    }

    /**
     * 设置当前操作模型
     * @access public
     * @param string $model  模型名
     * @return void
     */
    public function setModel($model)
    {
        $this->model =  $model;
    }

   /**
     * 析构方法
     * @access public
     */
    public function __destruct()
    {
        // 释放查询
        if ($this->queryID) {
            $this->free();
        }
        // 关闭连接
        $this->close();
    }

    // 关闭数据库 由驱动类定义
    public function close()
    {
    }
}
