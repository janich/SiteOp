<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.SiteOp
 *
 * @copyright   Copyright (C) 2016 CGOnline.dk, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');


/**
 * Joomla! Optimisation plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  System.SiteOp
 */
class PlgSystemSiteOp extends JPlugin
{
    public function onBeforeCompileHead()
    {
    	if (!$this->isValidRequest()) {
			return;
		}

		$this->process('css');
        $this->process('js');
	}


	public function onAfterRender()
	{
        if (!$this->isValidRequest()) {
			return;
		}

		$this->process('html');
	}


    private function isValidRequest()
    {
        return (!JFactory::getApplication()->isAdmin() && JFactory::getDocument()->getType() === 'html');
    }


	private function process($type = '')
	{
        require_once __DIR__ . '/lib/Minifier.php';
        require_once __DIR__ . '/lib/cssmin.php';
        require_once __DIR__ . '/lib/acompressor.php';
        require_once __DIR__ . '/helper/' . $type . '.php';

        $class = 'SiteOp' . strtoupper($type) . 'Helper';

        $helper = new $class($this->params);
        $helper->process();
	}
}
