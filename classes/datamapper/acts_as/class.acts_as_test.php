<?php // -*- mode:php; tab-width:4; indent-tabs-mode:t; c-basic-offset:4; -*-
// The MIT License
// 
// Copyright (c) 2008-2010 Ted Kulp
// 
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
// 
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
// 
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.

namespace silk\datamapper\acts_as;

use \silk\performance\Profiler;

/**
 * Test class for the acts_as system.  Will be removed at a later date.
 *
 * @author Ted Kulp
 * @since 1.0
 **/
class ActsAsTest extends ActsAs
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function before_load($type, $fields)
	{
		Profiler::get_instance()->mark('before load -- ActsAsTest');
	}
	
	public function after_load(&$obj)
	{
		Profiler::get_instance()->mark('after load -- ActsAsTest');
	}
	
	public function test_me()
	{
		Profiler::get_instance()->mark('Test Me -- ActsAsTest');
	}
}

# vim:ts=4 sw=4 noet
