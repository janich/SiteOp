<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.SiteOp
 *
 * @copyright   Copyright (C) 2016 CGOnline.dk, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

class SiteOpCSSHelper extends SiteOpHelper
{
	public function process()
	{
		// Remove CSS files from header
		$this->removeFiles();

		// Add CSS files to header
		$this->addFiles();

		// Add inline CSS to header
		$this->addInline();

		// Prioritize CSS load order
		$this->prioritize();

		// Combine any CSS files
		$this->combine();
	}


	public function addFiles()
	{
		$add    = (int) $this->params->get('styleAdd', '0');
		$list   = $this->params->get('styleAddList', '');

		if ($add)
		{
			$files = $this->getArrayFromText($list);
			foreach ($files as $file) {
				$this->doc->addStyleSheet($file, 'text/css', 'all');
			}
		}
	}


	public function addInline()
	{
		$add    = (int) $this->params->get('styleAdd', '0');
		$style  = $this->params->get('styleAddInline', '');

		if ($add)
		{
			$style = trim($style);
			if ($style) {
				$this->doc->addStyleDeclaration($style);
			}
		}
	}


	public function removeFiles()
	{
		$remove = (int) $this->params->get('styleRemove', '0');
		$list   = $this->params->get('styleRemoveList', '');

		if ($remove)
		{
			$files = $this->getArrayFromText($list);
			$this->unsetFiles($this->doc->_styleSheets, $files);
		}
	}


	public function prioritize()
	{
		$prio   = (int) $this->params->get('stylePrioritize', '0');
		$list   = $this->params->get('stylePrioritizeList', '');

		if ($prio) 
		{
			$merge = array();

			$input = $this->getArrayFromText($list);
			$files = $this->parseFilelist($this->doc->_styleSheets, $input, 'only');

			foreach ($files as $file) {
				if (isset($this->doc->_styleSheets[$file])) {
					$merge[$file] = $this->doc->_styleSheets[$file];
				}
			}

			$this->unsetFiles($this->doc->_styleSheets, $files);

			$prioritized = array();
			foreach ($input as $pattern) {
				foreach ($merge as $file => $data) {
					if (self::stringMatch($file, $pattern)) {
						$prioritized[$file] = $data;
					}
				}
			}

			$this->doc->_styleSheets = array_merge($prioritized, $this->doc->_styleSheets);
		}
	}


	public function combine()
	{
		$combineCSS     = $this->params->get('styleCombine', 'none');
		$combineList    = $this->params->get('styleCombineList', '');
		$minifyCSS      = (int) $this->params->get('styleMinify', '0');
		$cacheTime      = (int) $this->params->get('styleCachetime', '0');

		/**
		 * Possible values:
		 *  all      = Combine all files
		 *  ignore   = Combine all files, except the combineList entries
		 *  files    = Combine only the combineList entries
		 */
		if ($combineCSS != 'none') 
		{
			// Set up combiner/compressor
			$compressor = new ACompressor('css', $this->cache_dir, $cacheTime);

			// Moved inline styling to compressor
			if (isset($this->doc->_style['text/css'])) {
				$compressor->addText($this->doc->_style['text/css']);
				unset($this->doc->_style['text/css']);
			}

			// Sort out which files to compress
			$input = $this->getArrayFromText($combineList);
			$files = $this->parseFilelist($this->doc->_styleSheets, $input, $combineCSS);
			foreach ($files as $file) {
				// Add file to compressor and unset from header adhoc
				$compressor->addFile($file);
				$this->unsetFiles($this->doc->_styleSheets, array($file));
			}

			// Figure out if a re-combine is needed
			if (!$compressor->hasCache()) 
			{
				// If so, should we minify result?
				if ($minifyCSS) {
					$compressor->minifier = new CSSmin();
				}

				// Do magic
				$compressor->compress();
			}

			// Add combined file
			$this->doc->addStyleSheet($this->cache_uri . $compressor->getFilename(), 'text/css', 'all');
		}
	}
}
