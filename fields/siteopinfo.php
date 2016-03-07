<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.SiteOp
 *
 * @copyright   Copyright (C) 2016 CGOnline.dk, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

jimport('joomla.form.formfield');

class JFormFieldSiteopinfo extends JFormField
{
	protected $type = 'siteopinfo';

	public function getInput()
	{
		return '
            <style type="text/css">
                .siteopinfo-container {
                    margin-left: 0px !important;
                }
                .siteopinfo {
                    box-sizing: border-box;
                    padding: 20px 50px;
                    display: inline-block;
                    box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.8);
                }
                .siteopinfo img {
                    float: left;
                    max-width: 200px;
                    margin: 25px 50px 0px 0px;
                }
                .siteopinfo dl {
                    float: left;
                    width: 310px;
                }
                .siteopinfo dt {
                    float: left;
                    clear: left;
                    width: 100px;
                }
                .siteopinfo dd {
                    float: left;
                    width: 200px;
                }
            </style>

            <script type="text/javascript">
                jQuery(document).ready(function (){
                    jQuery(".siteopinfo").parent().addClass("siteopinfo-container");
                });

            </script>

            <div class="siteopinfo">
                <img src="../plugins/system/siteop/fields/logo.jpg" />
                <dl>
                        <dt>Name</dt>		<dd>Site Optimisation</dd>
                        <dt>Version</dt>	<dd>1.0.0</dd>
                        <dt>Author</dt>		<dd>CGOnline.dk</dd>
                        <dt>Website</dt>	<dd><a href="http://www.cgonline.dk" target="_blank">http://www.cgonline.dk</a> </dd>
                        <dt>Contact</dt>	<dd><a href="mailto:info@cgonline.dk">info@cgonline.dk</a> </dd>
                </dl>
		    </div>
		';
	}
}
