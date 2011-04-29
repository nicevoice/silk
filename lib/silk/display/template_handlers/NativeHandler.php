<?php // -*- mode:php; tab-width:4; indent-tabs-mode:t; c-basic-offset:4; -*-
// The MIT License
//
// Copyright (c) 2008-2011 Ted Kulp
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

namespace silk\display\template_handlers;

use \silk\core\Object;

class NativeHandler extends Object implements TemplateHandlerInterface
{
	protected $controller = null;
	protected $helper = null;
	protected $variables = array();

	public function setController(&$controller)
	{
		$this->controller = $controller;
	}

	public function setHelper(&$helper)
	{
		$this->helper = $helper;
	}

	public function setVariables($variables)
	{
		$this->variables = $variables;
	}

	public function processTemplateFromFile($filename)
	{
		//Start the buffering
		@ob_start();

		{
			//If a helper exists, pull it into scope
			if ($this->helper != null)
				$helper = $this->helper;

			//Pull the variables into the current scope
			extract($this->variables);

			include_once($filename);
		}

		$contents = @ob_get_contents();
		@ob_end_clean();

		return $contents;
	}
}

# vim:ts=4 sw=4 noet
