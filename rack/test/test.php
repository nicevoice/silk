<?php

require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'rack.php');

use Rack\Rack;

class RackTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
	}

	public function tearDown()
	{
		Rack::clear();
	}

	public function testApp()
	{
		Rack::add('MockApp', MockApp);
		list($status, $headers, $body) = Rack::run(array(), false);
		$this->assertEquals('test output', $body[0]);
	}

	public function testAddMiddleware()
	{
		Rack::add('MockMiddleware', MockMiddleware);
		Rack::add('MockApp', MockApp);
		list($status, $headers, $body) = Rack::run(array(), false);
		$this->assertEquals('TEST OUTPUT', $body[0]);
	}

	public function testInsertBeforeMiddleware()
	{
		Rack::add('MockApp', MockApp);
		Rack::insert_before('MockApp', 'MockMiddleware', MockMiddleware);
		list($status, $headers, $body) = Rack::run(array(), false);
		$this->assertEquals('TEST OUTPUT', $body[0]);
	}

	public function testInsertAfterMiddleware()
	{
		Rack::add('MockMiddleware', MockMiddleware);
		Rack::insert_after('MockMiddleware', 'MockApp', MockApp);
		list($status, $headers, $body) = Rack::run(array(), false);
		$this->assertEquals('TEST OUTPUT', $body[0]);
	}

	public function testReplaceMiddleware()
	{
		Rack::add('MockMiddleware', null);
		Rack::add('MockApp', null);
		Rack::replace('MockMiddleware', MockMiddleware);
		Rack::replace('MockApp', MockApp);
		list($status, $headers, $body) = Rack::run(array(), false);
		$this->assertEquals('TEST OUTPUT', $body[0]);
	}

	public function test404Headers()
	{
		Rack::add('MockApp404', MockApp404);
		list($status, $headers, $body) = Rack::run(array(), false);
		$this->assertEquals(404, $status);
		$this->assertEquals('HTTP/1.1 404 Not Found', array_shift(array_keys($headers)));
		$this->assertEquals('404 Not Found', $headers['Status']);
		$this->assertEquals('text/html', $headers['Content-Type']);
	}
}

class MockApp
{
	public function __construct(&$app)
	{
		$this->app = $app;
	}

	public function call(&$env)
	{
		return array(200, array("Content-Type" => "text/html"), array("test output"));
	}
}

class MockMiddleware
{
	public function __construct(&$app)
	{
		$this->app = $app;
	}

	public function call(&$env)
	{
		list($status, $headers, $body) = $this->app->call($env);
		return array($status, $headers, array_map('strtoupper', $body));
	}
}

class MockApp404
{
	public function __construct(&$app)
	{
		$this->app = $app;
	}

	public function call(&$env)
	{
		return array(404, array("Content-Type" => "text/html"), "Not Found");
	}
}
