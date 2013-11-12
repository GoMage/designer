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
class GoMage_ProductDesigner_Block_Adminhtml_Catalog_Product_Attribute_Edit_Tab_Options
    extends Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Options
{
    public function __construct()
    {
        parent::__construct();
        if (!Mage::helper('gomage_designer')->isEnabled()) {
            return;
        }
        $colorAttributeCode = Mage::getStoreConfig('gomage_designer/navigation/color_attribute');
        if ($colorAttributeCode && ($this->getAttributeObject()->getAttributeCode() == $colorAttributeCode)) {
            $this->setTemplate('gomage/catalog/product/attribute/options.phtml');
        }

    }

    public function getOptionValues()
    {
        $values = parent::getOptionValues();
        $colorAttributeCode = Mage::getStoreConfig('gomage_designer/navigation/color_attribute');
        if (!$colorAttributeCode || !($this->getAttributeObject()->getAttributeCode() == $colorAttributeCode)) {
            return $values;
        }
        if ($values) {
            $images = $this->getAttributeObject()->getOptionImages();
            foreach ($values as $value) {
                if (isset($images[$value['id']])) {
                    $value->setImageInfo(array($images[$value['id']]));
                }
            }
        }

        return $values;

    }
}
