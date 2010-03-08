<?php
/**
 * require the adaptor class
 *
 * @author Ed Finkler
 */
require_once(
    dirname(__FILE__)
    .DIRECTORY_SEPARATOR.'Barstool'
    .DIRECTORY_SEPARATOR.'Adaptor.php'
);


/**
* Barstool is a simple key/object storage system
* This PHP implementation of Lawnchair is based on
* the JavaScript original by Brian Leroux
* <http://brianleroux.github.com/lawnchair/>
* 
* Data is stored as JSON strings inside the storage backend
*/
class Barstool
{
    
    protected $adaptors = array(
        'sqlite'=>'Barstool_Adaptor_Sqlite',
        'textfile'=>'Barstool_Adaptor_Textfile'
    );
    
    /**
     * Constructor
     */
    public function __construct($options) {
        $this->init($options);
    }
    
    /**
     * 
     */
    public function init($options) {
        
        
        if ($options['adaptor']) {
            $this->loadAdaptor($options);
        } else {
            throw new Exception('missing adaptor from init options');
        }
    }
    
    /**
     * Loads an adaptor
     *
     * @param string $adaptor 
     * @return boolean
     * @author Ed Finkler
     */
    protected function loadAdaptor($options) {
        
        if (array_key_exists($options['adaptor'], $this->adaptors)) {
            require_once(
                dirname(__FILE__)
                .DIRECTORY_SEPARATOR.'Barstool'
                .DIRECTORY_SEPARATOR.'Adaptor'
                .DIRECTORY_SEPARATOR.$options['adaptor'].'.php'
            );
            $this->adaptor = new $this->adaptors[$options['adaptor']]($options);
            return true;
        } else {
            throw new Exception('invalid adaptor type "'.$options['adaptor'].'" from init options');
            return false;
        }
    }
    
    
    /**
     * save an object to the store
     *
     * @param mixed $obj 
     * @param string|function $callback a function to call on success
     * @return boolean
     * @author Ed Finkler
     */
    public function save($obj, $callback=null) {
        return $this->adaptor->save($obj, $callback);
    }

    /**
     * get an object from the store, and invoke a callback on it if exists
     *
     * @param string $key 
     * @param string|function $callback 
     * @return void
     * @author Ed Finkler
     */
    public function get($key, $callback=null) {
        return $this->adaptor->get($key, $callback);
    }
    
    
    /**
     * returns whether a key exists, directly or to the callback
     *
     * @param string $key 
     * @param string|function $callback 
     * @return boolean
     * @author Ed Finkler
     */
    public function exists($key, $callback=null) {
        return $this->adaptor->exists($key, $callback);
    }


    /**
     * returns all rows, directly or to the callback
     *
     * @param string|function $callback 
     * @return array
     * @author Ed Finkler
     */
    public function all($callback=null) {
        return $this->adaptor->all($callback);
    }
    

    /**
     * Removes a json object from the store
     *
     * @param string $key 
     * @param string|function $callback 
     * @return boolean
     * @author Ed Finkler
     */
    public function remove($keyOrObj, $callback=null) {
        return $this->adaptor->remove($keyOrObj, $callback);
    }
    
    
    /**
     * deletes all docs from the store and returns self
     *
     * @param string|function $callback 
     * @return Barstool
     * @author Ed Finkler
     */
    public function nuke($callback=null) {
        $this->adaptor->nuke($callback);
        return $this;
    }
    
	/**
	 * Iterator that accepts two paramters:
	 *
	 * - conditional test for a record
	 * - callback to invoke on matches
	 * 
	 * @param string|function $condition 
	 * @param string|function $callback 
	 * @return array|boolean
	 * @author Ed Finkler
	 */
    public function find($condition, $callback=null) {
        
    }
    
    /**
	 * Classic iterator.
	 * - Passes the record and the index as the second parameter to the callback.
	 * - Accepts a string for a callback func or a function to be invoked for each document in the collection.
     *
     * @param string|function $callback 
     * @return boolean
     * @author Ed Finkler
     */
    public function each($callback) {
        # code...
    }
    
}



?>