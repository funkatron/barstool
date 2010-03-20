<?php

/**
* This adaptor is for the PDO abstraction layer that ships with PHP
* http://php.net/pdo
*/
class Barstool_Adaptor_Pdo extends Barstool_Adaptor
{
    protected $db;
    
    function __construct($options) {
        $this->init($options);
    }
    
    /**
     * initialize the store
     * 
     * Options include
     * - pdo_dsn - this will override all other connection options
     * - pdo_username
     * - pdo_password
     * - pdo_driver_opts (array)
     *
     * @param string $options 
     * @return void
     * @author Ed Finkler
     */
    protected function init($options) {

        if (is_string($options)) {
            $name = $options;
            $options = array('table'=>$name);
        }
        
        
        if (is_array($options)) {
            
            if (!isset($options['pdo_dsn']) || !$options['pdo_dsn']) {
                throw new Exception('PDO DSN required');
            }
            
            if (isset($options['name']) && $options['name']) {
                $this->name = $options['name'];
            }
            
            if (isset($options['table']) && $options['table']) {
                $this->table = $options['table'];
            }
            
            if (!isset($options['pdo_username']) || !$options['pdo_username']) {
                $options['pdo_username'] = null;
            }
            
            if (!isset($options['pdo_password']) || !$options['pdo_password']) {
                $options['pdo_password'] = null;
            }
            
            if (!isset($options['pdo_driver_opts']) || !$options['pdo_driver_opts']) {
                $options['pdo_driver_opts'] = null;
            }

        }
        
        
        if ($this->db = new PDO($options['pdo_dsn'],
                                $options['pdo_username'],
                                $options['pdo_password'],
                                $options['pdo_driver_opts'])) { 
            
            $sql = 'SELECT COUNT(*) FROM '.$this->table;
            $stmt = $this->db->query($sql);
            
            if ($stmt === FALSE) {
                $stmt = $this->exec('CREATE TABLE '.$this->table.' (id NVARCHAR(32) UNIQUE PRIMARY KEY, value TEXT, timestamp REAL)');
            }
            
        } else {

            throw new Exception("Failed to create PDO connection");

        }
                
    }
    
    
    /**
     * save a record to the store
     *
     * @todo support callback
     * @param mixed $obj 
     * @param string|function $callback 
     * @return void
     * @author Ed Finkler
     */
    public function save($obj, $callback=null) {
        
         // if this is an associative array, convert it to a stdClass object first
        if (is_array($obj) && $this->isAssoc($obj)) {
            $obj = $this->assocToObject($obj);
        }
        
        if (!isset($obj->key) || !$obj->key) {
            $key = $this->uuid();
        } else {
            $key = $obj->key;
            unset($obj->key);
        }
        
        $value = $this->serialize($obj);
        $timestamp = $this->now();
        
        if (!$this->exists($key)) {
            $data = array(':id'=>$key, ':value'=>$value, ':timestamp'=>$timestamp);
            $sql = 'INSERT INTO '.$this->table . ' (id, value,timestamp) VALUES (:id,:value,:timestamp)';
        } else {
            $data = array(':id'=>$key, ':value'=>$value, ':timestamp'=>$timestamp);
            $sql = 'UPDATE '.$this->table.' SET value=:value, timestamp=:timestamp WHERE id=:id';
        }
        $stmt = $this->exec($sql, $data);
        
        if ($callback) {
            if (is_callable($callback)) {
                call_user_func($callback, $obj);
            } else {
                throw new Exception('Callback passed is not callable');
            }
        }
        
        return $obj;
    }


