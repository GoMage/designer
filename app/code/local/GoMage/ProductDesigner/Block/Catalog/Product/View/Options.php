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
 * Short description of the class
 *
 * Long description of the class (if any...)
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @subpackage Block
 * @author     Roman Bublik <rb@gomage.com>
 */
class GoMage_ProductDesigner_Block_Catalog_Product_View_Options
    extends Mage_Catalog_Block_Product_View_Options
{
    protected $_designOption;

    public function getDesignOption()
    {
        if (is_null($this->_designOption)) {
            $designId = (int) $this->getRequest()->getParam('design_id', false);
            if ($designId) {
                $design = Mage::getModel('gmpd/design')->load($designId);
                if ($design->getId() && $design->getProductId() == $this->getProduct()->getId()) {
                    $option = new Mage_Catalog_Model_Product_Option(array(
                        'id' => $design->getId(),
                        'value' => $design->getId(),
                        'price' => $design->getPrice(),
                        'price_type' => 'fixed',
                    ));

                    if (!$this->hasOptions()) {
                        $option->setData('decorated_is_last', true);
                    }
                    $option->setProduct($this->getProduct());
                    $priceConfig = $this->_getPriceConfiguration($option);
                    $option->setPriceConfig(
                        Mage::helper('core')->jsonEncode($priceConfig)
                    );

                    $this->_designOption = $option;
                }
            } else {
                $this->_designOption = false;
            }
        }

        return $this->_designOption;
    }

    public function getDesignOptionHtml()
    {
        if ($option = $this->getDesignOption()) {
            $block = $this->getChild('product_option_design');
            $block->setOption($option);
            return $block->toHtml();
        }

        return '';
    }

    public function hasDesignOption()
    {
        $option = $this->getDesignOption();
        return $option ? true : false;
    }
}
