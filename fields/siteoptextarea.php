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
JFormHelper::loadFieldClass('textarea');

class JFormFieldSiteopTextarea extends JFormFieldTextarea
{
    public $type = 'siteoptextarea';

    public function getInput()
    {
        JFactory::getDocument()->addStyleDeclaration('#' . $this->id . ' { font-family: monospace; height: 140px; width: 590px; max-width: 95%; color #111; } #' . $this->id . ':active, #' . $this->id . ':focus, #' . $this->id . ':hover { color: #000; }');
        return parent::getInput();
    }
}
