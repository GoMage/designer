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

class GoMage_ProductDesigner_Block_Customer_Account_Navigation_Design extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        if (Mage::helper('gomage_designer')->isEnabled()) {
            $navigation = $this->getLayout()->getBlock('customer_account_navigation');
            $navigation->addLink('designs', 'gomage_designer/customer/designs/',
                Mage::helper('gomage_designer')->__('My Saved Designs'));
        }

        return parent::_prepareLayout();
    }
}
