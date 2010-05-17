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

/**
 * Methods for display of form pieces.
 *
 * @since 1.0
 * @author Ted Kulp
 **/
class SilkForm extends \silk\core\Object
{
	static private $instance = NULL;

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Returns an instnace of the SilkForm singleton.  Most
	 * people can generally use forms() instead of this, but they
	 * both do the same thing.
	 *
	 * @return SilkForm The singleton SilkForm instance
	 * @author Ted Kulp
	 **/
	static public function get_instance()
	{
		if (self::$instance == NULL)
		{
			self::$instance = new SilkForm();
		}
		return self::$instance;
	}

	/**
	 * Returns the start of a module form\n
	 * Parameters:
	 * - 'action' - The action that this form should do when the form is submitted.  Defaults to 'default'.
	 * - 'method' - Method to put in the form tag.  Defaults to 'post'.
	 * - 'enctype' - Optional enctype for the form.  Only real option is 'multipart/form-data'.  Defaults to null.
	 * - 'inline' - Boolean to tell whether or not we want the form's result to be "inline".  Defaults to false.
	 * - 'id_suffix' - Text to append to the end of the id and name of the form.  Defaults to ''.
	 * - 'extra' - Text to append to the <form>-statement, ex. for javascript-validation code.  Defaults to ''.
	 * - 'html_id' - Id to use for the html id="".  Defaults to an autogenerated value.
	 * - 'use_current_page_as_action' - A flag to determine if the action should just
	 *      redirect back to this exact page.  Defaults to false.
	 * - 'remote' - Boolean to add an onsubmit that will serialize the form contents and submit it via an
	 *      XMLHttpRequest instead of the traditional POST.  Defaults to false.
	 * - 'params' - An array of key/value pairs to add as extra hidden parameters.  These will merge into any
	 *      additional parameters you pass along in to the $params hash that aren't parsed by the function.
	 *
	 * @param array An array of parameters to pass to the method.  Unrecognized parameters will be added as hidden
	 *        variables to the form and merged correctly with anything in the 'params' key if passed.
	 * @param boolean Test whether keys are all valid or not.  Not helpful if you're
	 *        passing extra key/values along, but good for debugging.
	 * @return string
	 * @author Ted Kulp
	 **/
	public function create_form_start($params = array(), $check_keys = false)
	{
		$default_params = array(
			'action' => coalesce_key($params, 'action', '', FILTER_SANITIZE_URL),
			'controller' => coalesce_key($params, 'controller', '', FILTER_SANITIZE_URL),
			'method' => coalesce_key($params, 'method', 'post', FILTER_SANITIZE_STRING),
			'enctype' => coalesce_key($params, 'enctype', '', FILTER_SANITIZE_STRING),
			'inline' => coalesce_key($params, 'inline', false, FILTER_VALIDATE_BOOLEAN),
			'id_suffix' => coalesce_key($params, 'id_suffix', '', FILTER_SANITIZE_STRING),
			'url' => coalesce_key($params, 'url', SilkRequest::get_requested_uri()),
			'extra' => coalesce_key($params, 'extra', ''),
			'remote' => coalesce_key($params, 'remote', false, FILTER_VALIDATE_BOOLEAN),
			'params' => coalesce_key($params, 'params', array())
		);
		$default_params['html_id'] = coalesce_key($params,
			'html_id',
			SilkResponse::make_dom_id('form_'.$default_params['action'].$default_params['id_suffix']),
			FILTER_SANITIZE_STRING
		);
		$default_params['html_name'] = coalesce_key($params,
			'html_name',
			$default_params['html_id'],
			FILTER_SANITIZE_STRING
		);

		if ($check_keys && !are_all_keys_valid($params, $default_params))
			throw new SilkInvalidKeyException(invalid_key($params, $default_params));

		//Strip out any straggling parameters to their own array
		//Merge in anything if it was passed in the params key to the method
		$extra_params = forms()->strip_extra_params($params, $default_params, 'params');
		
		//Need to set the URL if only an action was passed
		if ($params['action'] != '' && $params['url'] == SilkRequest::get_requested_uri())
		{
			$params['url'] = SilkResponse::create_url(array('action' => $params['action'], 'controller' => $params['controller']), false);
		}
		
		$form_params = array(
			'id' => $params['html_id'],
			'name' => $params['html_name'],
			'method' => $params['method'],
			'action' => $params['url']
		);

		if ($params['enctype'] != '')
		{
			$form_params['enctype'] = $params['enctype'];
		}

		$extra = '';
		if ($params['extra'])
		{
			$extra = $params['extra'];
			unset($params['extra']);
		}

		if ($params['remote'] == true)
		{
			$form_params['onsubmit'] = "silk_ajax_call('".$form_params['action']."', $(this).serializeArray()); return false;";
		}

		$text = forms()->create_start_tag('form', $form_params, false, $extra);

		foreach ($extra_params as $key=>$value)
		{
			$text .= forms()->create_start_tag('input', array('type' => 'hidden', 'name' => $key, 'value' => $value), true);
		}

		return $text;
	}

