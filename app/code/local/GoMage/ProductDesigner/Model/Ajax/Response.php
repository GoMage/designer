<?php

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
