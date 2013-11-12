<?php
class GoMage_ProductDesigner_Model_Font extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('gomage_designer/font');
    }

    public function getFontPath($fontPath)
    {
        $fontPath = str_replace($this->getConfig()->getBaseTmpMediaUrl(), '', $fontPath);
        $fontPath = str_replace($this->getConfig()->getBaseMediaUrl(), '', $fontPath);
        return $fontPath;
    }

    public function getUrl($fontPath)
    {
        return $this->getConfig()->getBaseTmpMediaUrl() . $fontPath;
    }

    public function getDestinationPath($fontPath)
    {
        return $this->getConfig()->getBaseMediaPath() . $fontPath;
    }

    public function getTempPath($fontPath)
    {
        return $this->getConfig()->getBaseTmpMediaPath() . $fontPath;
    }

    public function getDestinationDir($fontPath)
    {
        $expFontPath = explode('/', $fontPath);
        array_pop($expFontPath);
        $fontPath = implode('/', $expFontPath);
        if(strpos($fontPath, $this->getConfig()->getBaseMediaPath()) === false) {
            $fontPath = $this->getConfig()->getBaseMediaPath() . $fontPath;
        }

        return $fontPath;
    }

    public function loadFontByFile($fileName)
    {
        $fontData = $this->getResource()->getFontByName($fileName);
        if ($fontData) {
            $this->setData($fontData);
            return $this;
        }

        return false;
    }

    public function getConfig()
    {
        return Mage::getSingleton('gomage_designer/font_gallery_config');
    }
}