	/**
	 * Returns the end of a module form\n
	 * Parameters:
	 * - none
	 *
	 * @param array An array of parameters to pass to the method.  Unrecognized parameters will be added as hidden
	 *        variables to the form and merged correctly with anything in the 'params' key if passed.
	 * @param boolean Test whether keys are all valid or not.  Not helpful if you're
	 *        passing extra key/values along, but good for debugging.
	 * @return string
	 * @author Ted Kulp
	 **/
	public function create_form_end($params = array(), $check_keys = false)
	{
		return forms()->create_end_tag('form');
	}

	/**
	 * Returns the xhtml equivalent of an input textbox.  This is basically a nice little wrapper
	 * to make sure that id's are placed in names and also that it's xhtml compliant.
	 * Parameters:
	 * - 'name' - The name of the field.  Defaults to 'input'.
	 * - 'value' - The value of the field.  Defaults to ''.
	 * - 'size' - The length of the input field when displayed.  Defaults to 10.
	 * - 'maxlength' - The max length of the value allowed.  Defaults to 255.
	 * - 'extra' - Text to append to the <input>-statement, ex. for javascript-validation code.  Defaults to ''.
	 * - 'html_id' - Id to use for the html id="".  Defaults to an autogenerated value.
	 * - 'label' - If set to a string, a label will be created with the proper "for" value for the input.
	 * - 'label_extra' - Text to append to the <label>-statement, ex. for javascript-validation code.  Defaults to ''.
	 * - 'in_between_text' - Text to put between the label and input fields.  Defaults to ''.
	 * - 'label_div' - Name of the div to wrap around the label. Defaults to "block"
	 * - 'password' - Boolean to tell whether or not we want it to be a password input.  Defaults to false.
	 * - 'params' - An array of key/value pairs to add as attributes to the input command.  These will merge into any
	 *      additional parameters you pass along in to the $params hash that aren't parsed by the function.
	 *
	 * @param array An array of parameters to pass to the method.  Unrecognized parameters will be added as attributes to the
	 *        tag and merged correctly with anything in the 'params' key if passed.
	 * @param boolean Test whether keys are all valid or not.  Not helpful if you're
	 *        passing extra key/values along, but good for debugging.
	 * @return string
	 * @author Ted Kulp
	 */
	public function create_input_text($params = array(), $check_keys = false)
	{
		$default_params = array(
			'name' => coalesce_key($params, 'name', 'input', FILTER_SANITIZE_STRING),
			'value' => coalesce_key($params, 'value', '', FILTER_SANITIZE_STRING),
			'size' => coalesce_key($params, 'size', 25, FILTER_SANITIZE_NUMBER_INT),
			'maxlength' => coalesce_key($params, 'maxlength', 255, FILTER_SANITIZE_NUMBER_INT),
			'extra' => coalesce_key($params, 'extra', ''),
			'label' => coalesce_key($params, 'label', '', FILTER_SANITIZE_STRING),
			'label_extra' => coalesce_key($params, 'label_extra', ''),
			'in_between_text' => coalesce_key($params, 'in_between_text', ''),
			'label_div' => coalesce_key($params, 'label_div', 'block'),
			'password' => coalesce_key($params, 'password', false, FILTER_VALIDATE_BOOLEAN),
			'params' => coalesce_key($params, 'params', array())
		);
		$default_params['id'] = coalesce_key($params,
			'html_id',
			SilkResponse::make_dom_id($default_params['name']),
			FILTER_SANITIZE_STRING
		);
		unset($params['html_id']);

		if ($check_keys && !are_all_keys_valid($params, $default_params))
			throw new SilkInvalidKeyException(invalid_key($params, $default_params));

		//Combine EVERYTHING together into a big managerie
		$params = array_merge($default_params, forms()->strip_extra_params($params, $default_params, 'params'));
		unset($params['params']);

		$extra = '';
		if ($params['extra'])
		{
			$extra = $params['extra'];
		}
		unset($params['extra']);

		$params['type'] = ($params['password'] == true ? 'password' : 'text');
		unset($params['password']);

		$text = '';

		if ($params['label'] != '')
		{
			$text .= forms()->create_start_tag('label', array('for' => $params['id'], 'class' => $params['label_div']), false, $params['label_extra']);
			$text .= $params['label'];
			$text .= forms()->create_end_tag('label');
			$text .= $params['in_between_text'];
		}

		unset($params['label']);
		unset($params['label_extra']);
		unset($params['in_between_text']);

		$text .= forms()->create_start_tag('input', $params, true, $extra);

		return $text;
	}

