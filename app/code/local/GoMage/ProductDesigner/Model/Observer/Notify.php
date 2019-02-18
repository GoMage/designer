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
class GoMage_ProductDesigner_Model_Observer_Notify
{
	public function notify($event)
    {
		if (Mage::getSingleton('admin/session')->isLoggedIn()
            && Mage::getStoreConfig('gomage_notification/notification/enable')) {
			Mage::helper('gomage_designer')->notify();
		}
	}

}