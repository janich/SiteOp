<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.SiteOp
 *
 * @copyright   Copyright (C) 2016 CGOnline.dk, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

class SiteOpHTMLHelper extends SiteOpHelper
{
	public function process()
	{
		$html = JResponse::getBody();

		$code = $this->params->get('htmlAddcode', '');
		if (trim($code))
		{
			$html = str_replace('</body>', $code . '</body>', $html);
		}

		$comments = (int)$this->params->get('htmlRemoveComments', '0');
		if ($comments)
		{
			$html = $this->removeComments($html);
		}

		$newlines = (int)$this->params->get('htmlRemoveNewlines', '0');
		if ($newlines)
		{
			$html = $this->removeNewlines($html);
		}

		$tidy = (int)$this->params->get('htmlTidy', '0');
		if ($tidy)
		{
			$html = $this->tidy($html);
		}

		JResponse::setBody($html);
	}


	public static function removeComments($input = '')
	{
		// Test 1
		//$input = $this->strip_html_tags($input);
		$input = preg_replace('/<!--(?!<!)[^\[>].*?-->/s', '', $input);

		// Test 2
		//$input = preg_replace('/<!--(.*)?-->/s', '', $input);

		return $input;
	}


	public static function removeNewlines($input = '')
	{
		$input = preg_replace('/^\n+|^[\t\s]*\n+/m', '', $input);
		$input = preg_replace('/^\t*+/m', '', $input);
		$input = preg_replace('/\n\s\s+/s', "\n", $input);

		//Removes all indentation
		$input = preg_replace("/\t+/", "", $input);
		$input = preg_replace("/^\s+/", "", $input);

		return $input;
	}


	public static function tidy($input = '')
	{
		if (!class_exists('tidy')) {
			JFactory::getApplication()->enqueueMessage('PHP extension not installed: Tidy', 'warning');

			return $input;
		}

		$config = array(
			'input-xml'           => true,
			'output-xhtml'        => true,

			'hide-comments'       => false,
			'indent'              => true,
			'doctype'             => 'omit',

			'wrap'                => '2000',
			'wrap-attributes'     => false,
			'new-blocklevel-tags' => 'article aside audio details figcaption figure footer header hgroup nav section source summary temp track video',
			'new-empty-tags'      => 'command embed keygen source track wbr',
			'new-inline-tags'     => 'audio canvas command datalist embed keygen mark meter output progress time video wbr',

			'char-encoding'       => 'utf8',
			'input-encoding'      => 'utf8',
			'output-encoding'     => 'utf8'
		);

		$tidy = new tidy();
		$tidy->parseString($input, $config, 'utf8');
		$tidy->cleanRepair();
		$input = tidy_get_output($tidy);

		return $input;
	}


	public static function stripTags($html = '')
	{
		// Basic replacement
		$html = preg_replace(
			array(
				// Remove invisible content
				'@<head[^>]*?>.*?</head>@siu',
				'@<style[^>]*?>.*?</style>@siu',
				'@<script[^>]*?.*?</script>@siu',
				'@<object[^>]*?.*?</object>@siu',
				'@<embed[^>]*?.*?</embed>@siu',
				'@<applet[^>]*?.*?</applet>@siu',
				'@<noframes[^>]*?.*?</noframes>@siu',
				'@<noscript[^>]*?.*?</noscript>@siu',
				'@<noembed[^>]*?.*?</noembed>@siu',
				// Add line breaks before and after blocks
				'@</?((address)|(blockquote)|(center)|(del))@iu',
				'@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
				'@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
				'@</?((table)|(th)|(td)|(caption))@iu',
				'@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
				'@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
				'@</?((frameset)|(frame)|(iframe))@iu',
			),
			array(
				' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', "$0", "$0", "$0", "$0", "$0", "$0", "$0", "$0",), $html);

		// Let's exclude a few html tags for the example
		return strip_tags($html, '<b><i>');
	}
}
