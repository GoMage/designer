<?php

/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2018 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use/
 * @version      Release: 2.6.0
 * @since        Available since Release 1.0.0
 */
class GoMage_ProductDesigner_Helper_Image_Design extends GoMage_ProductDesigner_Helper_Image_Abstract
{
    /**
     * Init base image
     *
     * @param string $filename Filename
     * @return GoMage_ProductDesigner_Helper_Image
     */
    public function init($filename)
    {
        $this->_width    = null;
        $this->_height   = null;
        $this->_filename = $filename;
        $this->_baseDir  = Mage::getSingleton('gomage_designer/design_config')->getBaseMediaPath();

        return $this;
    }

    public function initView($filename)
    {
        $this->init($filename);

        $imageExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($imageExtension == 'pdf') {
            $filename = str_replace('.pdf', '.png', $filename);
            if (file_exists($this->_baseDir . $filename)) {
                $this->_filename = $filename;
            }
        }

        return $this;
    }

    /**
     * Get cached file url
     *
     * @return string
     */
    protected function _getCachedUrl()
    {
        $path = Mage::getSingleton('gomage_designer/design_config')->getBaseMediaUrl() . DS . 'cache';
        if ($this->_width || $this->_height) {
            $path .= "/{$this->_width}_{$this->_height}";
        }

        return $path . DS . ltrim($this->_filename, '/');
    }

    /**
     * Get cache dir
     *
     * @return string
     */
    protected function _getCacheDir()
    {
        $dir = Mage::getSingleton('gomage_designer/design_config')->getBaseMediaPath() . DS . 'cache';
        if ($this->_width || $this->_height) {
            $dir .= DS . $this->_width . '_' . $this->_height;
        }
        return $dir;
    }

    /**
     * @param  string $filename
     * @return $this
     */
    public function rename($filename)
    {
        $imageExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($imageExtension == 'pdf') {
            $orig_jpeg_input = str_replace('.pdf', '.png', $this->_getOriginalFilePath());
            $orig_jpeg_output = str_replace('.pdf', '.png', $this->_baseDir . DS . $filename);
            if (file_exists($orig_jpeg_input)) {
                rename($orig_jpeg_input, $orig_jpeg_output);
            }

        }

        rename($this->_getOriginalFilePath(), $this->_baseDir . DS . $filename);
        $this->init($filename);
        return $this;
    }

}
