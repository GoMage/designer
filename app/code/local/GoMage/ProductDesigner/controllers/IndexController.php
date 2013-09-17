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
        return Mage::helper('designer')->initializeProduct();
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
            Mage::helper('designer/ajax')->sendError($e->getMessage());
        }
    }

    protected function _saveDesign()
    {
        Mage::helper('designer')->saveProductDesignedImages();
    }

    public function uploadImagesAction()
    {
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
            Mage::helper('designer/ajax')->sendError($e->getMessage());
        }
    }

    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    protected function getConvertHelper()
    {
        return Mage::helper('designer/convert');
    }

    protected function getSessionId()
    {
        return $this->_getCustomerSession()->getEncryptedSessionId();
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
