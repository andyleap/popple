<?php

use \Popple\Popple;

class PoppleTest extends PHPUnit_Framework_TestCase
{
	protected $popple;
	
	protected function setUp()
	{
		$this->popple = new Popple;
	}
	
	protected function tearDown()
	{
		unset($this->popple);
	}
	
	public function testAttributeAssign()
	{
		$this->popple['foo'] = 'bar';
		
		$this->assertEquals($this->popple['foo'], 'bar');
	}
	
	public function testPropertyAssign()
	{
		$this->popple->foo = 'bar';
		
		$this->assertEquals($this->popple->foo, 'bar');
	}
	
	public function testShare()
	{
		$this->popple->share('foo', function($p)
		{
			return 'bar';
		});
		
		$this->assertEquals($this->popple->foo, 'bar');
	}
	
	public function testPreMutate()
	{
		$this->popple->share('foo', function($p)
		{
			return 'bar';
		});
		
		$this->popple->mutate('foo', function($p)
		{
			return $p . 'baz';
		});
		
		$this->assertEquals($this->popple->foo, 'barbaz');
	}
	
	public function testPostMutate()
	{
		$this->popple->share('foo', function($p)
		{
			return 'bar';
		});
		
		$this->assertEquals($this->popple->foo, 'bar');
		
		$this->popple->mutate('foo', function($p)
		{
			return $p . 'baz';
		});
		
		$this->assertEquals($this->popple->foo, 'barbaz');
	}
	
	public function testPreShareMutate()
	{
		$this->popple->mutate('foo', function($p)
		{
			return $p . 'baz';
		});
		
		$this->popple->share('foo', function($p)
		{
			return 'bar';
		});
		
		$this->assertEquals($this->popple->foo, 'barbaz');
	}
	
	public function testExtend()
	{
		$this->popple->extend('foo', function()
		{
			return 'bar';
		});
		
		$this->assertEquals($this->popple->foo(), 'bar');
	}
}