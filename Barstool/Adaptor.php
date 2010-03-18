<?php

/**
 * 
 */
class Barstool_Adaptor
{
    /**
     * if this is true, objects in JSON are returned as associative
     * arrays. Otherwise they're returned as objects (default)
     *
     * @var string
     */
    protected $return_assoc_arrays = false;
    
    /**
     * The name of the store, typically the database name
     *
     * @var string
     */
    protected $name  = 'Lawnchair';
    
    /**
     * the table name if we're using an RBDMS store
     *
     * @var string
     */
    protected $table = 'field';
    
    
    function __construct($options) {
        $this->init($options);
    }
    
    protected function init($options) {
        if ($options['return_assoc_arrays']) {
            $this->return_assoc_arrays = true;
        }
    }
    
    
    public function save($obj, $callback=null) {
    }

    public function get($key, $callback=null) {
        
    }
    
    
    public function exists($key, $callback=nul) {
    }


    public function all($callback=null) {
        
    }

    public function remove($keyOrObj, $callback=null) {
    }
    
    
    public function nuke($callback=null) {
    }
    
    
    
    public function find($condition, $callback=null) {
        $cb = array($this, 'findIterator');
        $this->all($cb, $condition, $callback);
    }
    
    
    /**
     * an iteration function that calls the callback on each object that meets the conditions.
     * Intended to be used from Barstool_Adaptor::find
     *
     * @param array $objs 
     * @param callback $condition
     * @param callback $callback 
     * @return void
     * @author Ed Finkler
     */
    protected function findIterator($objs, $condition, $callback) {
        $matches = array();
        foreach ($objs as $obj) {
            array_push($matches, $obj);
            if (call_user_func($condition, $obj)) {
                call_user_func($callback, $obj);
            }
        }
    }
    
    /**
     * Applies a callback to all records in the store
     *
     * @param callback $callback 
     * @return void
     * @author Ed Finkler
     */
    public function each($callback) {
        $cb = array($this, 'eachIterator');
        $this->all($cb, $callback);
    }
    
    /**
     * a simple iteration function that calls the callback on each object.
     * Intended to be used from Barstool_Adaptor::each
     *
     * @param array $objs 
     * @param callback $callback 
     * @return void
     * @author Ed Finkler
     */
    protected function eachIterator($objs, $callback) {
        foreach ($objs as $obj) {
            call_user_func($callback, $obj);
        }
    }
    
    /**
     * encodes the given value (probably an object) as JSON
     *
     * @param mixed $obj 
     * @return string
     * @author Ed Finkler
     */
    protected function serialize($obj) {
        return json_encode($obj);
    }
    
    /**
     * decodes the given JSON string into its PHP value. 
     *
     * @see Barstool_Adaptor::return_assoc_arrays
     * @param string $str
     * @return mixed
     * @author Ed Finkler
     */
    protected function deserialize($str) {
        return json_decode($str, $this->return_assoc_arrays);
    }
    
    /**
     * from comments for http://php.net/manual/en/function.uniqid.php
     *
     * @return string
     * @author Ed Finkler
     */
    protected function uuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    /**
     * returns the current timestamp in milliseconds
     *
     * @return integer
     * @author Ed Finkler
     */
    protected function now() {
        return round(microtime(true)*1000);
    }


    	/**
	 * reports if an array is associative
	 *
	 * @param array $array
	 * @return boolean
	 */
	protected function isAssoc($array) {
		if ( !is_array($_array) || empty($array) ) {
			return -1;
		}
		foreach (array_keys($_array) as $k => $v) {
			if ($k !== $v) {
				return true;
			}
		}
		return false;
	}

	/**
	 * converts an associative array to an object by encoding and decoding json
	 * @param array $assoc an associative array
	 * @return stdClass
	 */
	protected function assocToObject($assoc) {
		return json_decode(json_encode($assoc));
	}
}


?>
