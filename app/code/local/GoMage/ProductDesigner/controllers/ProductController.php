<?php
//@TODO Remove secure injection posibility
class GoMage_ProductDesigner_ProductController extends Mage_Core_Controller_Front_Action
{
    public function dispatch($action) {
        $moduleEnabled = Mage::getStoreConfig('gmpd/general/enabled', Mage::app()->getStore());
        if(!$moduleEnabled) {
            $action = 'noRoute';
        }
        parent::dispatch($action);
    }

    public function editAction()
    {
        $request = $this->getRequest();
        $productId = $request->getParam("pid");
        $imageId = $request->getParam('img');

        $product = Mage::getModel('catalog/product')->load($productId);
        $images  = $product->getMediaGalleryImages();
        $image   = $images->getItemByColumnValue('value_id', $imageId);
        $imageUrl = Mage::helper('designer')->getDesignImageUrl($product, $image);
        $image->setUrl($imageUrl);
        list($imageWidth, $imageHeight) = Mage::helper('designer')->getImageDimensions($imageUrl);

        $settings = $product->getDesignAreas();

        if ($settings == null) {
            $settings = array();
        } else {
            $settings = $this->_jsonDecode($settings);
        }

        if (isset($settings[$imageId])) {
            $settings = $settings[$imageId];
        } else {
            $settings = array(
                't' => round($imageHeight / 2),
                'l' => round($imageWidth / 2),
                'h' => 100,
                'w' => 100,
                's' => 1,
                'ip' => 0,
            );
        }

        $this->loadLayout();

        $this->getLayout()
            ->getBlock("product_designer_product")
            ->setData("image", $image)
            ->setData("image_width", $imageWidth)
            ->setData("image_height", $imageHeight)
            ->setData('product_id', $productId)
            ->setData('image_id', $imageId)
            ->setData('settings', $settings);

        $this->renderLayout();
    }

    public function saveAction()
    {
        $params  = $this->getRequest()->getParams();

        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $product = Mage::getModel('catalog/product')->load($params['product_id']);
        $settings = $product->getDesignAreas();

        if ($settings == null) {
            $settings = array();
        } else {
            $settings = $this->_jsonDecode($settings);
        }

        $isActive = 0;
        if (isset($settings[$params['image_id']]['on'])) {
            $isActive = $settings[$params['image_id']]['on'];
        }

        $settings[$params['image_id']] = array(
            't'  => $params['t'], // offset top
            'l'  => $params['l'], // offset left
            'h'  => $params['h'], // design area height
            'w'  => $params['w'], // design area width
            's'  => $params['s'], // side type [front, back, left, right]
            'ip' => $params['initial_price'],
            'on' => $isActive,
        );

        $product->setDesignAreas(json_encode($settings))->save();

        exit("OK"); // TODO
    }

    public function updateStateAction()
    {
        $params    = $this->getRequest()->getParams();
        $productId = (int)$params['id'];
        $imageId   = (int)$params['image_id'];

        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $product  = Mage::getModel('catalog/product')->load($productId);
        $settings = $product->getDesignAreas();

        if ($settings == null) {
            $settings = array();
        } else {
            $settings = $this->_jsonDecode($settings);
        }

        if (isset($settings[$imageId])) {
            $settings[$imageId]['on'] = (int)$params['state'];
            $product->setDesignAreas(json_encode($settings))->save();
            exit("OK"); // TODO
        }

        exit('ERROR'); // TODO
    }

    protected function _jsonDecode($string)
    {
        return Mage::helper('designer')->jsonDecode($string);
    }
}