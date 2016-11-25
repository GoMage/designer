<?php

/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2016 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use/
 * @version      Release: 2.4.0
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
        $imageExtension  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($imageExtension == 'pdf') {
            $filename = str_replace('.pdf', '.jpg', $filename);
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
        rename($this->_getOriginalFilePath(), $this->_baseDir . DS . $filename);
        $this->init($filename);
        return $this;
    }

}
