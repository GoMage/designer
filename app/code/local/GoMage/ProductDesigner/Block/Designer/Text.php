<?php
//@todo Take needed options from admin settings
class GoMage_ProductDesigner_Block_Designer_Text extends Mage_Core_Block_Template
{
    protected $_fonts;

    public function getAvailableColors() {
        $colors = array(
            'AC58FA', '00FFFF', '0A2A0A', 'BFFF00', '61210B', '0B610B', '0B615E ',
            'B40486', '9FF781', '610B21', 'BDBDBD', '2ECCFA', '00FF40', 'FFBF00'
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
        $font = substr(strrchr($font, '/'), 1);
        return substr($font, 0, strrpos($font, '.'));
    }

    /**
     * Return font url
     *
     * @param string $font Font
     * @return mixed
     */
    public function getFontUrl($font)
    {
        return Mage::getSingleton('gmpd/font_gallery_config')->getMediaUrl($font);
    }

    /**
     * Return Fonts collection
     *
     * @return GoMage_ProductDesigner_Model_Mysql4_Font_Collection
     */
    public function getFonts()
    {
        if(is_null($this->_fonts)) {
            $fonts = Mage::getResourceModel('gmpd/font_collection');
            $fonts->addFieldToFilter('disabled', '0');
            $this->_fonts = $fonts;
        }
        return $this->_fonts;
    }

    /**
     * Return default font family
     *
     * @return string
     */
    public function getDefaultFontFamily()
    {
        return Mage::getStoreConfig('gmpd/text/font');
    }

    /**
     * Return default font size
     *
     * @return int
     */
    public function getDefaultFontSize()
    {
        return Mage::getStoreConfig('gmpd/text/size');
    }

    /**
     * Return font sizes
     *
     * @return array
     */
    public function getFontSizes()
    {
        $sizes = array();
        $step = 2;
        for ($size = 16; $size <= 72; $size += $step) {
            $sizes[] = $size;
            if ($size == 24 || $size == 52) {
                $step *= 2;
            } elseif ($size == 60) {
                $step = 12;
            }
        }

        return $sizes;
    }
}