	public function create_input_textarea($params = array(), $check_keys = false)
	{
		$default_params = array(
			'name' => coalesce_key($params, 'name', 'input', FILTER_SANITIZE_STRING),
			'value' => coalesce_key($params, 'value', ''),
			'rows' => coalesce_key($params, 'rows', 5, FILTER_SANITIZE_NUMBER_INT),
			'cols' => coalesce_key($params, 'cols', 40, FILTER_SANITIZE_NUMBER_INT),
			'extra' => coalesce_key($params, 'extra', ''),
			'label' => coalesce_key($params, 'label', '', FILTER_SANITIZE_STRING),
			'label_extra' => coalesce_key($params, 'label_extra', ''),
			'in_between_text' => coalesce_key($params, 'in_between_text', ''),
			'params' => coalesce_key($params, 'params', array())
		);
		$default_params['id'] = coalesce_key($params,
			'html_id',
			SilkResponse::make_dom_id($default_params['name']),
			FILTER_SANITIZE_STRING
		);

		if ($check_keys && !are_all_keys_valid($params, $default_params))
			throw new SilkInvalidKeyException(invalid_key($params, $default_params));

		//Combine EVERYTHING together into a big managerie
		$params = array_merge($default_params, forms()->strip_extra_params($params, $default_params, 'params'));
		unset($params['params']);

		$text = "";
		$extra = '';
		if ($params['extra'])
		{
			$extra = $params['extra'];
		}
		unset($params['extra']);

		$value = $params['value'];
		unset($params['value']);

		if ($params['label'] != '')
		{
			$text .= forms()->create_start_tag('label', array('for' => $params['id']), false, $params['label_extra']);
			$text .= $params['label'];
			$text .= forms()->create_end_tag('label');
			$text .= $params['in_between_text'];
		}

		unset($params['label']);
		unset($params['label_extra']);
		unset($params['in_between_text']);

		$text .= forms()->create_start_tag('textarea', $params, false, $extra) . htmlentities($value, ENT_COMPAT, 'UTF-8') . forms()->create_end_tag('textarea');

		return $text;
	}

	/**
	 * Returns the xhtml equivalent of an hidden input.  This is basically a nice little wrapper
	 * to make sure that id's are placed in names and also that it's xhtml compliant.\n
	 * Parameters:
	 * - 'name' - The name of the field.  Defaults to 'input'.
	 * - 'value' - The value of the field.  Defaults to ''.
	 * - 'extra' - Text to append to the <input>-statement, ex. for javascript-validation code.  Defaults to ''.
	 * - 'html_id' - Id to use for the html id="".  Defaults to an autogenerated value.
	 * - 'params' - An array of key/value pairs to add as attributes to the input command.  These will merge into any
	 *      additional parameters you pass along in to the $params hash that aren't parsed by the function.
	 *
	 * @param array An array of parameters to pass to the method.  Unrecognized parameters will be added as attributes to the
	 *        tag and merged correctly with anything in the 'params' key if passed.
	 * @param boolean Test whether keys are all valid or not.  Not helpful if you're
	 *        passing extra key/values along, but good for debugging.
	 * @return string
	 * @author Ted Kulp
	 */
	public function create_input_hidden($params = array(), $check_keys = false)
	{
		$default_params = array(
			'name' => coalesce_key($params, 'name', 'input', FILTER_SANITIZE_STRING),
			'value' => coalesce_key($params, 'value', '', FILTER_SANITIZE_STRING),
			'extra' => coalesce_key($params, 'extra', ''),
			'params' => coalesce_key($params, 'params', array())
		);
		$default_params['id'] = coalesce_key($params,
			'html_id',
			SilkResponse::make_dom_id($default_params['name']),
			FILTER_SANITIZE_STRING
		);

		if ($check_keys && !are_all_keys_valid($params, $default_params))
			throw new SilkInvalidKeyException(invalid_key($params, $default_params));

		$params['type'] = 'hidden';

		//Combine EVERYTHING together into a big managerie
		$params = array_merge($default_params, forms()->strip_extra_params($params, $default_params, 'params'));
		unset($params['params']);

		$extra = '';
		if ($params['extra'])
		{
			$extra = $params['extra'];
		}
		unset($params['extra']);

		return forms()->create_start_tag('input', $params, true, $extra);
	}