    /**
     * retreve a single record
     *
     * @todo support callback
     * @param string $key 
     * @param string|function $callback 
     * @return mixed
     * @author Ed Finkler
     */
    public function get($key, $callback=null) {
        $data = array(':id'=>$key);
        $sql = 'SELECT id, value, timestamp FROM ' . $this->table . ' WHERE id=:id';
        $stmt = $this->exec($sql, $data);
        if ($stmt && ($row = $stmt->fetch()) !== FALSE ) {
            $obj = $this->deserialize($row['value']);
            if ($callback) {
                if (is_callable($callback)) {
                    call_user_func($callback, $obj);
                } else {
                    throw new Exception('Callback passed is not callable');
                }
            }
            
            return $obj;
        } else {
            return false;
        }    
    }
    
    
    /**
     * returns TRUE if a record with this key exists, FALSE otherwise
     *
     * @param string $key 
     * @return boolean
     * @author Ed Finkler
     */
    public function exists($key, $callback=null) {
        $data = array(':id'=>$key);
        $sql = 'SELECT COUNT(id) as count FROM ' . $this->table . ' WHERE id=:id';
        $stmt = $this->exec($sql, $data);
        if ($stmt && $stmt->fetchColumn() > 0) {
            if ($callback) {
                if (is_callable($callback)) {
                    call_user_func($callback, true);
                } else {
                    throw new Exception('Callback passed is not callable');
                }
            }
            return true;
        } else {
            return false;
        }
    }


    /**
     * retrieves an array of all rows
     *
     * the first argument is the callback. Any additional params are passed as additional parameters to the callback function
     * 
     * @todo support callback
     * @param string|function $callback 
     * @return array
     * @author Ed Finkler
     */
    public function all($callback=null) {
        $sql = "SELECT id, value, timestamp FROM ".$this->table;
        $stmt = $this->db->query($sql);
        $rows = array();
        while($row = $stmt->fetch()) {
            $obj = $this->deserialize($row['value']);
            $obj->key = $row['id'];
            $rows[$row['id']] = $obj;            
        }
        if ($callback) {
            if (is_callable($callback)) {
                if (func_num_args() > 1) {
                    $cb_args = array_slice(func_get_args(), 1); // remove the $callback arg
                    array_unshift($cb_args, $rows); // prepend $rows as the first arg
                    call_user_func_array($callback, $cb_args);
                } else {
                    call_user_func($callback, $rows);
                }
                
            } else {
                throw new Exception('Callback passed is not callable');
            }
        }
        return $rows;
    }
    
    /**
     * remove a value from the store by id or by value
     *
     * @todo support callback
     * @param string $keyOrObj 
     * @param string|function $callback 
     * @return void
     * @author Ed Finkler
     */
    public function remove($keyOrObj, $callback=null) {
        if (is_string($keyOrObj)) {
            $sql = "DELETE FROM ".$this->table." WHERE id=:keyorobj";
            $data = array(':keyorobj'=>$keyOrObj);
        } else {
            $sql = "DELETE FROM ".$this->table." WHERE value=:keyorobj";
            $data = array(':keyorobj'=>$this->serialize($keyOrObj));
        }
        $stmt = $this->exec($sql, $data);
        if ($callback) {
            if (is_callable($callback)) {
                call_user_func($callback);
            } else {
                throw new Exception('Callback passed is not callable');
            }
        }
        
    }
    
    /**
     * delete all records
     *
     * @todo support callback
     * @param string|function $callback 
     * @return void
     * @author Ed Finkler
     */
    public function nuke($callback=null) {
        $sql = "DELETE FROM ".$this->table;
        $stmt = $this->db->query($sql);
        if ($callback) {
            if (is_callable($callback)) {
                call_user_func($callback);
            } else {
                throw new Exception('Callback passed is not callable');
            }
        }
    }
    
    /**
     * helper to create, execute and return a PDOStatement object
     *
     * @param string $sql 
     * @param array $data 
     * @return PDOStatement
     * @author Ed Finkler
     */
    protected function exec($sql, $data=null) {
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($data)===FALSE) {
            throw new Exception('PDOStatement execute failed. Info: ' . json_encode($stmt->errorInfo()));
        } else {
            // var_dump('Apparently it worked');
        }
        return $stmt;
    }
}



?>