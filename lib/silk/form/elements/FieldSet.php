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

namespace silk\form\elements;

use \silk\core\Object;
use \silk\form\Form;

/**
 * Global object that holds references to various data structures
 * needed by classes/functions.
 */
class FieldSet extends Form 
{
	public $form = null;

	public $id = '';

	public $class = '';

	public $dir = '';

	public $lang = '';

	public $style = '';

	public $title = '';

	public $legend = '';

	protected $fields = array();

	/**
	 * Constructor
	 */
	public function __construct($form, $name, $params = array())
	{
		parent::__construct($name, $params);
		$this->form = $form;
	}

	public function addFieldSet($name, array $params = array())
	{
		//No nested fieldsets -- just return ourselves
		return $this;
	}

	public function render()
	{
		$result = $this->renderStart();

		foreach ($this->fields as $name => $one_field)
		{
			$result .= $one_field->render();
		}

		$result .= $this->renderEnd();

		return $result;
	}

	public function renderStart()
	{
		$params = $this->compactVariables(array('id', 'dir', 'class', 'lang', 'style', 'title'));

		$result = $this->createStartTag('fieldset', $params);
		if ($this->legend != '')
			$result .= '<legend>' . $this->legend . '</legend>';

		return $result;
	}

	public function renderEnd()
	{
		return $this->createEndTag('fieldset');
	}
}

# vim:ts=4 sw=4 noet
