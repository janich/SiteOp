<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.SiteOp
 *
 * @copyright   Copyright (C) 2016 CGOnline.dk, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

class ACompressor
{
	protected   $type = '';
	protected   $hash = '';
	protected   $buffer = '';
	protected   $append = '';
	protected   $files = array();
	public      $minifier = null;


	public function __construct($type = '', $cache_dir = JPATH_CACHE, $cache_timeout = 15)
	{
		$this->type     = $type;
		$this->dir      = $cache_dir;
		$this->timeout  = $cache_timeout * 60;
		$this->root     = str_replace('\\', '/', JPATH_ROOT);
		$this->uri      = JUri::base(true);
		$this->sroot    = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
	}


	public function addText($text = '')
	{
		$this->hash     .= md5($text);
		$this->append   .= $text;
	}


	public function addFile($path = '')
	{
		$file   = $path;
		$uri    = $path;
		$local  = false;

		if (substr($path, 0, 2) == '//') {
			// Remote file - probably from a cdn
			$file = 'http:' . $path;
			$uri = $file;
		} else if (substr($path, 0, 1) == '/') {
			// Local file
			$file = $this->sroot . $path;
			$uri = $path;
			$local = true;
		} else if (substr($path, 0, 4) == 'http') {
			// Remote file - Maybe a font or the likes?
		} else {
			// Assume file is relative to Joomla
			$file = $this->root . '/' . $path;
			$uri = $this->uri . '/' . $path;
			$local = true;
		}

		if ($local && JFile::exists($file)) {
			$this->hash .= filemtime($file);
		}

		$this->files[] = array(
			'file'  => $file,
			'uri'   => $uri,
			'local' => $local
		);

		// Debug:
		//echo "<!-- ". str_pad($file, 80, " ") .' '. str_pad($uri, 80, " ") .' '. str_pad(($local ? 'local' : 'remote'), 10, " ") ."-->\n";
	}


	public function getFilename()
	{
		return md5($this->hash) . '.' . $this->type;
	}


	public function hasCache()
	{
		$file = $this->dir . $this->getFilename();

		if (!JFile::exists($file)) {
			return false;
		}

		$mod = filemtime($file);
		$now = time();

		if ($mod < ($now - $this->timeout)) {
			return false;
		}

		return true;
	}


	public function compress($minify = false)
	{
		$this->combine();

		if ($this->append) {
			$this->buffer .= "\n/** Inline data: */\n" . $this->append . "\n\n";
			$this->append = '';
		}

		if ($this->minifier) {
			$this->minify();
		}

		return JFile::write($this->dir . $this->getFilename(), $this->buffer);
	}


	protected function combine()
	{
		foreach ($this->files as $data) {
			$content = $this->getFileContents($data);

			if ($content !== false) {
				$this->buffer .= "\n/** File: " . $data['uri'] . " */\n$content\n\n";
			}
		}
	}


	protected function minify()
	{
		if (is_object($this->minifier)) {
			$class = get_class($this->minifier);

			switch (strtolower($class)) {
				case 'cssmin':
					$this->buffer = $this->minifier->run($this->buffer);
					break;
				default:
			}
		} else if (is_array($this->minifier)) {
			$this->buffer = call_user_func($this->minifier, $this->buffer);
		}
	}


	protected function getFileContents($file = array())
	{
		$data = file_get_contents($file['file']);

		if ($data !== false && $file['local']) {
			$path = dirname($file['uri']) . '/';
			$search = '#url\((?!\s*[\'"]?(?:https?:)?//)\s*([\'"])?#';
			$replace = "url($1" . $path;
			$data = preg_replace($search, $replace, $data);
		}

		return $data;
	}
}
