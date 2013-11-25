<?php 
/**
 * GoMage.com extension for Magento
 *
 * Long description of this file (if any...)
 *
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the GoMage ProductDesigner module to newer versions in the future.
 * If you wish to customize the GoMage ProductDesigner module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @copyright  Copyright (C) 2013 GoMage.com (http://www.gomage.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer designs block
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @subpackage Block
 * @author     Roman Bublik <rb@gomage.com>
 */
class GoMage_ProductDesigner_Block_Customer_Designs extends Mage_Catalog_Block_Product_Abstract
{
    protected $_designCollection;

    protected $_pagerBlock;

    protected $_defaultColumnCount = 4;

    /**
     * Return design collection
     *
     * @return GoMage_ProductDesigner_Model_Mysql4_Design_Collection
     */
    public function getDesignCollection()
    {
        return $this->_initDesignCollection();
    }

    /**
     * Initialize design collection
     *
     * @return GoMage_ProductDesigner_Model_Mysql4_Design_Collection
     */
    protected function _initDesignCollection()
    {
        if (is_null($this->_designCollection)) {
            $customerId = $this->getCustomerId();
            $collection = Mage::getModel('gomage_designer/design')->getCollection()
                ->getCustomerDesignCollection($customerId)
                ->setOrder('created_date');
            $pager = $this->getPager();
            $pager->setCollection($collection);
            $collection->addProductsToCollection();
            $this->_designCollection = $collection;
        }

        return $this->_designCollection;
    }

    /**
     * Get pager block instance
     *
     * @return Mage_Page_Block_Html_Pager
     */
    public function getPager()
    {
        if (is_null($this->_pagerBlock)) {
            $pager = $this->getChild('pager');
            $pager->setAvailableLimit(array(12=>12, 24=>24, 48=>48));
            $this->_pagerBlock = $pager;
        }
        return $this->_pagerBlock;
    }

    /**
     * Return pager html
     *
     * @return string
     */
    public function getPagerHtml()
    {
        $this->_initDesignCollection();
        return $this->getPager()->toHtml();
    }

    /**
     * Return Customer id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return Mage::getSingleton('customer/session')->getCustomerId();
    }

    /**
     * Return image
     *
     * @param GoMage_ProductDesigner_Model_Design $design Design
     * @return Varien_Image
     */
    public function getImage($design)
    {
        return Mage::helper('gomage_designer/image')->init($design->getImage())->resize(135);
    }

    /**
     * Retrieve url for add product to cart
     * Will return product view page URL if product has required options
     *
     * @param Mage_Catalog_Model_Product $product    Product
     * @param array                      $additional Additional params
     * @return string
     */
    public function getAddToCartUrl($product, $additional = array())
    {
        if ($product->getTypeInstance(true)->hasRequiredOptions($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = array();
            }
            $additional['_query']['options'] = 'cart';
            if (isset($additional['design'])) {
                $additional['_query']['design_id'] = $additional['design'];
                unset($additional['design_id']);
            }

            return $this->getProductUrl($product, $additional);
        }
        return $this->helper('checkout/cart')->getAddUrl($product, $additional);
    }

    /**
     * Return delete url
     *
     * @param int $designId Design Id
     * @return string
     */
    public function getDeleteUrl($designId)
    {
        return $this->getUrl('*/customer/deleteDesign', array('design_id' => $designId));
    }

    public function setColumnCount($count)
    {
        if (Mage::getEdition() == Mage::EDITION_ENTERPRISE) {
            $count = 3;
        }

        $this->setData('column_count', $count);

        return $this;
    }
}