	/**
	 * Returns the xhtml equivalent of an checkbox input.  This is basically a nice little wrapper
	 * to make sure that id's are placed in names and also that it's xhtml compliant.  Also adds the feature
	 * of making sure that even unchecked checkboxes return a value back to the form.\n
	 * Parameters:
	 * - 'name' - The name of the field.  Defaults to 'input'.
	 * - 'checked' - Boolean of whether or not the checkbox is checked.  Defaults to false.
	 * - 'extra' - Text to append to the <input>-statement, ex. for javascript-validation code.  Defaults to ''.
	 * - 'html_id' - Id to use for the html id="".  Defaults to an autogenerated value.
	 * - 'params' - An array of key/value pairs to add as attributes to the input command.  These will merge into any
	 *      additional parameters you pass along in to the $params hash that aren't parsed by the function.
	 *
	 * @param array An array of parameters to pass to the method.  Unrecognized parameters will be added as attributes to the
	 *        tag and merged correctly with anything in the 'params' key if passed.
	 * @param boolean Test whether keys are all valid or not.  Not helpful if you're
	 *        passing extra key/values along, but good for debugging.
	 * @return string
	 * @author Ted Kulp
	 */
	public function create_input_checkbox($params = array(), $check_keys = false)
	{
		$default_params = array(
			'name' => coalesce_key($params, 'name', 'input', FILTER_SANITIZE_STRING),
			'checked' => coalesce_key($params, 'checked', false, FILTER_VALIDATE_BOOLEAN),
			'extra' => coalesce_key($params, 'extra', ''),
			'params' => coalesce_key($params, 'params', array())
		);
		$default_params['id'] = coalesce_key($params,
			'html_id',
			SilkResponse::make_dom_id($default_params['name']),
			FILTER_SANITIZE_STRING
		);

		if ($check_keys && !are_all_keys_valid($params, $default_params))
			throw new SilkInvalidKeyException(invalid_key($params, $default_params));

		//Combine EVERYTHING together into a big managerie
		$params = array_merge($default_params, forms()->strip_extra_params($params, $default_params, 'params'));
		unset($params['params']);

		$params['type'] = 'checkbox';
		$params['value'] = '1';

		if ($params['checked'])
			$params['checked'] = 'checked';
		else
			unset($params['checked']);

		$extra = '';
		if ($params['extra'])
		{
			$extra = $params['extra'];
		}
		unset($params['extra']);

		return forms()->create_start_tag('input', array('type' => 'hidden', 'name' => $params['name'], 'value' => '0'), true) .
			forms()->create_start_tag('input', $params, true, $extra);
	}

