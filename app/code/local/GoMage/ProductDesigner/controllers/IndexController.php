<?php

/**
 * GoMage.com
 *
 * GoMage ProductDesigner Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.0
 * @since        Class available since Release 1.0
 */
class GoMage_ProductDesigner_IndexController extends Mage_Core_Controller_Front_Action
{
    public function dispatch($action)
    {
        $moduleEnabled = Mage::getStoreConfig('gmpd/general/enabled', Mage::app()->getStore());
        if (!$moduleEnabled) {
            $action = 'noRoute';
        }
        parent::dispatch($action);
    }

    /**
     * Inititalize product
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _initializeProduct()
    {
        $request = $this->getRequest();
        $productId = $request->getParam("id", false);

        $product = Mage::getModel('catalog/product');
        if ($productId) {
            $product->load($productId);
        }
        Mage::register('current_product', $product);

        return $product;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initializeProduct();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Change product action
     *
     * @return void
     */
    public function changeProductAction()
    {
        $request = $this->getRequest();
        $isAjax = (bool) $request->getParam('ajax');
        if (!$isAjax) {
            $this->norouteAction();
            return;
        }

        $product = $this->_initializeProduct();
        $settings = Mage::helper('designer')->getProductSettingForEditor($product);
        $responseData = array('product_settings' => $settings);
        Mage::helper('designer/ajax')->sendSuccess($responseData);
    }

    /**
     * Filter products action
     *
     * @return void
     */
    public function filterProductsAction()
    {
        $isAjax = (bool) $this->getRequest()->getPost('ajax');
        if (!$isAjax) {
            $this->norouteAction();
            return;
        }

        $this->loadLayout();
        $navigationBlock = $this->getLayout()->getBlock('productNavigator');
        $responseData = array(
            'navigation_filters'  => $navigationBlock->getFiltersHtml(),
            'navigation_prodcuts' => $navigationBlock->getProductListHtml()
        );

        Mage::helper('designer/ajax')->sendSuccess($responseData);
    }

    /**
     * Filter cliparts action
     *
     * @return void
     */
    public function filterClipartsAction()
    {
        $isAjax = (bool) $this->getRequest()->getPost('ajax');
        if (!$isAjax) {
            $this->norouteAction();
            return;
        }

        $this->loadLayout();
        $designBlock = $this->getLayout()->getBlock('design');
        $responseData = array(
            'filters' => $designBlock->getChildHtml('design.filters'),
            'cliparts' => $designBlock->getChildHtml('design.cliparts')
        );
        Mage::helper('designer/ajax')->sendSuccess($responseData);
    }

    /**
     * Save product designed images and redirect to product view page
     *
     * @return void
     */
    public function continueAction()
    {
        /**
         * @var $request Mage_Core_Controller_Request_Http
         * @var $product Mage_Catalog_Model_Product
         */
        $request = $this->getRequest();
        $isAjax = $request->isAjax();
        if (!$isAjax) {
            $this->norouteAction();
            return;
        }
        try {
            $this->_saveDesign();
            $product = Mage::registry('current_product');
            Mage::helper('designer/ajax')->sendRedirect(array(
                'url' => $product->getProductUrl(),
            ));
        } catch (Exception $e) {
            Mage::helper('designer/ajax')->sendError(array(
                'message' => $e->getMessage()
            ));
        }
    }

    protected function _saveDesign()
    {
        $product = $this->_initializeProduct();
        $images = $this->getRequest()->getParam('images');

        if ($product->getId() && $images && !empty($images)) {
            $images = Mage::helper('core')->jsonDecode($images);
            Mage::getModel('gmpd/image')->createProductImages($images, $product);
        } elseif(!$product->getId()) {
            throw new Exception(Mage::helper('designer')->__('Product is not defined'));
        } elseif(!$images || empty($images)) {
            throw new Exception(Mage::helper('designer')->__('Designed images are empty'));
        }
    }

