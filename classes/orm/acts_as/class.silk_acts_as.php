<?php // -*- mode:php; tab-width:4; indent-tabs-mode:t; c-basic-offset:4; -*-
// The MIT License
// 
// Copyright (c) 2008 Ted Kulp
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

/**
 * Base class for "acts as" ORM model extensions
 *
 * @author Ted Kulp
 * @since 1.0
 **/
class SilkActsAs extends SilkObject
{
	/**
	 * Create a new acts_as.
	 *
	 * @author Ted Kulp
	 **/
	public function __construct()
	{
		parent::__construct();
	}
	
	public function setup(&$obj)
	{
		
	}
	
	public function before_load($type, $fields)
	{

	}
	
	public function after_load(&$obj)
	{

	}
	
	public function before_validation(&$obj)
	{
		
	}
	
	public function before_save(&$obj)
	{
		
	}
	
	public function after_save(&$obj, &$result)
	{
		
	}
	
	public function before_delete(&$obj)
	{
		
	}
	
	public function after_delete(&$obj)
	{
		
	}

}

# vim:ts=4 sw=4 noet
?>