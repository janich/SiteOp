<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.SiteOp
 *
 * @copyright   Copyright (C) 2016 CGOnline.dk, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('editor');

class JFormFieldSiteopEditor extends JFormFieldEditor
{
	public $type = 'siteopeditor';

	public function getInput()
	{
        JFactory::getDocument()->addStyleDeclaration('textarea + .CodeMirror { max-width: 600px; }');
        return parent::getInput();
	}
}
