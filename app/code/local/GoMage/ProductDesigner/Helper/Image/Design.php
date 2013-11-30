<?php
/**
 * GoMage.com
 *
 * GoMage ProductDesigner Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.0
 * @since        Class available since Release 1.0
 */

/**
 * Category image helper
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @subpackage Helper
 * @author     Roman Bublik <rb@gomage.com>
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
        $this->_width = null;
        $this->_height = null;
        $this->_filename = $filename;
        $this->_baseDir = Mage::getSingleton('gomage_designer/design_config')->getBaseMediaPath();
        $imageExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
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
}
