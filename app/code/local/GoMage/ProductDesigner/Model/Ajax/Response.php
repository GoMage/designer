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

class GoMage_ProductDesigner_Model_Ajax_Response extends Varien_Object
{
    const SUCCESS = 'success';

    const ERROR = 'error';

    const REDIRECT = 'redirect';

    public function success()
    {
        return $this->setStatus(self::SUCCESS);
    }

    public function error()
    {
        return $this->setStatus(self::ERROR);
    }

    public function redirect()
    {
        return $this->setStatus(self::REDIRECT);
    }

    public function asJson()
    {
        return Mage::helper('core')->jsonEncode($this->getData());
    }
}