	/**
	 * Returns the xhtml equivalent of an submit button.  This is basically a nice little wrapper
	 * to make sure that id's are placed in names and also that it's xhtml compliant.\n
	 * Parameters:
	 * - 'name' - The name of the field.  Defaults to 'input'.
	 * - 'value' - The value (text) of the button.  Defaults to ''.
	 * - 'extra' - Text to append to the <input>-statement, ex. for javascript-validation code.  Defaults to ''.
	 * - 'html_id' - Id to use for the html id="".  Defaults to an autogenerated value.
	 * - 'image' - The name of an image to display instead of the text.  Defaults to ''.
	 * - 'confirm_text' - If set, a message to display to confirm the click.  Defaults to ''.
	 * - 'params' - An array of key/value pairs to add as attributes to the input tag.  These will merge into any
	 *      additional parameters you pass along in to the $params hash that aren't parsed by the function.
	 * - 'reset' - Boolean of whether or not this is a reset buton.  Defaults to false.
	 *
	 * @param array An array of parameters to pass to the method.  Unrecognized parameters will be added as attributes to the
	 *        tag and merged correctly with anything in the 'params' key if passed.
	 * @return string
	 * @author Ted Kulp
	 */
	public function create_link($params = array())
	{
		$tag_params = array(
			'text' => coalesce_key($params, 'text', ''),
			'href' => coalesce_key($params, 'href', ''),
			'onclick' => coalesce_key($params, 'onclick', ''),
			'only_href' => coalesce_key($params, 'only_href', false, FILTER_VALIDATE_BOOLEAN),
			'remote' => coalesce_key($params, 'remote', false, FILTER_VALIDATE_BOOLEAN),
			'confirm_text' => coalesce_key($params, 'confirm_text', '', FILTER_SANITIZE_STRING),
			'params' => coalesce_key($params, 'params', array())
		);
		$tag_params['id'] = coalesce_key($params,
			'html_id',
			'',
			FILTER_SANITIZE_STRING
		);
		$tag_params['class'] = coalesce_key($params,
			'html_class',
			'',
			FILTER_SANITIZE_STRING
		);
		unset($params['html_class']);
		unset($params['html_id']);
		
		if ($tag_params['id'] != '')
			$tag_params['id'] = SilkResponse::make_dom_id($tag_params['id']);
		else
			unset($tag_params['id']);

		$url_params = forms()->strip_extra_params($params, $tag_params, 'params');
		unset($tag_params['params']);

		if (!empty($url_params))
			$tag_params['href'] = SilkResponse::create_url($url_params, false);

		if ($tag_params['only_href'] == true)
		{
			return $tag_params['href'];
		}
		unset($tag_params['only_href']);
		unset($tag_params['remote']);

		if ($tag_params['confirm_text'] == true)
		{
			if ($tag_params['onclick'] != '')
			{
				$tag_params['onclick'] = "if (confirm('" . $tag_params['confirm_text'] . "')) { " . $tag_params['onclick'] . " } else { return false; }";
			}
			else
			{
				$tag_params['onclick'] = "return confirm('" . $tag_params['confirm_text'] . "');";
			}
		}
		unset($tag_params['confirm_text']);

		if ($tag_params['onclick'] == '')
			unset($tag_params['onclick']);

		$text = $tag_params['text'];
		unset($tag_params['text']);

		$result = forms()->create_start_tag('a', $tag_params);
		$result .= $text;
		$result .= forms()->create_end_tag('a');

		return $result;
	}

	/**
	 * Returns the xhtml equivalent of an submit button.  This is basically a nice little wrapper
	 * to make sure that id's are placed in names and also that it's xhtml compliant.\n
	 * Parameters:
	 * - 'name' - The name of the field.  Defaults to 'input'.
	 * - 'value' - The value (text) of the button.  Defaults to ''.
	 * - 'extra' - Text to append to the <input>-statement, ex. for javascript-validation code.  Defaults to ''.
	 * - 'html_id' - Id to use for the html id="".  Defaults to an autogenerated value.
	 * - 'image' - The name of an image to display instead of the text.  Defaults to ''.
	 * - 'confirm_text' - If set, a message to display to confirm the click.  Defaults to ''.
	 * - 'params' - An array of key/value pairs to add as attributes to the input tag.  These will merge into any
	 *      additional parameters you pass along in to the $params hash that aren't parsed by the function.
	 * - 'reset' - Boolean of whether or not this is a reset buton.  Defaults to false.
	 *
	 * @param array An array of parameters to pass to the method.  Unrecognized parameters will be added as attributes to the
	 *        tag and merged correctly with anything in the 'params' key if passed.
	 * @param boolean Test whether keys are all valid or not.  Not helpful if you're
	 *        passing extra key/values along, but good for debugging.
	 * @return string
	 * @author Ted Kulp
	 */
	public function create_input_submit($params = array(), $check_keys = false)
	{
		$default_params = array(
			'name' => coalesce_key($params, 'name', 'input', FILTER_SANITIZE_STRING),
			'value' => coalesce_key($params, 'value', '', FILTER_SANITIZE_STRING),
			'extra' => coalesce_key($params, 'extra', ''),
			'image' => coalesce_key($params, 'image', '', FILTER_SANITIZE_STRING),
			'confirm_text' => coalesce_key($params, 'confirm_text', '', FILTER_SANITIZE_STRING),
			'reset' => coalesce_key($params, 'reset', false, FILTER_VALIDATE_BOOLEAN),
			'action' => coalesce_key($params, 'action', '', FILTER_SANITIZE_URL),
			'controller' => coalesce_key($params, 'controller', '', FILTER_SANITIZE_URL),
			'url' => coalesce_key($params, 'url', SilkRequest::get_requested_uri()),
			'remote' => coalesce_key($params, 'remote', false, FILTER_VALIDATE_BOOLEAN),
			'params' => coalesce_key($params, 'params', array())
		);
		$default_params['id'] = coalesce_key($params,
			'html_id',
			SilkResponse::make_dom_id($default_params['name']),
			FILTER_SANITIZE_STRING
		);

		if ($check_keys && !are_all_keys_valid($params, $default_params))
			throw new SilkInvalidKeyException(invalid_key($params, $default_params));

		//Combine EVERYTHING together into a big managerie
		$params = array_merge($default_params, forms()->strip_extra_params($params, $default_params, 'params'));
		unset($params['params']);

		if ($params['reset'])
		{
			$params['type'] = 'reset';
		}
		else if ($params['image'] != '')
		{
			$params['type'] = 'image';
			$params['src'] = $params['image'];
		}
		else
		{
			$params['type'] = 'submit';
		}
		unset($params['image']);
		unset($params['reset']);
		
		//Need to set the URL if only an action was passed
		if ($params['action'] != '' && $params['url'] == SilkRequest::get_requested_uri())
		{
			$params['url'] = SilkResponse::create_url(array('action' => $params['action'], 'controller' => $params['controller']), false);
		}
		
		if ($params['remote'] == true)
		{
			$params['onclick'] = "var ary = $(this.form).serializeArray(); ary.push({name:'".$params['name']."', value:'".$params['value']."'}); silk_ajax_call('".$params['url']."', ary); return false;";
		}
		unset($params['remote']);
		unset($params['url']);
		
		$extra = '';
		if ($params['extra'])
		{
			$extra = $params['extra'];
			if ($params['confirm_text'] != '')
 			{
				$extra .= ' onclick="return confirm(\''.$params['confirm_text'].'\');"';
			}
		}
		unset($params['extra']);
		unset($params['confirm_text']);
		
		return forms()->create_start_tag('input', $params, true, $extra);
	}

