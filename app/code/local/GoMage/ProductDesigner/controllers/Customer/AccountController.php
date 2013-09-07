<?php
require_once(Mage::getModuleDir('controllers', 'Mage_Customer')."/AccountController.php");
class GoMage_ProductDesigner_Customer_AccountController extends Mage_Customer_AccountController {
    public function createPostAction()
    {
        /**
         * @var $response Mage_Core_Controller_Response_Http
         * @var $result mixed
         * @var $customer Mage_Customer_Model_Customer
         * @var $url string
         */
        $response = $this->getResponse();
        $result = parent::createPostAction();
        if(!$this->hasError()) {
            if($customer = $this->_getSession()->getCustomer()) {
                $response->clearHeaders();
                $url = $this->_welcomeCustomer($customer);
                $response->setBody(
                    '<script type="text/javascript">
                    parent.w.config.isCustomerRegistered = true;
                    location.href = "'.$url.'";
                    </script>'
                );
            }
        }
        return $result;
    }

    protected function hasError() {
        $messages = $this->_getSession()->getMessages();
        foreach($messages->getItems() as $message) {
            if($message instanceof Mage_Core_Model_Message_Error) {
                return true;
            }
        }
    }
}