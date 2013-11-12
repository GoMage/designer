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
        $moduleEnabled = Mage::getStoreConfig('gomage_designer/general/enabled', Mage::app()->getStore());
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
        $product = $this->_initializeProduct();
        if ($product->getId() && (!$product->getEnableProductDesigner() || !$product->hasImagesForDesign())
            && !Mage::helper('designer')->isNavigationEnabled()) {
            $this->_redirectReferer();
        } elseif (!$product->getId() && !Mage::helper('designer')->isNavigationEnabled()) {
            $this->_redirectReferer();
        }
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle(Mage::getStoreConfig('design/head/default_title'));
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

        try {
            $product = $this->_initializeProduct();
            if (!$product || !$product->getId()) {
                throw new Exception(Mage::helper('designer')->__('Product with id %d not found',
                    $this->getRequest()->getParam('id')));
            }
            $settings = Mage::helper('designer')->getProductSettingForEditor($product);
            $responseData = array(
                'product_settings' => $settings,
                'design_price' => $this->_getDesignPriceHtml(),
                'price_config' => Mage::helper('designer')->getProductPriceConfig(),
            );
            if ($productColors = $product->getProductColors()) {
                $responseData['product_colors'] = $productColors;
            }
            Mage::helper('designer/ajax')->sendSuccess($responseData);
        } catch(Exception $e) {
            Mage::helper('designer/ajax')->sendError($e->getMessage());
        }
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
            $design = $this->_saveDesign();
            $product = Mage::registry('product');
            Mage::helper('designer/ajax')->sendRedirect(array(
                'url' => $product->getDesignedProductUrl($design->getId()),
                'design_id' => $design->getId()
            ));
        } catch (Exception $e) {
            Mage::helper('designer/ajax')->sendError($e->getMessage());
            Mage::logException($e);
        }
    }

    protected function _saveDesign()
    {
        return Mage::helper('designer')->saveProductDesignedImages();
    }

    public function uploadImagesAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->getBlock('uploadedImages');
        $baseMediaPath = Mage::getSingleton('gmpd/uploadedImage_config')->getBaseMediaPath();
        $allowedFormatsString = Mage::getStoreConfig('gomage_designer/upload_image/format');
        $maxUploadFileSize = Mage::getStoreConfig('gomage_designer/upload_image/size');
        $allowedFormats = explode(',', $allowedFormatsString);
        $sessionId = $this->getSessionId();
        $customerId = (int) $this->_getCustomerSession()->getCustomerId();
        $uploadedFilesCount = 0;
        $errors = array();

        try {
            if (!isset($_FILES['filesToUpload'])) {
                throw new Exception(Mage::helper('designer')->__('Please, select files for upload'));
            }
            $files = $this->prepareFilesArray($_FILES['filesToUpload']);
            foreach ($files as $file) {
                if (!$file['name']) {
                    continue;
                }
                if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE
                    || $file['size'] > $maxUploadFileSize * 1024 * 1024) {
                    $errors['size'] = Mage::helper('designer')->__('You can not upload files larger than %d MB', $maxUploadFileSize);
                    continue;
                }

                $imageExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($imageExtension, $allowedFormats)) {
                    $errors['type'] = Mage::helper('designer')->__('Cannot upload the file. The format is not supported. Supported file formats are: %s', $allowedFormatsString);
                    continue;
                }

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
                    $uploadedFilesCount++;
                }
            }

            if (!empty($errors)) {
                throw new Exception(implode("\n", $errors));
            }
            if ($uploadedFilesCount == 0) {
                throw new Exception(Mage::helper('designer')->__('Please, select files for upload'));
            }

        } catch (Exception $e) {
            $block->setError($e->getMessage());
        }

        $content = preg_replace('/\t+|\n+|\s{2,}/', '', $block->toHtml());
        $this->getResponse()->setBody($content);
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
            $design = $this->_saveDesign();
            Mage::helper('designer/ajax')->sendSuccess(array('design_id' => $design->getId()));
        } catch (Exception $e) {
            Mage::helper('designer/ajax')->sendError($e->getMessage());
        }
    }

    protected function _getDesignPriceHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('gomage_designer_design_price');
        $layout->generateXml();
        $layout->generateBlocks();
        return $layout->getOutput();
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