	/**
	 * Returns the xhtml equivalent of the opening of a select input.  This is basically a nice little wrapper
	 * to make sure that id's are placed in names and also that it's xhtml compliant.\n
	 * Parameters:
	 * - 'name' - The name of the field.  Defaults to 'input'.
	 * - 'extra' - Text to append to the <input>-statement, ex. for javascript-validation code.  Defaults to ''.
	 * - 'html_id' - Id to use for the html id="".  Defaults to an autogenerated value.
	 * - 'multiple' - Boolean of whether or not this is should show multiple items.  Defaults to false.
	 * - 'size' - Number of items to show if multiple is set to true.  Defaults to 3.
	 * - 'params' - An array of key/value pairs to add as attributes to the input tag.  These will merge into any
	 *      additional parameters you pass along in to the $params hash that aren't parsed by the function.
	 *
	 * @param array An array of parameters to pass to the method.  Unrecognized parameters will be added as attributes to the
	 *        tag and merged correctly with anything in the 'params' key if passed.
	 * @param boolean Test whether keys are all valid or not.  Not helpful if you're
	 *        passing extra key/values along, but good for debugging.
	 * @return string
	 * @author Ted Kulp
	 */
	public function create_input_select($params = array(), $check_keys = false)
	{
		$default_params = array(
			'name' => coalesce_key($params, 'name', 'input', FILTER_SANITIZE_STRING),
			'extra' => coalesce_key($params, 'extra', ''),
			'multiple' => coalesce_key($params, 'multiple', false, FILTER_VALIDATE_BOOLEAN),
			'size' => coalesce_key($params, 'size', 3, FILTER_SANITIZE_NUMBER_INT),
			'params' => coalesce_key($params, 'params', array())
		);
		$default_params['id'] = coalesce_key($params,
			'html_id',
			SilkResponse::make_dom_id($default_params['name']),
			FILTER_SANITIZE_STRING
		);

		if ($check_keys && !are_all_keys_valid($params, $default_params))
			throw new SilkInvalidKeyException(invalid_key($params, $default_params));

		//Combine EVERYTHING together into a big managerie
		$params = array_merge($default_params, forms()->strip_extra_params($params, $default_params, 'params'));
		unset($params['params']);

		if ($params['multiple'])
		{
			$params['multiple'] = 'multiple';
		}
		else
		{
			unset($params['multiple']);
			unset($params['size']);
		}

		$extra = '';
		if ($params['extra'])
		{
			$extra = $params['extra'];
		}
		unset($params['extra']);

		return forms()->create_start_tag('select', $params, false, $extra);
	}

