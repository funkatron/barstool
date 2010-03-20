<?php

require_once 'PHPUnit/Framework.php';

require_once 'Barstool.php';

class BarstoolTest extends PHPUnit_Framework_TestCase {

    protected $fixture;
    
    public $webSourceCount = 0;

	protected function setUp() {
		$this->stool = new Barstool(array('adaptor'=>'sqlite'));
		$this->stool->nuke();
		$fixture_file_path = dirname(__FILE__).DIRECTORY_SEPARATOR."fixtures".DIRECTORY_SEPARATOR."public_timeline.json";
		$this->fixture = json_decode(file_get_contents($fixture_file_path));
        $this->webSourceCount = 0;
	}
	

    public function testGet1() {
        
    }

	public function testExists1() {
		$obj = new stdClass;
		$obj->key = 'testdata';
		$obj->foo = 'bar';
		$this->stool->save($obj);
		$rs = $this->stool->exists('testdata');
		
		$this->assertTrue($rs);
	}


	public function testRemove1() {
		$obj = new stdClass;
		$rs = $this->stool->remove('testdata');
		$this->assertNull($rs);
	}

	
	public function testSave1() {
		$obj = new stdClass;
		$obj->key = 'testdata';
		$obj->foo = 'bar';
		$this->stool->save($obj);
		$rs = $this->stool->get('testdata');
		
		$this->assertEquals($rs->foo, 'bar', 'testdata obj foo != "bar"');
	}
	
	
	
	
	public function testSave2() {
		$arr = array(
			'key'=>'testdata2',
			'foo'=>'barboo'
		);
		$this->stool->save($arr);
		$rs = $this->stool->get('testdata2');
		
		$this->assertEquals($rs->foo, 'barboo', 'Assoc array should have been retrieved as a stdClass object');
	}
	
	public function testAll1() {
	    /*
	       set-up the fixture
	    */
	    foreach ($this->fixture as $thisobj) {
		    $this->stool->save($thisobj);
		}
	    $all = count($this->stool->all());
	    $this->assertEquals($all, 20, 'Twenty entries should exist');
	}
	
	public function testFind1() {
	    /*
	       set-up the fixture
	    */
	    foreach ($this->fixture as $thisobj) {
		    $this->stool->save($thisobj);
		}
        
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            /*
                use a closure if we can
            */
            $that = $this;
            $this->stool->find(function($obj) use ($that) {
                if ($obj->source === 'web') {
                    $that->webSourceCount++;
                }
            });
            $this->assertEquals($this->webSourceCount, 5, "There should be 5 web posts");
        } else {
            $this->markTestSkipped('PHP Version is < 5.3.0');
        }
	}
	
	
	public function testFind2() {
	    /*
	       set-up the fixture
	    */
	    foreach ($this->fixture as $thisobj) {
		    $this->stool->save($thisobj);
		}
        
        $this->stool->find(array($this, 'addWebSourceCount'));
        $this->assertEquals($this->webSourceCount, 2, "There should be 2 TweetDeck posts");
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
	
	public function testEach1() {
	    /*
	       set-up the fixture
	    */
	    foreach ($this->fixture as $thisobj) {
		    $this->stool->save($thisobj);
		}
		
		$this->stool->each(array($this,'userAttrExists'));
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
	
	
	public function testNuke1() {	    
	    /*
	       set-up the fixture
	    */
	    foreach ($this->fixture as $thisobj) {
		    $this->stool->save($thisobj);
		}
	    
	    $none = $this->stool->nuke();
	    $all  = count($this->stool->all());
	    $this->assertEquals($all, 0, 'All entries removed by nuke');
	}
	

	
}
