<?php

/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2015 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use/
 * @version      Release: 2.3.0
 * @since        Available since Release 1.0.0
 */
class GoMage_ProductDesigner_Block_Page_Html_Head extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        if (Mage::helper('gomage_designer')->isEnabled()) {
            $head = $this->getLayout()->getBlock('head');
            $head->addItem('skin_css', 'css/gomage/productdesigner.css');
        }

        return parent::_prepareLayout();
    }
}