	/**
	 * Returns the xhtml equivalent of options tags.  This is basically a nice little wrapper
	 * to make sure that id's are placed in names and also that it's xhtml compliant.\n
	 * Parameters:
	 * - 'items' - An associative array of key/values to represent the value and text of the items in the list.  This can also be
	 *           passed a string in the form of 'key,value,key,value'.  Defaults to array().
	 * - 'selected_value' - A string that will set the matching item (by value) as selected.  Defaults = ''.
	 * - 'selected_index' - An integer that will set the matching item (by index) as selected.  Defaults to -1 (no selection).
	 * - 'selected_values' - An array of strings that will set the matching item as selected.  This is for multiple select items.
	 * - 'extra' - Text to append to the <input>-statement, ex. for javascript-validation code.  Defaults to ''.
	 * - 'flip_items' - Boolean that tells whether or not the value and text of the given items should be swapped.  Defaults to false.
	 *
	 * @param array An array of parameters to pass to the method.  Unrecognized parameters will be added as attributes to the
	 *        tag and merged correctly with anything in the 'params' key if passed.
	 * @param boolean Test whether keys are all valid or not.  Not helpful if you're
	 *        passing extra key/values along, but good for debugging.
	 * @return string
	 * @author Ted Kulp
	 */
	public function create_input_options($params = array(), $check_keys = false)
	{
		$default_params = array(
			'items' => coalesce_key($params, 'items', array()),
			'selected_value' => coalesce_key($params, 'selected_value', '', FILTER_SANITIZE_STRING),
			'selected_index' => coalesce_key($params, 'selected_index', -1, FILTER_SANITIZE_NUMBER_INT),
			'selected_values' => coalesce_key($params, 'selected_values', array()),
			'flip_items' => coalesce_key($params, 'flip_items', false, FILTER_VALIDATE_BOOLEAN),
			'params' => coalesce_key($params, 'params', array())
		);

		if ($check_keys && !are_all_keys_valid($params, $default_params))
			throw new SilkInvalidKeyException(invalid_key($params, $default_params));

		//Combine EVERYTHING together into a big managerie
		$params = array_merge($default_params, forms()->strip_extra_params($params, $default_params, 'params'));
		unset($params['params']);

		$selected_index = $params['selected_index']; unset($params['selected_index']);
		$selected_value = $params['selected_value']; unset($params['selected_value']);
		$selected_values = $params['selected_values']; unset($params['selected_values']);

		$items = $params['items'];
		unset($params['items']);
		if (!is_array($items) && strlen($items) > 0)
		{
			$ary = array_chunk(explode(',', $items), 2);
			$items = array();
			foreach ($ary as $one_item)
			{
				if (count($one_item) == 2)
					$items[$one_item[0]] = $one_item[1];
			}
		}

		if ($params['flip_items'])
			$items = array_flip($items);
		unset($params['flip_items']);

		$text = '';

		$count = 0;
		foreach ($items as $k=>$v)
		{
			$hash = array('value' => $k);
			if ($count == $selected_index || $k == $selected_value || in_array($k, $selected_values))
			{
				$hash['selected'] = 'selected';
			}
			$text .= forms()->create_start_tag('option', $hash) . $v . forms()->create_end_tag('option');
			$count++;
		}

		return $text;
	}

	public function create_label_for_input($params = array(), $check_keys = false)
	{
		if(!isset($params["div"])) {
			$params["div"] = "block";
		}
		return '<label class="' . $params[div] . '">' . $params['content'] . '</label>';
	}

	static public function strip_extra_params(&$params, $default_params, $other_params_key = '')
	{
		$extra_params = array_diff_key($params, $default_params);
		$params = $default_params;
		if ($other_params_key != '' && isset($params[$other_params_key]) && is_array($params[$other_params_key]))
		{
			$extra_params = array_merge($extra_params, $params[$other_params_key]);
			unset($params[$other_params_key]);
		}
		return $extra_params;
	}

	static public function create_start_tag($name, $params, $self_close = false, $extra_html = '')
	{
		$text = "<{$name}";

		foreach ($params as $key=>$value)
		{
			if ($value != '')
				$text .= " {$key}=\"{$value}\"";
		}

		if ($extra_html != '')
		{
			$text .= " {$extra_html}";
		}

		$text .= ($self_close ? ' />' : '>');

		return $text;
	}

	static public function create_end_tag($name)
	{
		return "</{$name}>";
	}


