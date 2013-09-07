<?php
class GoMage_ProductDesigner_Helper_Ajax extends Mage_Core_Helper_Data
{
    public function sendResponse(GoMage_ProductDesigner_Model_Ajax_Response $response)
    {
        Mage::app()->getResponse()->setHeader('Content-type', 'application/json');
        return Mage::app()->getResponse()->setBody($response->asJson());
    }

    public function sendError($message)
    {
        return $this->sendResponse(
            Mage::getModel('gmpd/ajax_response')->error()->setMessage($message)
        );
    }

    public function sendSuccess($data = array())
    {
        return $this->sendResponse(
            Mage::getModel('gmpd/ajax_response')->success()->addData($data)
        );
    }

    public function sendRedirect($data = array())
    {
        return $this->sendResponse(
            Mage::getModel('gmpd/ajax_response')->redirect()->addData($data)
        );
    }
}
