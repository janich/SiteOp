<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.SiteOp
 *
 * @copyright   Copyright (C) 2016 CGOnline.dk, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

class SiteOpHelper
{
    protected $params;
    protected $cache_uri = '';
    protected $cache_dir = '';
    protected $cache_base = 'plg_system_siteop';


    public function __construct($params)
    {
        $this->params       = $params;

        $this->app          = JFactory::getApplication();
        $this->sess         = JFactory::getSession();
        $this->doc          = JFactory::getDocument();
        $this->root	        = JURI::root(true);
        $this->root	        = '/';

        $this->cache_uri    = JURI::root(true) . '/cache/'. $this->cache_base .'/';
        $this->cache_dir    = JPATH_BASE .'/cache/'. $this->cache_base .'/';

        $this->init();
    }


	public static function getArrayFromText($text = '', $separator = "\r\n")
	{
		$list = (array) explode($separator, $text);
		foreach ($list as $item => $data) {
			if (empty($data)) {
				unset($list[$item]);
			}
		}

		return $list;
	}


	public function unsetFiles(&$source = array(), $files = array())
	{
		foreach ($source as $entry => $data) {
			foreach ($files as $pattern) {
				if (self::stringMatch($entry, $pattern)) {
					unset($source[ $entry ]);
				}
			}
		}
	}

    protected function stringMatch($source, $pattern)
    {
		if (strpos($pattern, '/') === false) {
			$pattern = '*/'. $pattern;
		}
		else if ($pattern{0} == '/' || $pattern{0} != '*') {
			$pattern = '*'. $pattern;
		}

		$pattern = preg_quote($pattern, '/');
		$pattern = str_replace('\*', '.*', $pattern);
		$result = preg_match('/^' . $pattern . '$/i' , $source);

		if (strpos($source, ".js") !== false) {
		    //echo $result ." - ". str_pad($source, 20, " ") ." -- ". str_pad($pattern, 20, " ") ."\n";
		}
		
		return $result;
	}


	protected function parseFilelist(&$sources = array(), $files = array(), $mode = 'none')
	{
		$result = array();

		if ($mode == 'none') {
			return $result;
		}

		foreach ($sources as $entry => $data) {
			if (empty($entry)) {
				continue;
			}

			$combine = false;

			if ($mode == 'all') {
				$combine = true;
			}
			else {
				foreach ($files as $pattern) {
					$combine |= self::stringMatch($entry, $pattern);
				}

				if ($mode == 'ignore') {
					$combine = !$combine;
				}
			}

			if ($combine) {
				$result[] = $entry;
			}
		}

		return $result;
	}


    private function init()
    {
        $this->doc->setGenerator('Joomla! CMS - Enhanced by CGOnline.dk');
    }
}
