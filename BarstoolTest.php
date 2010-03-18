<?php

require_once 'PHPUnit/Framework.php';

require_once 'Barstool.php';

class BarstoolTest extends PHPUnit_Framework_TestCase {

	protected function setUp() {
		$this->stool = new Barstool(array('adaptor'=>'sqlite'));
	}
	
	public function testSave() {
		$obj = new stdClass;
		$obj->key = 'testdata';
		$obj->foo = 'bar';
		$this->stool->save($obj);
		$rs = $this->stool->get('testdata');
		
		$this->assertEquals($rs->foo, 'bar');
	}
	
	public function testSave2() {
		$arr = array(
			'key'=>'testdata2',
			'foo'=>'barboo'
		);
		$this->stool->save($arr);
		$rs = $this->stool->get('testdata2');
		
		var_dump($rs);
		
		$this->assertEquals($rs->foo, 'barboo');
	}
}
