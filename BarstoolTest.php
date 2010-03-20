<?php

require_once 'PHPUnit/Framework.php';

require_once 'Barstool.php';

class BarstoolTest extends PHPUnit_Framework_TestCase {

    protected $fixture;
    
    public $webSourceCount = 0;


    public static function provider() {
        $stools = array();

        if (extension_loaded('SQLite')) {
            $stool_sqlite = new Barstool(array('adaptor'=>'sqlite'));
            $stool_sqlite->nuke();
            $stools[] = array($stool_sqlite);
        }
        
        if (extension_loaded('pdo_sqlite')) {
            $stool_pdo_sqlite = new Barstool(array('adaptor'=>'pdo', 'pdo_dsn'=>'sqlite:Lawnchair_pdo.sqlite.db'));
            $stool_pdo_sqlite->nuke();
            $stools[] = array($stool_pdo_sqlite);
        }
        
		return $stools;
    }

	protected function setUp() {
		$fixture_file_path = dirname(__FILE__).DIRECTORY_SEPARATOR."fixtures".DIRECTORY_SEPARATOR."public_timeline.json";
		$this->fixture = json_decode(file_get_contents($fixture_file_path));
        $this->webSourceCount = 0;
	}
	

    /**
     * @dataProvider provider
     *
     * @return void
     * @author Ed Finkler
     */
	public function testExists1($stool) {
		$obj = new stdClass;
		$obj->key = 'testdata';
		$obj->foo = 'bar';
		$stool->save($obj);
		$rs = $stool->exists('testdata');
		
		$this->assertTrue($rs);
		$stool->nuke();
	}

    /**
     * @dataProvider provider
     *
     * @return void
     * @author Ed Finkler
     */
	public function testRemove1($stool) {
		$obj = new stdClass;
		$rs = $stool->remove('testdata');
		$this->assertNull($rs);
		$stool->nuke();
	}

	
	
    /**
     * @dataProvider provider
     *
     * @return void
     * @author Ed Finkler
     */
	public function testSave1($stool) {
		$obj = new stdClass;
		$obj->key = 'testdata';
		$obj->foo = 'bar';
		$stool->save($obj);
		$rs = $stool->get('testdata');
		
		$this->assertEquals($rs->foo, 'bar', 'testdata obj foo != "bar"');
		$stool->nuke();
	}
	
	
	
	
    /**
     * @dataProvider provider
     *
     * @return void
     * @author Ed Finkler
     */
	public function testSave2($stool) {
		$arr = array(
			'key'=>'testdata2',
			'foo'=>'barboo'
		);
		$stool->save($arr);
		$rs = $stool->get('testdata2');
		
		$this->assertEquals($rs->foo, 'barboo', 'Assoc array should have been retrieved as a stdClass object');
		$stool->nuke();
	}


	
    /**
     * @dataProvider provider
     *
     * @return void
     * @author Ed Finkler
     */
	public function testAll1($stool) {
	    /*
	       set-up the fixture
	    */
	    foreach ($this->fixture as $thisobj) {
		    $stool->save($thisobj);
		}
	    $all = count($stool->all());
	    $this->assertEquals($all, 20, 'Twenty entries should exist');
	    $stool->nuke();
	}

    /**
     * This function demonstrates usage of an anonymous function as a callback
     * 
     * It will only work under PHP >= 5.3.0. Commented out for now.
     * 
     * @dataProvider provider
     * @return void
     * @author Ed Finkler
     */
    // public function testFind1($stool) {
    //     /*
    //        set-up the fixture
    //     */
    //     foreach ($this->fixture as $thisobj) {
    //         $stool->save($thisobj);
    //     }
    //         
    //     if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
    //         /*
    //             use a closure if we can
    //         */
    //         $that = $this;
    //         $stool->find(function($obj) use ($that) {
    //             if ($obj->source === 'web') {
    //                 $that->webSourceCount++;
    //             }
    //         });
    //         $this->assertEquals($this->webSourceCount, 5, "There should be 5 web posts");
    //     } else {
    //         $this->markTestSkipped('PHP Version is < 5.3.0');
    //     }
    // }
	
	
    /**
     * @dataProvider provider
     *
     * @return void
     * @author Ed Finkler
     */
	public function testFind2($stool) {
	    /*
	       set-up the fixture
	    */
	    foreach ($this->fixture as $thisobj) {
		    $stool->save($thisobj);
		}
        
        $stool->find(array($this, 'addWebSourceCount'));
        $this->assertEquals($this->webSourceCount, 2, "There should be 2 TweetDeck posts");
        $stool->nuke();
	}
	
	/**
	 * callback used by testFind2
	 *
	 * @param stdClass $obj 
	 * @return void
	 * @author Ed Finkler
	 */
	public function addWebSourceCount($obj) {
        if (strpos($obj->source, 'TweetDeck') !== FALSE) {
            $this->webSourceCount++;
        }	    
	}
	
    /**
     * @dataProvider provider
     *
     * @return void
     * @author Ed Finkler
     */
	public function testEach1($stool) {
	    /*
	       set-up the fixture
	    */
	    foreach ($this->fixture as $thisobj) {
		    $stool->save($thisobj);
		}
		
		$stool->each(array($this,'userAttrExists'));
        $stool->nuke();
	}
	
	/**
	 * This is a callback used by testEach1
	 *
	 * @param stdClass $obj 
	 * @return void
	 * @author Ed Finkler
	 */
	public function userAttrExists($obj) {
	    $this->assertObjectHasAttribute('user', $obj);
	}
	
	
    /**
     * @dataProvider provider
     *
     * @return void
     * @author Ed Finkler
     */
	public function testNuke1($stool) {	    
	    /*
	       set-up the fixture
	    */
	    foreach ($this->fixture as $thisobj) {
		    $stool->save($thisobj);
		}
	    
	    $none = $stool->nuke();
	    $all  = count($stool->all());
	    $this->assertEquals($all, 0, 'All entries removed by nuke');
	}
	

	
}