	/**
	 * Generate a form based on defaults set by column type
	 * $params:
	 * any "id" field or a field with "_id" in it is hidden by default
	 * Use $params[fields][fieldname][visible] = "yes" to override
	 * All other fields are shown by default
	 * Use $params[fields][fieldname][visible] = "hidden" to hide the field, or "none" to not have it on the form at all
	 *
	 * $obj:
	 * a single object of the class being autoform'd
	 * if an array of results is passed, the first record will be used to populate the form.
	 */
	public function auto_form($obj, $params, $extra_fields = array(), $start_form = true, $submit = true, $end_form = true) {

		$default_params = array("div" => get_class($this), "submitValue" => "Submit");

		if( is_array($obj) ) $obj = $obj[0];

		$params = array_merge($default_params, $params);
		$fields = $obj->get_columns_in_table();

		$form_params = array(   "action" => isset($params["action"]) ? $params["action"] : "",
								"controller" => isset($params["controller"]) ? $params["controller"] : "",
								"method" => isset($params["method"]) ? $params["method"] : "",
								"remote" => isset($params["remote"]) ? $params["remote"] : "");

		$form = "<div class='autoform " . $params["div"] . "_autoform'>";
		if( $start_form ) { $form .= SilkForm::create_form_start(array($form_params)); }

		foreach( $fields as $field ) {
			$element = "<div>";
			$input_params = array(   "name" => $field->name,
									"value" => $obj->params[$field->name], // get the values in here from the model: $this->$field->name ??
									"label" => humanize($field->name),
									"label_extra" => "class='block'"
			);
			if( strtolower($field->name) == "password" ) {
				$input_params["password"] = true;
			} else {
				$input_params["password"] = false;
			}

			if( isset($params["fields"]["$field->name"]["label"]) ) $input_params["label"] = $params["fields"][$field->name]["label"];
			if( isset($params["fields"]["$field->name"]["override"])) $field->type = "override";
			switch( $field->type ) {

				case "int":
				case "varchar":
				case "datetime":
					if( $field->type == "varchar" ) $input_params["maxlength"] = $field->max_length;

					if( ( $field->name == "id" || strpos($field->name, "_id") != 0 || $field->name == "type" ) && empty($params["fields"][$field->name]["visible"]) ) {
						$element .= SilkForm::create_input_hidden($input_params);
					} elseif( isset($params["fields"][$field->name]["visible"])) {
						if( $params["fields"][$field->name]["visible"] == "hidden" ) {
							$element .= SilkForm::create_input_hidden($input_params);
						}
					} elseif( isset($params["fields"][$field->name]["visible"])) {
						if( $params["fields"][$field->name]["visible"] == "none" ) {
							// do nothing
						}
					} else {
						$element .= SilkForm::create_input_text($input_params);
						if(strtolower($field->name) == "password") {
							$input_params = array(	"name" => "confirm_password",
													"label" => humanize("confirm_password"),
													"label_extra" => "class='block'",
													"password" => true
													);						
							$element .= "</div><div>" . SilkForm::create_input_text($input_params);
						}
					}
					break;

				case "text":
					if( $params["fields"][$field->name]["visible"] == "hidden" ) {
						$element .= SilkForm::create_input_hidden($input_params);
					} elseif( $params["fields"][$field->name]["visible"] == "none" ) {
						// do nothing
					} else {
						$element .= SilkForm::create_input_textarea($input_params);
					}
					break;
					
				case "override":
					if( isset( $params["fields"]["$field->name"]["label"] )) {
						$element .= SilkForm::create_label_for_input(array("content" => $params["fields"]["$field->name"]["label"]));
					}
					$element .= $params["fields"]["$field->name"]["override"];
					break;

				default:
					$element .= "SilkForm does not currently support ($field->type) fields.<br />";
					break;
			}
			if( $element != "<div>" ) {
				$element .= "</div>";
			} else {
				$element = "";
			}
			$form .= $element;
		}
		if( $submit ) { $form .= "<div>" . SilkForm::create_input_submit(array("value" => $params["submitValue"])). "</div>"; }
		if( $end_form ) { $form .= SilkForm::create_form_end(); }
		$form .= "</div>";
		return $form;
	}

	/**
	 * Show the data in an object
	 *
	 * @param unknown_type $params
	 */
	public function data_table($obj) {
		if( is_array($obj) ) $obj = $obj[0];
		$fields = $obj->get_columns_in_table();
		$field_names = array();
		foreach($fields as $field) {
			$field_names[] = $field->name;
		}
		
		$table = "";
		foreach($obj->params as $key=>$value) {
			if( $key != "id" && strpos( $key, "_id") == 0 && in_array($key, $field_names)) {
				$table .= "<div>";

				$table .= forms()->create_start_tag('label', array('for' => $key, 'class' => 'autoform data_table label'), false);
				$table .= $key;
				$table .= forms()->create_end_tag('label') . ": ";

				$table .= forms()->create_start_tag('label', array('for' => $key, 'class' => 'autoform data_table data'), false);
				$table .= $value;
				$table .= forms()->create_end_tag('label');

				$table .= "</div>";
			}
		}
		return $table;
	}
}

# vim:ts=4 sw=4 noet
?>
