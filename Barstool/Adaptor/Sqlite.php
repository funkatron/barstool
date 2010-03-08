<?php

/**
* This adaptor is for the sqlite(2) backend that ships with PHP
* http://php.net/sqlite
*/
class Barstool_Adaptor_Sqlite extends Barstool_Adaptor
{
    protected $db;
    
    protected $dbfile;
    
    protected $sqlite_directory;
    
    function __construct($options) {
        $this->init($options);
    }
    
    /**
     * initialize the store
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
            
            if (isset($options['name']) && $options['name']) {
                $this->name = $options['name'];
            }
            
            if (isset($options['table']) && $options['table']) {
                $this->table = $options['table'];
            }
            
            if (isset($options['sqlite_directory']) && $options['sqlite_directory']) {
                $this->sqlite_directory = $options['sqlite_directory'];
            } else {
                $this->sqlite_directory = getcwd(); // default, set here b/c we can't in property def
            }
            
            $this->dbfile = $this->sqlite_directory . DIRECTORY_SEPARATOR . $this->name . ".sqlite.db";
            
        } else {
            
        }
        
        if ($this->db = new SQLiteDatabase($this->dbfile, 0666, $sqliteerror)) { 
            if (!$this->db->query("SELECT COUNT(*) FROM " . $this->table)) {
                $sql = 'CREATE TABLE ' . sqlite_escape_string($this->table) . ' (id NVARCHAR(32) UNIQUE PRIMARY KEY, value TEXT, timestamp REAL)';
                $this->db->query($this->db, $sql);
            }
            
        } else {

            throw new Exception($sqliteerror);

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
        if (!$obj->key) {
            $key = $this->uuid();
        } else {
            $key = $obj->key;
            unset($obj->key);
        }
        
        $value = $this->serialize($obj);
        $timestamp = $this->now();
        
        if (!$this->exists($key)) {
            $sql = 'INSERT INTO '.sqlite_escape_string($this->table) . ' ' .
                    '(id, value,timestamp)'.
                    ' VALUES '.
                    '(\''.sqlite_escape_string($key).'\', \''.sqlite_escape_string($value).'\', '.sqlite_escape_string($timestamp).')';
        } else {
            $sql = 'UPDATE '.sqlite_escape_string($this->table).
                    ' SET '.
                        'value=\''.sqlite_escape_string($value).'\', '.
                        'timestamp='.sqlite_escape_string($timestamp).
                    ' WHERE id=\''.sqlite_escape_string($key).'\'';
        }
        $rs = $this->db->query($sql);
        
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
        $sql = 'SELECT id, value, timestamp FROM '.sqlite_escape_string($this->table) . ' ' .
                ' WHERE id=\''.sqlite_escape_string($key).'\'';
        $rs = $this->db->query($sql);
        if ($rs && $rs->numRows() > 0) {
            $row = $rs->fetch();
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
        $sql = 'SELECT id FROM '.sqlite_escape_string($this->table) . ' ' .
                ' WHERE id=\''.sqlite_escape_string($key).'\'';
        $rs = $this->db->query($sql);
        if ($rs && $rs->numRows() > 0) {
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
        $sql = "SELECT id, value, timestamp FROM '".sqlite_escape_string($this->table)."'";
        $rs = $this->db->unbufferedQuery($sql);
        $rows = array();
        while($row = $rs->fetch(SQLITE_ASSOC)) {
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
            $sql = "DELETE FROM ".sqlite_escape_string($this->table)." ".
                    "WHERE id='".sqlite_escape_string($keyOrObj)."'";
        } else {
            $sql = "DELETE FROM ".sqlite_escape_string($this->table)." ".
                    "WHERE value='".sqlite_escape_string($this->serialize($keyOrObj))."'";
        }
        $this->db->query($sql);
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
        $sql = "DELETE FROM ".sqlite_escape_string($this->table);
        $this->db->query($sql);
        if ($callback) {
            if (is_callable($callback)) {
                call_user_func($callback);
            } else {
                throw new Exception('Callback passed is not callable');
            }
        }
    }
}



?>