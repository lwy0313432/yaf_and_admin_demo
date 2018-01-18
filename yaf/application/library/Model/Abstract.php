<?php

/**
 * @package yindou
 * @brief Model模型抽象类
 * @author weixiaotong
 * @date 2016-3-29
 * @encoding UTF-8
 * @copyright yindou
 */

 abstract class Model_Abstract
 {
     // 操作状态
    const MODEL_INSERT          =   1;      //  插入模型数据
    const MODEL_UPDATE          =   2;      //  更新模型数据
    const MODEL_BOTH            =   3;      //  包含上面两种方式
    const EXISTS_VALIDATE       =   0;      // 表单存在字段则验证
    const MUST_VALIDATE         =   1;      // 必须验证
    const VALUE_VALIDATE        =   2;      // 表单值不为空则验证

    // 当前数据库操作对象
    protected $db             =   null;
    // 主键名称
    protected $pk               =   '';
    
    // 主键是否自动增长
    protected $autoinc          =   false;
    // 数据表前缀
    protected $tablePrefix      =   null;
    // 模型名称
    protected $name             =   '';
    // 数据库名称
    protected $dbName           =   '';
    //数据库配置
    protected $connection       =   '';
    // 数据表名（不包含表前缀）
    protected $tableName        =   '';
    // 实际数据表名（包含表前缀）
    protected $trueTableName    =   '';
    // 最近错误信息
    protected $error            =   '';
    // 字段信息
    protected $fields           =   array();
    // 数据信息
    protected $data             =   array();
    // 查询表达式参数
    protected $options          =   array();
     protected $_validate        =   array();  // 自动验证定义
    protected $_auto            =   array();  // 自动完成定义
    protected $_map             =   array();  // 字段映射定义
    protected $_scope           =   array();  // 命名范围定义
    // 是否自动检测数据表字段信息
    protected $autoCheckFields  =   true;
    // 是否批处理验证
    protected $patchValidate    =   false;
    // 链操作方法列表
    protected $methods          =   array('order','alias','having','group','lock','distinct','auto','filter','validate','result','token');

    /**
     * 取得DB类的实例对象 字段检查
     * @access public
     * @param string $table_name 表名称
     * @param string $pk 表主键
     * @param mixed $connection 数据库连接信息
     */
    public function __construct($table_name, $pk, $db_conf_name)
    {
        $this->trueTableName=$table_name;
        $this->pk=$pk;
         
        $getinstance=new Model_Db($db_conf_name);
        $this->db=$getinstance;
    }



    /**
     * 自动检测数据表信息
     * @access protected
     * @return void
     */
    protected function _checkTableInfo()
    {
        // 如果不是Model类 自动记录数据表信息
        // 只在第一次执行记录
        if (empty($this->fields)) {
            // 如果数据表字段没有定义则自动获取
            if (!Model_Config::getDbGlobalConf('flush_table_fields')) {
                throw new Exception(Result::getErrCode(Errno::ERR_DB_TABLE_SCHEMA_NOT_DEFINED), Errno::ERR_DB_TABLE_SCHEMA_NOT_DEFINED);
                return;
            }
            // 每次都会读取数据表信息
            $this->flush();
        } else {
            $this->db->setModel($this->name);
            $this->db->initDbType();
        }
    }

    /**
     * 获取字段信息并缓存
     * @access public
     * @return void
     */
    public function flush()
    {
        // 缓存不存在则查询数据表信息
        $this->db->setModel($this->name);
        $fields =   $this->db->getFields($this->getTableName());
        if (!$fields) { // 无法获取字段信息
            return false;
        }
        $this->fields   =   array_keys($fields);
        foreach ($fields as $key=>$val) {
            // 记录字段类型
            $type[$key]     =   $val['type'];
            if ($val['primary']) {
                $this->pk   =   $key;
                $this->fields['_pk']   =   $key;
                if ($val['autoinc']) {
                    $this->autoinc   =   true;
                }
            }
        }
        // 记录字段类型信息
        $this->fields['_type'] =  $type;
    }

    /**
     * 设置数据对象的值
     * @access public
     * @param string $name 名称
     * @param mixed $value 值
     * @return void
     */
    public function __set($name, $value)
    {
        // 设置数据对象属性
        $this->data[$name]  =   $value;
    }

    /**
     * 获取数据对象的值
     * @access public
     * @param string $name 名称
     * @return mixed
     */
    public function __get($name)
    {
        return isset($this->data[$name])?$this->data[$name]:null;
    }

    /**
     * 检测数据对象的值
     * @access public
     * @param string $name 名称
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * 销毁数据对象的值
     * @access public
     * @param string $name 名称
     * @return void
     */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }
    
    /**
     * 字符串命名风格转换
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     * @param string $name 字符串
     * @param integer $type 转换类型
     * @return string
     */
    protected function _parse_name($name, $type=0)
    {
        if ($type) {
            return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name));
        } else {
            return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
        }
    }

    /**
     * 利用__call方法实现一些特殊的Model方法
     * @access public
     * @param string $method 方法名称
     * @param array $args 调用参数
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (in_array(strtolower($method), $this->methods, true)) {
            // 连贯操作的实现
            $this->options[strtolower($method)] =   $args[0];
            return $this;
        } elseif (in_array(strtolower($method), array('count','sum','min','max','avg'), true)) {
            // 统计查询的实现
            $field =  isset($args[0])?$args[0]:'*';
            return $this->getField(strtoupper($method).'('.$field.') AS tp_'.$method);//@TODO
        } elseif (strtolower(substr($method, 0, 5))=='getby') {
            // 根据某个字段获取记录
            $field   =   $this->_parse_name(substr($method, 5));
            $where[$field] =  $args[0];
            return $this->where($where)->find();
        } elseif (strtolower(substr($method, 0, 10))=='getfieldby') {
            // 根据某个字段获取记录的某个值
            $name   =   $this->_parse_name(substr($method, 10));
            $where[$name] =$args[0];
            return $this->where($where)->getField($args[1]);
        } elseif (isset($this->_scope[$method])) {// 命名范围的单独调用支持
            return $this->scope($method, $args[0]);
        } else {
            throw new Exception(Result::getErrCode(Errno::ERR_DB_MODEL_METHOD_NOT_EXIST), Errno::ERR_DB_MODEL_METHOD_NOT_EXIST);
            return;
        }
    }
    // 回调方法 初始化模型  暂 不用 这种 方法 ccc
  //  abstract protected function _initialize();

    /**
     * 对保存到数据库的数据进行处理
     * @access protected
     * @param mixed $data 要操作的数据
     * @return boolean
     */
     protected function _facade($data)
     {
         // 检查数据字段合法性
        if (!empty($this->fields)) {
            if (!empty($this->options['field'])) {
                $fields =   $this->options['field'];
                unset($this->options['field']);
                if (is_string($fields)) {
                    $fields =   explode(',', $fields);
                }
            } else {
                $fields =   $this->fields;
            }
            foreach ($data as $key=>$val) {
                if (!in_array($key, $fields, true)) {
                    unset($data[$key]);
                } elseif (is_scalar($val)) {
                    // 字段类型检查 和 强制转换ss
                    $this->_parseType($data, $key);
                }
            }
        }
        // 安全过滤
      /*if(!empty($this->options['filter'])) {
            $data = array_map($this->options['filter'],$data);
            unset($this->options['filter']);
        }*/
        $this->_before_write($data);
         return $data;
     }

    // 写入数据前的回调方法 包括新增和更新
    protected function _before_write(&$data)
    {
    }

    /**
     * 新增数据
     * @access public
     * @param mixed $data 数据
     * @param array $options 表达式
     * @param boolean $replace 是否replace
     * @return mixed
     */
    public function add($data='', $options=array(), $replace=false)
    {
        if (empty($data)) {
            // 没有传递数据，获取当前数据对象的值
            if (!empty($this->data)) {
                $data           =   $this->data;
                // 重置数据
                $this->data     = array();
            } else {
                $this->error    = Result::getErrCode(Errno::ERR_DB_DATA_INVILID);
                return false;
            }
        }
        // 分析表达式
        $options    =   $this->_parseOptions($options);
        // 数据处理
        $data       =   $this->_facade($data);
        if (false === $this->_before_insert($data, $options)) {
            return false;
        }
        // 写入数据到数据库
        $result = $this->db->insert($data, $options, $replace);

        if (false !== $result) {
            $insertId   =   $this->getLastInsID();
            if ($insertId) {
                // 自增主键返回插入ID
                $data[$this->getPk()]  = $insertId;
                $this->_after_insert($data, $options);
                return $insertId;
            }
            $this->_after_insert($data, $options);
        }
        return $result;
    }
    // 插入数据前的回调方法
    protected function _before_insert(&$data, $options)
    {
    }
    // 插入成功后的回调方法
    protected function _after_insert($data, $options)
    {
    }

     public function addAll($dataList, $options=array(), $replace=false)
     {
         if (empty($dataList)) {
             $this->error =  Result::getErrCode(Errno::ERR_DB_DATA_INVILID);
             return false;
         }
        // 分析表达式
        $options =  $this->_parseOptions($options);
        // 数据处理
        foreach ($dataList as $key=>$data) {
            $dataList[$key] = $this->_facade($data);
        }
        // 写入数据到数据库
        $result = $this->db->insertAll($dataList, $options, $replace);
         if (false !== $result) {
             $insertId   =   $this->getLastInsID();
             if ($insertId) {
                 return $insertId;
             }
         }
         return $result;
     }

    /**
     * 通过Select方式添加记录
     * @access public
     * @param string $fields 要插入的数据表字段名
     * @param string $table 要插入的数据表名
     * @param array $options 表达式
     * @return boolean
     */
    public function selectAdd($fields='', $table='', $options=array())
    {
        // 分析表达式
        $options =  $this->_parseOptions($options);
        // 写入数据到数据库
        if (false === $result = $this->db->selectInsert($fields?$fields:$options['field'], $table?$table:$this->getTableName(), $options)) {
            // 数据库插入操作失败
            $this->error =  Result::getErrCode(Errno::ERR_DB_ADD_FAILED);
            return false;
        } else {
            // 插入成功
            return $result;
        }
    }

    /**
     * 更新数据
     * @access public
     * @param mixed $data 数据
     * @param array $options 表达式
     * @return boolean
     */
    public function save($data='', $options=array())
    {
        if (empty($data)) {
            // 没有传递数据，获取当前数据对象的值
            if (!empty($this->data)) {
                $data           =   $this->data;
                // 重置数据
                $this->data     =   array();
            } else {
                $this->error    =    Result::getErrCode(Errno::ERR_DB_DATA_INVILID);
                return false;
            }
        }
        // 数据处理
        $data       =   $this->_facade($data);
        // 分析表达式
        $options    =   $this->_parseOptions($options);
        $pk         =   $this->getPk();
        if (!isset($options['where'])) {
            // 如果存在主键数据 则自动作为更新条件
            if (isset($data[$pk])) {
                $where[$pk]         =   $data[$pk];
                $options['where']   =   $where;
                unset($data[$pk]);
            } else {
                // 如果没有任何更新条件则不执行
                $this->error        =   Result::getErrCode(Errno::ERR_DB_CONDITION_INVILID);
                return false;
            }
        }
        if (is_array($options['where']) && isset($options['where'][$pk])) {
            $pkValue    =   $options['where'][$pk];
        }
        if (false === $this->_before_update($data, $options)) {
            return false;
        }
        $result     =   $this->db->update($data, $options);
        if (false !== $result) {
            if (isset($pkValue)) {
                $data[$pk]   =  $pkValue;
            }
            $this->_after_update($data, $options);
        }
        return $result;
    }
    // 更新数据前的回调方法
    protected function _before_update(&$data, $options)
    {
    }
    // 更新成功后的回调方法
    protected function _after_update($data, $options)
    {
    }

    /**
     * 删除数据
     * @access public
     * @param mixed $options 表达式
     * @return mixed
     */
    public function delete($options=array())
    {
        if (empty($options) && empty($this->options['where'])) {
            // 如果删除条件为空 则删除当前数据对象所对应的记录
            if (!empty($this->data) && isset($this->data[$this->getPk()])) {
                return $this->delete($this->data[$this->getPk()]);
            } else {
                return false;
            }
        }
        $pk   =  $this->getPk();
        if (is_numeric($options)  || is_string($options)) {
            // 根据主键删除记录
            if (strpos($options, ',')) {
                $where[$pk]     =  array('IN', $options);
            } else {
                $where[$pk]     =  $options;
            }
            $options            =  array();
            $options['where']   =  $where;
        }
        // 分析表达式
        $options =  $this->_parseOptions($options);
        if (is_array($options['where']) && isset($options['where'][$pk])) {
            $pkValue            =  $options['where'][$pk];
        }
        if (false === $this->_before_delete($options)) {
            return false;
        }
        $result  =    $this->db->delete($options);
        if (false !== $result) {
            $data = array();
            if (isset($pkValue)) {
                $data[$pk]   =  $pkValue;
            }
            $this->_after_delete($data, $options);
        }
        // 返回删除记录个数
        return $result;
    }
    // 删除数据前的回调方法
    protected function _before_delete($options)
    {
    }
    // 删除成功后的回调方法
    protected function _after_delete($data, $options)
    {
    }

    /**
     * 查询数据集
     * @access public
     * @param array $options 表达式参数
     * @return mixed
     */
    public function select($options=array())
    {
        if (is_string($options) || is_numeric($options)) {
            // 根据主键查询
            $pk   =  $this->getPk();
            if (strpos($options, ',')) {
                $where[$pk]     =  array('IN',$options);
            } else {
                $where[$pk]     =  $options;
            }
            $options            =  array();
            $options['where']   =  $where;
        } elseif (false === $options) { // 用于子查询 不查询只返回SQL
            $options            =  array();
            // 分析表达式
            $options            =  $this->_parseOptions($options);
            return  '( '.$this->db->buildSelectSql($options).' )';
        }
        // 分析表达式
        $options    =  $this->_parseOptions($options);
        // 判断查询缓存
        $resultSet  = $this->db->select($options);
        if (false === $resultSet) {
            return false;
        }
        if (empty($resultSet)) { // 查询结果为空
            return null;
        }
        $resultSet  =   array_map(array($this,'_read_data'), $resultSet);
        $this->_after_select($resultSet, $options);
        
        return $resultSet;
    }
    // 查询成功后的回调方法
    protected function _after_select(&$resultSet, $options)
    {
    }

    /**
     * 生成查询SQL 可用于子查询
     * @access public
     * @param array $options 表达式参数
     * @return string
     */
    public function buildSql($options=array())
    {
        // 分析表达式
        $options =  $this->_parseOptions($options);
        return  '( '.$this->db->buildSelectSql($options).' )';
    }

    /**
     * 分析表达式
     * @access protected
     * @param array $options 表达式参数
     * @return array
     */
    protected function _parseOptions($options=array())
    {
        if (is_array($options)) {
            $options =  array_merge($this->options, $options);
        }

        if (!isset($options['table'])) {
            // 自动获取表名
            $options['table']   =   $this->getTableName();
            $fields             =   $this->fields;
        } else {
            // 指定数据表 则重新获取字段列表 但不支持类型检测
            $fields             =   $this->getDbFields();
        }

        // 查询过后清空sql表达式组装 避免影响下次查询ccc
//        $this->options  =   array();
        // 数据表别名
        if (!empty($options['alias'])) {
            $options['table']  .=   ' '.$options['alias'];
        }
        // 记录操作的模型名称
        $options['model']       =   $this->name;

        // 字段类型验证
        if (isset($options['where']) && is_array($options['where']) && !empty($fields) && !isset($options['join'])) {
            // 对数组查询条件进行字段类型检查
            foreach ($options['where'] as $key=>$val) {
                $key            =   trim($key);
                if (in_array($key, $fields, true)) {
                    if (is_scalar($val)) {
                        $this->_parseType($options['where'], $key);
                    }
                } elseif (!is_numeric($key) && '_' != substr($key, 0, 1) && false === strpos($key, '.') && false === strpos($key, '(') && false === strpos($key, '|') && false === strpos($key, '&')) {
                    unset($options['where'][$key]);
                }
            }
        }

        // 表达式过滤
        $this->_options_filter($options);
        // 查询过后清空sql表达式组装 避免影响下次查询
        $this->options  =   array();
        return $options;
    }
    // 表达式过滤回调方法
    protected function _options_filter(&$options)
    {
    }

    /**
     * 数据类型检测
     * @access protected
     * @param mixed $data 数据
     * @param string $key 字段名
     * @return void
     */
    protected function _parseType(&$data, $key)
    {
        if (empty($this->options['bind'][':'.$key]) && isset($this->fields['_type'][$key])) {
            $fieldType = strtolower($this->fields['_type'][$key]);
            if (false !== strpos($fieldType, 'enum')) {
                // 支持ENUM类型优先检测
            } elseif (false === strpos($fieldType, 'bigint') && false !== strpos($fieldType, 'int')) {
                $data[$key]   =  intval($data[$key]);
            } elseif (false !== strpos($fieldType, 'float') || false !== strpos($fieldType, 'double')) {
                $data[$key]   =  floatval($data[$key]);
            } elseif (false !== strpos($fieldType, 'bool')) {
                $data[$key]   =  (bool)$data[$key];
            }
        }
    }

    /**
     * 数据读取后的处理
     * @access protected
     * @param array $data 当前数据
     * @return array
     */
    protected function _read_data($data)
    {
        // 检查字段映射
        if (!empty($this->_map) && Model_Config::getDbGlobalConf('read_data_map')) {
            foreach ($this->_map as $key=>$val) {
                if (isset($data[$val])) {
                    $data[$key] =   $data[$val];
                    unset($data[$val]);
                }
            }
        }
        return $data;
    }

    /**
     * 查询数据-by one
     * @access public
     * @param mixed $options 表达式参数
     * @return mixed
     */
    public function find($options=array())
    {
        if (is_numeric($options) || is_string($options)) {
            $where[$this->getPk()]  =   $options;
            $options                =   array();
            $options['where']       =   $where;
        }
        // 总是查找一条记录
        $options['limit']   =   1;
        // 分析表达式
        $options            =   $this->_parseOptions($options);
        //
        $resultSet          =   $this->db->select($options);
        if (false === $resultSet) {
            return false;
        }
        if (empty($resultSet)) {// 查询结果为空
            return null;
        }
        // 读取数据后的处理
        $data   =   $this->_read_data($resultSet[0]);
        $this->_after_find($data, $options);
        if (!empty($this->options['result'])) {
            return $this->returnResult($data, $this->options['result']);
        }
        $this->data     =   $data;
       
        return $this->data;
    }
    // 查询成功的回调方法
    protected function _after_find(&$result, $options)
    {
    }

     protected function returnResult($data, $type='')
     {
         if ($type) {
             if (is_callable($type)) {
                 return call_user_func($type, $data);
             }
             switch (strtolower($type)) {
                case 'json':
                    return json_encode($data);
                case 'xml':
                    return $this->_xml_encode($data);
            }
         }
         return $data;
     }
    
    /**
     * XML编码
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $attr 根节点属性
     * @param string $id   数字索引子节点key转换的属性名
     * @param string $encoding 数据编码
     * @return string
     */
    protected function _xml_encode($data, $root='think', $item='item', $attr='', $id='id', $encoding='utf-8')
    {
        if (is_array($attr)) {
            $_attr = array();
            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $_attr);
        }
        $attr   = trim($attr);
        $attr   = empty($attr) ? '' : " {$attr}";
        $xml    = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
        $xml   .= "<{$root}{$attr}>";
        $xml   .= data_to_xml($data, $item, $id);
        $xml   .= "</{$root}>";
        return $xml;
    }

    /**
     * 处理字段映射
     * @access public
     * @param array $data 当前数据
     * @param integer $type 类型 0 写入 1 读取
     * @return array
     */
    public function parseFieldsMap($data, $type=1)
    {
        // 检查字段映射
        if (!empty($this->_map)) {
            foreach ($this->_map as $key=>$val) {
                if ($type==1) { // 读取
                    if (isset($data[$val])) {
                        $data[$key] =   $data[$val];
                        unset($data[$val]);
                    }
                } else {
                    if (isset($data[$key])) {
                        $data[$val] =   $data[$key];
                        unset($data[$key]);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 设置记录的某个字段值
     * 支持使用数据库字段和方法
     * @access public
     * @param string|array $field  字段名
     * @param string $value  字段值
     * @return boolean
     */
    public function setField($field, $value='')
    {
        if (is_array($field)) {
            $data           =   $field;
        } else {
            $data[$field]   =   $value;
        }
        return $this->save($data);
    }

    /**
     * 字段值增长
     * @access public
     * @param string $field  字段名
     * @param integer $step  增长值
     * @return boolean
     */
    public function setInc($field, $step=1)
    {
        return $this->setField($field, array('exp',$field.'+'.$step));
    }

    /**
     * 字段值减少
     * @access public
     * @param string $field  字段名
     * @param integer $step  减少值
     * @return boolean
     */
    public function setDec($field, $step=1)
    {
        return $this->setField($field, array('exp',$field.'-'.$step));
    }

    /**
     * 获取一条记录的某个字段值
     * @access public
     * @param string $field  字段名
     * @param string $spea  字段数据间隔符号 NULL返回数组
     * @return mixed
     */
    public function getField($field, $sepa=null)
    {
        $options['field']       =   $field;
        $options                =   $this->_parseOptions($options);
        // 判断查询缓存
      
        $field                  =   trim($field);
        if (strpos($field, ',')) { // 多字段
            if (!isset($options['limit'])) {
                $options['limit']   =   is_numeric($sepa)?$sepa:'';
            }
            $resultSet          =   $this->db->select($options);
            if (!empty($resultSet)) {
                $_field         =   explode(',', $field);
                $field          =   array_keys($resultSet[0]);
                $key            =   array_shift($field);
                $key2           =   array_shift($field);
                $cols           =   array();
                $count          =   count($_field);
                foreach ($resultSet as $result) {
                    $name   =  $result[$key];
                    if (2==$count) {
                        $cols[$name]   =  $result[$key2];
                    } else {
                        $cols[$name]   =  is_string($sepa)?implode($sepa, $result):$result;
                    }
                }
                return $cols;
            }
        } else {   // 查找一条记录
            // 返回数据个数
            if (true !== $sepa) {// 当sepa指定为true的时候 返回所有数据
                $options['limit']   =   is_numeric($sepa)?$sepa:1;
            }
            $result = $this->db->select($options);
            if (!empty($result)) {
                if (true !== $sepa && 1==$options['limit']) {
                    $data   =   reset($result[0]);
                    return $data;
                }
                foreach ($result as $val) {
                    $array[]    =   $val[$field];
                }
                return $array;
            }
        }
        return null;
    }


    /**
     * 使用正则验证数据
     * @access public
     * @param string $value  要验证的数据
     * @param string $rule 验证规则
     * @return boolean
     */
    public function regex($value, $rule)
    {
        $validate = array(
            'require'   =>  '/\S+/',
            'email'     =>  '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            'url'       =>  '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/',
            'currency'  =>  '/^\d+(\.\d+)?$/',
            'number'    =>  '/^\d+$/',
            'zip'       =>  '/^\d{6}$/',
            'integer'   =>  '/^[-\+]?\d+$/',
            'double'    =>  '/^[-\+]?\d+(\.\d+)?$/',
            'english'   =>  '/^[A-Za-z]+$/',
        );
        // 检查是否有内置的正则表达式
        if (isset($validate[strtolower($rule)])) {
            $rule       =   $validate[strtolower($rule)];
        }
        return preg_match($rule, $value)===1;
    }

   

   

    /**
     * 验证表单字段 支持批量验证
     * 如果批量验证返回错误的数组信息
     * @access protected
     * @param array $data 创建数据
     * @param array $val 验证因子
     * @return boolean
     */
    protected function _validationField($data, $val)
    {
        if ($this->patchValidate && isset($this->error[$val[0]])) {
            return ;
        } //当前字段已经有规则验证没有通过
        if (false === $this->_validationFieldItem($data, $val)) {
            if ($this->patchValidate) {
                $this->error[$val[0]]   =   $val[2];
            } else {
                $this->error            =   $val[2];
                return false;
            }
        }
        return ;
    }

    /**
     * 根据验证因子验证字段
     * @access protected
     * @param array $data 创建数据
     * @param array $val 验证因子
     * @return boolean
     */
    protected function _validationFieldItem($data, $val)
    {
        switch (strtolower(trim($val[4]))) {
            case 'function':// 使用函数进行验证
            case 'callback':// 调用方法进行验证
                $args = isset($val[6])?(array)$val[6]:array();
                if (is_string($val[0]) && strpos($val[0], ',')) {
                    $val[0] = explode(',', $val[0]);
                }
                if (is_array($val[0])) {
                    // 支持多个字段验证
                    foreach ($val[0] as $field) {
                        $_data[$field] = $data[$field];
                    }
                    array_unshift($args, $_data);
                } else {
                    array_unshift($args, $data[$val[0]]);
                }
                if ('function'==$val[4]) {
                    return call_user_func_array($val[1], $args);
                } else {
                    return call_user_func_array(array(&$this, $val[1]), $args);
                }
            case 'confirm': // 验证两个字段是否相同
                return $data[$val[0]] == $data[$val[1]];
            case 'unique': // 验证某个值是否唯一
                if (is_string($val[0]) && strpos($val[0], ',')) {
                    $val[0]  =  explode(',', $val[0]);
                }
                $map = array();
                if (is_array($val[0])) {
                    // 支持多个字段验证
                    foreach ($val[0] as $field) {
                        $map[$field]   =  $data[$field];
                    }
                } else {
                    $map[$val[0]] = $data[$val[0]];
                }
                if (!empty($data[$this->getPk()])) { // 完善编辑的时候验证唯一
                    $map[$this->getPk()] = array('neq',$data[$this->getPk()]);
                }
                if ($this->where($map)->find()) {
                    return false;
                }
                return true;
            default:  // 检查附加规则
                return $this->check($data[$val[0]], $val[1], $val[4]);
        }
    }

    /**
     * 验证数据 支持 in between equal length regex expire ip_allow ip_deny
     * @access public
     * @param string $value 验证数据
     * @param mixed $rule 验证表达式
     * @param string $type 验证方式 默认为正则验证
     * @return boolean
     */
    public function check($value, $rule, $type='regex')
    {
        $type   =   strtolower(trim($type));
        switch ($type) {
            case 'in': // 验证是否在某个指定范围之内 逗号分隔字符串或者数组
            case 'notin':
                $range   = is_array($rule)? $rule : explode(',', $rule);
                return $type == 'in' ? in_array($value, $range) : !in_array($value, $range);
            case 'between': // 验证是否在某个范围
            case 'notbetween': // 验证是否不在某个范围
                if (is_array($rule)) {
                    $min    =    $rule[0];
                    $max    =    $rule[1];
                } else {
                    list($min, $max)   =  explode(',', $rule);
                }
                return $type == 'between' ? $value>=$min && $value<=$max : $value<$min || $value>$max;
            case 'equal': // 验证是否等于某个值
            case 'notequal': // 验证是否等于某个值
                return $type == 'equal' ? $value == $rule : $value != $rule;
            case 'length': // 验证长度
                $length  =  mb_strlen($value, 'utf-8'); // 当前数据长度
                if (strpos($rule, ',')) { // 长度区间
                    list($min, $max)   =  explode(',', $rule);
                    return $length >= $min && $length <= $max;
                } else {// 指定长度
                    return $length == $rule;
                }
            case 'expire':
                list($start, $end)   =  explode(',', $rule);
                if (!is_numeric($start)) {
                    $start   =  strtotime($start);
                }
                if (!is_numeric($end)) {
                    $end   =  strtotime($end);
                }
                return time() >= $start && time() <= $end;
            case 'ip_allow': // IP 操作许可验证
                return in_array(Common_Tools::getip(), explode(',', $rule));
            case 'ip_deny': // IP 操作禁止验证
                return !in_array(Common_Tools::getip(), explode(',', $rule));
            case 'regex':
            default:    // 默认使用正则验证 可以使用验证类中定义的验证名称
                // 检查附加规则
                return $this->regex($value, $rule);
        }
    }

    /**
     * SQL查询
     * @access public
     * @param string $sql  SQL指令
     * @param mixed $parse  是否需要解析SQL
     * @return mixed
     */
    public function query($sql, $master)
    {
        /* if(!is_bool($parse) && !is_array($parse)) {
            $parse = func_get_args();
            array_shift($parse);
        }*/
      //$sql  =   $this->parseSql($sql,true);
      return $this->db->query($sql, $master);
    }

    /**
     * 执行SQL语句
     * @access public
     * @param string $sql  SQL指令
     * @param mixed $parse  是否需要解析SQL
     * @return false | integer
     */
    public function execute($sql, $parse=false)
    {
        if (!is_bool($parse) && !is_array($parse)) {
            $parse = func_get_args();
            array_shift($parse);
        }
        $sql  =   $this->parseSql($sql, $parse);
       // return $this->db->query($sql);
       return $this->db->execute($sql);
    }

    /**
     * 解析SQL语句
     * @access public
     * @param string $sql  SQL指令
     * @param boolean $parse  是否需要解析SQL
     * @return string
     */
    protected function parseSql($sql, $parse)
    {
        // 分析表达式
        if (true === $parse) {
            $options =  $this->_parseOptions();
            $sql    =   $this->db->parseSql($sql, $options);
        } elseif (is_array($parse)) { // SQL预处理
            $parse  =   array_map(array($this->db,'escapeString'), $parse);
            $sql    =   vsprintf($sql, $parse);
        } else {
            $sql    =   strtr($sql, array('__TABLE__'=>$this->getTableName(),'__PREFIX__'=>  Model_Config::getDbGlobalConf('table_prefix')));
        }
        $this->db->setModel($this->name);
        return $sql;
    }
    // 数据库切换后回调方法
    protected function _after_db()
    {
    }

    /**
     * 得到当前的数据对象名称
     * @access public
     * @return string
     * $todo model的处理方式根据需要进行修改
     */
    public function getModelName()
    {
        if (empty($this->name)) {
            $name = substr(get_class($this), 0, -5);
            if ($pos = strrpos($name, '\\')) {//有命名空间
                $this->name = substr($name, $pos+1);
            } else {
                $this->name = $name;
            }
        }
        return $this->name;
    }

    /**
     * 得到完整的数据表名
     * @access public
     * @return string
     */
    public function getTableName()
    {
        //$this->trueTableName=$this->table_name;
        if (empty($this->trueTableName)) {
            $tableName  = !empty($this->tablePrefix) ? $this->tablePrefix : '';
            if (!empty($this->tableName)) {
                $tableName .= $this->tableName;
            } else {
                $tableName .= $this->_parse_name($this->name);
            }
            $this->trueTableName    =   strtolower($tableName);
        }
        //return (!empty($this->dbName)?$this->dbName.'.':'').$this->trueTableName;
        return $this->trueTableName;
    }

    /**
     * 启动事务
     * @access public
     * @return void
     */
    public function startTrans()
    {
        //   $this->commit();
        $this->db->startTrans();
        return ;
    }

    /**
     * 提交事务
     * @access public
     * @return boolean
     */
    public function commit()
    {
        return $this->db->commit();
    }

    /**
     * 事务回滚
     * @access public
     * @return boolean
     */
    public function rollback()
    {
        return $this->db->rollback();
    }

    /**
     * 返回模型的错误信息
     * @access public
     * @return string
     */
    public function getError()
    {
        return $this->db->getError();
    }

    /**
     * 返回数据库的错误信息
     * @access public
     * @return string
     */
    public function getDbError()
    {
        return $this->db->getError();
    }

    /**
     * 返回最后插入的ID
     * @access public
     * @return string
     */
    public function getLastInsID()
    {
        return $this->db->getLastInsID();
    }

    /**
     * 返回最后执行的sql语句
     * @access public
     * @return string
     */
    public function getLastSql()
    {
        return $this->db->getLastSql($this->name);
    }
    // 鉴于getLastSql比较常用 增加_sql 别名
    public function _sql()
    {
        return $this->getLastSql();
    }

    /**
     * 获取主键名称
     * @access public
     * @return string
     */
    public function getPk()
    {
        return $this->pk;
    }

    /**
     * 获取数据表字段信息
     * @access public
     * @return array
     */
    public function getDbFields()
    {
        if (isset($this->options['table'])) {// 动态指定表名
            $array      =   explode(' ', $this->options['table']);
            $fields     =   $this->db->getFields($array[0]);
            return  $fields?array_keys($fields):false;
        }
        if ($this->fields) {
            $fields     =  $this->fields;
            unset($fields['_type'], $fields['_pk']);
            return $fields;
        }
        return false;
    }

    /**
     * 设置数据对象值
     * @access public
     * @param mixed $data 数据
     * @return Model
     */
    public function data($data='')
    {
        if ('' === $data && !empty($this->data)) {
            return $this->data;
        }
        if (is_object($data)) {
            $data   =   get_object_vars($data);
        } elseif (is_string($data)) {
            parse_str($data, $data);
        } elseif (!is_array($data)) {
            throw new Exception(Result::getErrCode(Errno::ERR_DB_DATA_INVILID), Errno::ERR_DB_DATA_INVILID);
        }
        $this->data = $data;
        return $this;
    }

    /**
     * 指定当前的数据表
     * @access public
     * @param mixed $table
     * @return Model
     */
    public function table($table)
    {
        $prefix =   $this->tablePrefix;
        if (is_array($table)) {
            $this->options['table'] =   $table;
        } elseif (!empty($table)) {
            //将__TABLE_NAME__替换成带前缀的表名
            $table  = preg_replace_callback("/__([A-Z_-]+)__/sU", function ($match) use ($prefix) {
                return $prefix.strtolower($match[1]);
            }, $table);
            $this->options['table'] =   $table;
        }
        return $this;
    }

    /**
     * 查询SQL组装 join
     * @access public
     * @param mixed $join
     * @param string $type JOIN类型
     * @return Model
     */
    public function join($join, $type='INNER')
    {
        $prefix =   $this->tablePrefix;

        if (is_array($join)) {
            foreach ($join as $key=>&$_join) {
                $_join  =   preg_replace_callback("/__([A-Z_-]+)__/sU", function ($match) use ($prefix) {
                    return $prefix.strtolower($match[1]);
                }, $_join);
                $_join  =   false !== stripos($_join, 'JOIN')? $_join : $type.' JOIN ' .$_join;
            }
            $this->options['join']      =   $join;
        } elseif (!empty($join)) {
            //将__TABLE_NAME__字符串替换成带前缀的表名
            $join  = preg_replace_callback("/__([A-Z_-]+)__/sU", function ($match) use ($prefix) {
                return $prefix.strtolower($match[1]);
            }, $join);
            $this->options['join'][]    =   false !== stripos($join, 'JOIN')? $join : $type.' JOIN '.$join;
        }
        return $this;
    }

    /**
     * 查询SQL组装 union
     * @access public
     * @param mixed $union
     * @param boolean $all
     * @return Model
     */
    public function union($union, $all=false)
    {
        if (empty($union)) {
            return $this;
        }
        if ($all) {
            $this->options['union']['_all']  =   true;
        }
        if (is_object($union)) {
            $union   =  get_object_vars($union);
        }
        // 转换union表达式
        if (is_string($union)) {
            $prefix =   $this->tablePrefix;
            //将__TABLE_NAME__字符串替换成带前缀的表名
            $options  = preg_replace_callback("/__([A-Z_-]+)__/sU", function ($match) use ($prefix) {
                return $prefix.strtolower($match[1]);
            }, $union);
        } elseif (is_array($union)) {
            if (isset($union[0])) {
                $this->options['union']  =  array_merge($this->options['union'], $union);
                return $this;
            } else {
                $options =  $union;
            }
        } else {
            throw new Exception('_DATA_TYPE_INVALID_');
        }
        $this->options['union'][]  =   $options;
        return $this;
    }

    /**
     * 查询缓存
     * @access public
     * @param mixed $key
     * @param integer $expire
     * @param string $type
     * @return Model
     */
    public function cache($key=true, $expire=null, $type='')
    {
        if (false !== $key) {
            $this->options['cache']  =  array('key'=>$key,'expire'=>$expire,'type'=>$type);
        }
        return $this;
    }

    /**
     * 指定查询字段 支持字段排除
     * @access public
     * @param mixed $field
     * @param boolean $except 是否排除
     * @return Model
     */
    public function field($field, $except=false)
    {
        /*  if(true === $field) {// 获取全部字段
            $fields     =  $this->getDbFields();
            $field      =  $fields?$fields:'*';
        }elseif($except) {// 字段排除
            if(is_string($field)) {
                $field  =  explode(',',$field);
            }
            $fields     =  $this->getDbFields();
            $field      =  $fields?array_diff($fields,$field):$field;
        }*/
        $this->options['field']   =   $field;
        return $this;
    }
    
    /**
     * 指定查询字段 支持字段排除
     * @access public
     * @param mixed $field
     * @param boolean $except 是否排除
     * @return Model
     */
    public function master($field, $except=false)
    {
        /*  if(true === $field) {// 获取全部字段
    	 $fields     =  $this->getDbFields();
    	$field      =  $fields?$fields:'*';
    	}elseif($except) {// 字段排除
    	if(is_string($field)) {
    	$field  =  explode(',',$field);
    	}
    	$fields     =  $this->getDbFields();
    	$field      =  $fields?array_diff($fields,$field):$field;
    	}*/
        $this->options['field']   =   $field;
        return $this;
    }

    /**
     * 调用命名范围
     * @access public
     * @param mixed $scope 命名范围名称 支持多个 和直接定义
     * @param array $args 参数
     * @return Model
     */
    public function scope($scope='', $args=null)
    {
        if ('' === $scope) {
            if (isset($this->_scope['default'])) {
                // 默认的命名范围
                $options    =   $this->_scope['default'];
            } else {
                return $this;
            }
        } elseif (is_string($scope)) { // 支持多个命名范围调用 用逗号分割
            $scopes         =   explode(',', $scope);
            $options        =   array();
            foreach ($scopes as $name) {
                if (!isset($this->_scope[$name])) {
                    continue;
                }
                $options    =   array_merge($options, $this->_scope[$name]);
            }
            if (!empty($args) && is_array($args)) {
                $options    =   array_merge($options, $args);
            }
        } elseif (is_array($scope)) { // 直接传入命名范围定义
            $options        =   $scope;
        }
        
        if (is_array($options) && !empty($options)) {
            $this->options  =   array_merge($this->options, array_change_key_case($options));
        }
        return $this;
    }

    /**
     * 指定查询条件 支持安全过滤
     * @access public
     * @param mixed $where 条件表达式
     * @param mixed $parse 预处理参数
     * @return Model
     */
    public function where($where, $parse=null)
    {
        if (!is_null($parse) && is_string($where)) {
            if (!is_array($parse)) {
                $parse = func_get_args();
                array_shift($parse);
            }
            $parse = array_map(array($this->db,'escapeString'), $parse);
            $where =   vsprintf($where, $parse);
        } elseif (is_object($where)) {
            $where  =   get_object_vars($where);
        }
        if (is_string($where) && '' != $where) {
            $map    =   array();
            $map['_string']   =   $where;
            $where  =   $map;
        }
        if (isset($this->options['where'])) {
            $this->options['where'] =   array_merge($this->options['where'], $where);
        } else {
            $this->options['where'] =   $where;
        }
        
        return $this;
    }

    /**
     * 指定查询数量
     * @access public
     * @param mixed $offset 起始位置
     * @param mixed $length 查询数量
     * @return Model
     */
    public function limit($offset, $length=null)
    {
        $this->options['limit'] =   is_null($length)?$offset:$offset.','.$length;
        return $this;
    }

    /**
     * 指定分页
     * @access public
     * @param mixed $page 页数
     * @param mixed $listRows 每页数量
     * @return Model
     */
    public function page($page, $listRows=null)
    {
        $this->options['page'] =   is_null($listRows)?$page:$page.','.$listRows;
        return $this;
    }

    /**
     * 查询注释
     * @access public
     * @param string $comment 注释
     * @return Model
     */
    public function comment($comment)
    {
        $this->options['comment'] =   $comment;
        return $this;
    }

    /**
     * 参数绑定
     * @access public
     * @param string $key  参数名
     * @param mixed $value  绑定的变量及绑定参数
     * @return Model
     */
    public function bind($key, $value=false)
    {
        if (is_array($key)) {
            $this->options['bind'] =    $key;
        } else {
            $num =  func_num_args();
            if ($num>2) {
                $params =   func_get_args();
                array_shift($params);
                $this->options['bind'][$key] =  $params;
            } else {
                $this->options['bind'][$key] =  $value;
            }
        }
        return $this;
    }

    /**
     * 设置模型的属性值
     * @access public
     * @param string $name 名称
     * @param mixed $value 值
     * @return Model
     */
    public function setProperty($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
        return $this;
    }
 }