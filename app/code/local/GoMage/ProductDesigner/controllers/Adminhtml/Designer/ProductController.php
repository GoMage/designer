<?php
class GoMage_ProductDesigner_Adminhtml_Designer_ProductController
    extends Mage_Adminhtml_Controller_Action
{
    public function dispatch($action)
    {
        if(!Mage::helper('designer')->isEnabled()) {
            $action = 'noRoute';
        }
        parent::dispatch($action);
    }

    /**
     * Initialize product
     *
     * @return false|Mage_Catalog_Model_Product
     */
    protected function _initializeProduct()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $product = Mage::getModel('catalog/product');

        if ($productId) {
            $product->load($productId);
        }
        Mage::register('product', $product);
        return $product;
    }

    /**
     * Edit Design area action
     *
     * @return void
     */
    public function editAction()
    {
        $product = $this->_initializeProduct();
        if ($product->getId()) {
            $image = $this->_initializeProductImage($product);
            if ($image && $image->getId()) {
                $html = $this->_getProductEditHtml();
                Mage::helper('designer/ajax')->sendSuccess(array(
                    'design_area' => $html
                ));
            } else {
                Mage::helper('designer/ajax')->sendError(Mage::helper('designer')->__('You can not choose this image for design'));
            }
        }
    }

    /**
     * Return design area edit block html
     *
     * @return string
     */
    protected function _getProductEditHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('adminhtml_designer_product_edit');
        $layout->generateXml();
        $layout->generateBlocks();
        return $layout->getOutput();
    }

    /**
     * Initialize product image
     *
     * @param Mage_Catalog_Model_Product $product Product
     * @return bool|Varien_Object
     */
    protected function _initializeProductImage($product)
    {
        $imageId = $this->getRequest()->getParam('img');
        if ($product->getId() && $imageId) {
            $images  = $product->getMediaGalleryImages(true);
            $image   = $images->getItemByColumnValue('value_id', $imageId);
            if ($image && $image->getId()) {
                Mage::register('current_image', $image);
                return $image;
            }
        }

        return false;
    }

    /**
     * Save design area action
     *
     * @return void
     */
    public function saveAction()
    {
        $product = $this->_initializeProduct();

        try {
            if (!$product || !$product->getId()){
                throw new Exception(Mage::helper('designer')->__('Product with id %d not found', $this->getRequest()->getParam('product_id')));
            }
            $product->setDesignAreas(Mage::helper('core')
                ->jsonEncode($this->_prepareDesignAreaSettings($product))
            )->save();
            Mage::helper('designer/ajax')->sendSuccess();
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('designer/ajax')->sendError($e->getMessage());
        }
    }

    /**
     * Prepare design area settings
     *
     * @param Mage_Catalog_Model_Product $product Product
     * @return array
     */
    protected function _prepareDesignAreaSettings($product)
    {
        $params  = $this->getRequest()->getParams();
        $settings = $product->getDesignAreas();

        if ($settings == null) {
            $settings = array();
        } else {
            $settings = Mage::helper('designer')->jsonDecode($settings);
        }

        $settings[$params['image_id']] = array(
            't'  => $params['t'], // offset top
            'l'  => $params['l'], // offset left
            'h'  => $params['h'], // design area height
            'w'  => $params['w'], // design area width
            's'  => $params['s'], // side type [front, back, left, right]
            'ip' => $params['initial_price']
        );

        return $settings;
    }

    /**
     * Update state action
     *
     * @return void
     */
    public function updateStateAction()
    {
        $productId = (int) $this->getRequest()->getParam('product_id');
        $imageId   = (int) $this->getRequest()->getParam('image_id');
        $state = (int) $this->getRequest()->getParam('state');

        try {
            $product  = $this->_initializeProduct();
            if ($product && $product->getId()) {
                $settings = $product->getDesignAreas();
                if (!$settings) {
                    $settings = array();
                } else {
                    $settings = Mage::helper('designer')->jsonDecode($settings);
                }
                if (isset($settings[$imageId]) && !$state) {
                    unset($settings[$imageId]);
                    $product->setDesignAreas(Mage::helper('core')->jsonEncode($settings))->save();
                }
                Mage::helper('designer/ajax')->sendSuccess();
            } else {
                throw new Exception(Mage::helper('designer')->__('Product with id %d not found', $productId));
            }
        } catch (Exception $e) {
            Mage::helper('designer/ajax')->sendError($e->getMessage());
        }
    }
}