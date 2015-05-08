<?php

/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2015 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use/
 * @version      Release: 2.1.0
 * @since        Available since Release 1.0.0
 */
class GoMage_ProductDesigner_Block_Designer_Text extends Mage_Core_Block_Template
{
    protected $_fonts;

    public function getAvailableColors()
    {
        $colors = array(
            'D6ACFD', '80FFFF', '859585', 'DFFF80', 'B09085', '85B085',
            '85B0AF', 'DA82C3', 'CFFBC0', 'B08590', 'DEDEDE', '97E6FD',
            '80FFA0', 'FFDF80'
        );

        return $colors;
    }

    /**
     * Return font name
     *
     * @param string $font Font
     * @return string
     */
    public function getFontName($font)
    {
        if ($font->getLabel()) {
            return $font->getLabel();
        }
        $fontName = $font->getFont();
        $fontName = substr(strrchr($fontName, '/'), 1);
        return pathinfo($fontName, PATHINFO_FILENAME);
    }

    /**
     * Return font url
     *
     * @param string $font Font
     * @return mixed
     */
    public function getFontUrl($font)
    {
        return Mage::getSingleton('gomage_designer/font_gallery_config')->getMediaUrl($font);
    }

    /**
     * Return Fonts collection
     *
     * @return GoMage_ProductDesigner_Model_Mysql4_Font_Collection
     */
    public function getFonts()
    {
        if (is_null($this->_fonts)) {
            $fonts = Mage::getResourceModel('gomage_designer/font_collection');
            $fonts->addFieldToFilter('disabled', '0')
                ->setOrder('position', 'ASC');
            $this->_fonts = $fonts;
        }
        return $this->_fonts;
    }

    /**
     * Return default fonts
     *
     * @return array
     */
    public function getDefaultFonts()
    {
        return Mage::getModel('gomage_designer/config_source_font')->toOptionArray();
    }

    /**
     * Return default font family
     *
     * @return string
     */
    public function getDefaultFontFamily()
    {
        return Mage::getStoreConfig('gomage_designer/text/font');
    }

    /**
     * Return default font size
     *
     * @return int
     */
    public function getDefaultFontSize()
    {
        return Mage::getStoreConfig('gomage_designer/text/size');
    }

    /**
     * Return font sizes
     *
     * @return array
     */
    public function getFontSizes()
    {
        $sizes = array();
        $step  = 2;
        for ($size = 12; $size <= 72; $size += $step) {
            $sizes[] = $size;
            if ($size == 24 || $size == 52) {
                $step *= 2;
            } elseif ($size == 60) {
                $step = 12;
            }
        }

        return $sizes;
    }

    public function getImplodedFontsString()
    {
        $fonts      = $this->getFonts();
        $fontsArray = array();
        foreach ($fonts as $font) {
            $fontsArray[] = $this->getFontName($font);
        }

        return implode(',', $fontsArray);
    }

    public function effectsEnabled()
    {
        return (bool)Mage::getStoreConfig('gomage_designer/text/effects');
    }
}
