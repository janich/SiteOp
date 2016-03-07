<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.SiteOp
 *
 * @copyright   Copyright (C) 2016 CGOnline.dk, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

class SiteOpJSHelper extends SiteOpHelper
{
    public function process()
    {
        // Remove JS files from header
        $this->removeFiles();

        // Add JS files to header
        $this->addFiles();

        // Add inline JS to header
        $this->addInline();

        // Combine any JS files
        $this->combine();
    }


    public function addFiles()
    {
        $add    = (int) $this->params->get('scriptAdd', '0');
        $list   = $this->params->get('scriptAddList', '');

        if ($add)
        {
            $files = $this->getArrayFromText($list);
            foreach ($files as $file)
            {
                $this->doc->addScript($file, 'text/javascript', true, false);
            }
        }
    }


    public function addInline()
    {
        $add    = (int) $this->params->get('scriptAdd', '0');
        $script = $this->params->get('scriptAddInline', '');

        if ($add)
        {
            $script = trim($script);
            if ($script)
            {
                $this->doc->addScriptDeclaration($script);
            }
        }
    }


    public function removeFiles()
    {
        $remove = (int)$this->params->get('scriptRemove', '0');
        $list   = $this->params->get('scriptRemoveList', '');

        if ($remove)
        {
            $files = $this->getArrayFromText($list);
            $this->unsetFiles($this->doc->_scripts, $files);
        }
    }


    public function combine()
    {
        $combineJS      = $this->params->get('scriptCombine', 'none');
        $combineList    = $this->params->get('scriptCombineList', '');
        $minifyJS       = (int) $this->params->get('scriptMinify', '0');
        $cacheTime      = (int) $this->params->get('scriptCachetime', '0');

        /**
         * Possible values:
         *  all      = Combine all files
         *  ignore   = Combine all files, except the combineList entries
         *  files    = Combine only the combineList entries
         */
        if ($combineJS != 'none')
        {
            // Set up combiner/compressor
            $compressor = new ACompressor('js', $this->cache_dir, $cacheTime);

            // Moved inline scripts to compressor
            if (isset($this->doc->_script['text/javascript']))
            {
                $compressor->addText($this->doc->_script['text/javascript']);
                unset($this->doc->_script['text/javascript']);
            }

            // Sort out which files to compress
            $input = $this->getArrayFromText($combineList);
            $files = $this->parseFilelist($this->doc->_scripts, $input, $combineJS);
            foreach ($files as $file)
            {
                // Add file to compressor and unset from header adhoc
                $compressor->addFile($file);
                $this->unsetFiles($this->doc->_scripts, array($file));
            }

            // Figure out if a re-combine is needed
            if (!$compressor->hasCache())
            {
                // If so, should we minify result?
                if ($minifyJS)
                {
                    $compressor->minifier = array('Minifier', 'minify');
                }

                // Do magic
                $compressor->compress();
            }

            // Add combined file
            $this->doc->addScript($this->cache_uri . $compressor->getFilename(), 'text/javascript', true);
        }
    }
}
