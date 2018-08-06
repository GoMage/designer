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
class GoMage_ProductDesigner_Block_Adminhtml_Catalog_Product_Attribute_Edit_Tab_Options
    extends Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Options
{
    protected $_navigationBlock;

    public function __construct()
    {
        parent::__construct();

        if ($navigationBlock = $this->_getNavigationOptionsBlock()) {
            $this->setTemplate($navigationBlock->getTemplate());
            return;
        }

        if (!Mage::helper('gomage_designer')->isEnabled()) {
            return;
        }
        $colorAttributeCode = Mage::getStoreConfig('gomage_designer/navigation/color_attribute');
        if ($colorAttributeCode && ($this->getAttributeObject()->getAttributeCode() == $colorAttributeCode)) {
            $this->setTemplate('gomage/productdesigner/catalog/product/attribute/options.phtml');
        }

    }

    public function getOptionValues()
    {
        if ($navigationBlock = $this->_getNavigationOptionsBlock()) {
            return $navigationBlock->getOptionValues();
        }
        $values             = parent::getOptionValues();
        $colorAttributeCode = Mage::getStoreConfig('gomage_designer/navigation/color_attribute');
        if (!$colorAttributeCode || !($this->getAttributeObject()->getAttributeCode() == $colorAttributeCode)) {
            return $values;
        }
        if ($values) {
            $images = $this->getAttributeObject()->getOptionImages();
            foreach ($values as $value) {
                if (isset($images[$value['id']]['color_hex'])) {
                    $value->setColor($images[$value['id']]['color_hex']);
                }
            }
        }

        return $values;
    }

    public function getPopupTextValues()
    {
        if ($navigationBlock = $this->_getNavigationOptionsBlock()) {
            return $navigationBlock->getPopupTextValues();
        }
    }

    protected function _getNavigationOptionsBlock()
    {
        if (is_null($this->_navigationBlock)) {
            if (!Mage::helper('gomage_designer')->advancedNavigationEnabled()) {
                $this->_navigationBlock = false;
            } elseif (class_exists('GoMage_Navigation_Block_Adminhtml_Catalog_Product_Attribute_Edit_Tab_Options')) {
                $this->_navigationBlock = new GoMage_Navigation_Block_Adminhtml_Catalog_Product_Attribute_Edit_Tab_Options();
            } else {
                $this->_navigationBlock = false;
            }
        }

        return $this->_navigationBlock;
    }

    public function getShowOptions()
    {
        return 10;
    }

    public function getUploader()
    {
        $uploader = $this->getLayout()->createBlock('core/template');

        $_modules      = Mage::getConfig()->getNode('modules')->children();
        $_modulesArray = (array)$_modules;
        if (!isset($_modulesArray['Mage_Uploader'])) {
            $uploader->setTemplate('gomage/productdesigner/catalog/product/attribute/uploader/flash.phtml');
        } else {
            $uploader->setTemplate('gomage/productdesigner/catalog/product/attribute/uploader/html.phtml');
        }
        $uploader->setParentBlock($this);
        return $uploader;
    }

}