    public function uploadImagesAction()
    {
        $this->prepareDesignerSessionId();
        $files = @$_FILES['filesToUpload'];
        if (is_array($files)) {
            $files = $this->prepareFilesArray($files);
            $sessionId = $this->getSessionId();
            $customerId = (int) $this->_getCustomerSession()->getCustomerId();

            $baseMediaPath = Mage::getSingleton('gmpd/uploadedImage_config')->getBaseMediaPath();

            foreach ($files as $file) {
                $fileName = substr(sha1(microtime()), 0, 20) . $this->getConvertHelper()->format($file['name']);
                $fileDir = '/' . ($customerId ? $customerId : $sessionId) . '/';
                $destinationDir = $baseMediaPath . $fileDir;
                if (!file_exists($destinationDir)) {
                    mkdir($destinationDir);
                }
                $destinationFile = $destinationDir . $fileName;

                if (move_uploaded_file($file['tmp_name'], $destinationFile)) {
                    $imageData = array(
                        'image' => $fileDir . $fileName,
                        'customer_id' => $customerId,
                        'session_id' => $customerId ? $customerId : $sessionId
                    );
                    $uploadImage = Mage::getModel('gmpd/uploadedImage');
                    $uploadImage->setData($imageData);
                    $uploadImage->save();
                }
            }

            $this->loadLayout();
            $content = preg_replace('/\t+|\n+|\s{2,}/', '', $this->getLayout()->getBlock('uploadedImages')->toHtml());
            $this->getResponse()->setBody($content);
        }
    }

    public function saveDesignAction()
    {
        $request = $this->getRequest();
        $isAjax = $request->isAjax();
        if (!$isAjax) {
            $this->norouteAction();
            return;
        }

        try {
            $this->_saveDesign();
            Mage::helper('designer/ajax')->sendSuccess();
        } catch (Exception $e) {
            Mage::helper('designer/ajax')->sendError(array(
                'message' => $e->getMessage()
            ));
        }
    }

    public function loginAction()
    {
        $request = $this->getRequest();
        $isAjax = $request->isAjax();
        if (!$isAjax) {
            $this->norouteAction();
            return;
        }

        try {
            $customerSession = $this->_getCustomerSession();
            $login = $this->getRequest()->getParam('login');
            if (!$customerSession->isLoggedIn()) {
                if (!empty($login['username']) && !empty($login['password'])) {
                    $customerSession->login($login['username'], $login['password']);
                    $customer = $customerSession->getCustomer();
                    if ($customer->getIsJustConfirmed()) {
                        $customer->sendNewAccountEmail('confirmed', '', Mage::app()->getStore()->getId());
                    }
                    $this->_saveDesign();
                    Mage::helper('designer/ajax')->sendSuccess();
                } else {
                    throw new Exception($this->__('Login and password are required.'));
                }
            }
        } catch (Exception $e) {
            Mage::helper('designer/ajax')->sendError(array(
                'message' => $e->getMessage()
            ));
        } catch (Mage_Core_Exception $e) {
            switch ($e->getCode()) {
                case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                    $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                    $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                    break;
                case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                    $message = $e->getMessage();
                    break;
                default:
                    $message = $e->getMessage();
                Mage::helper('designer/ajax')->sendError(array(
                    'message' => $message
                ));
            }
        }
    }

    public function signupAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    protected function redirectTo404Page()
    {
        // TODO
        $this->getResponse()->setHeader('HTTP/1.1', '404 Not Found');
        $this->getResponse()->setHeader('Status', '404 File not found');

        $pageId = Mage::getStoreConfig('web/default/cms_no_route');

        if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
            $this->_forward('defaultNoRoute');
        }
    }

    protected function _jsonDecode($string)
    {
        return Mage::helper('designer')->jsonDecode($string);
    }

    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    protected function _getCoreSession()
    {
        return Mage::getSingleton('core/session');
    }

    protected function getConvertHelper()
    {
        return Mage::helper('designer/convert');
    }

    protected function prepareDesignerSessionId()
    {
        Mage::helper('designer')->prepareDesignerSessionId();
    }

    protected function getSessionId()
    {
        return $this->_getCustomerSession()->getDesignerSessionId();
    }

    protected function prepareFilesArray($files)
    {
        $filesArray = array();
        foreach ($files as $key => $values) {
            foreach ($values as $valueKey => $value) {
                $filesArray[$valueKey][$key] = $value;
            }
        }
        return $filesArray;
    }

}
