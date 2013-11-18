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
 * Catalog product configurable resource model
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @subpackage Model
 * @author     Roman Bublik <rb@gomage.com>
 */
class GoMage_ProductDesigner_Model_Mysql4_Catalog_Product_Type_Configurable
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Type_Configurable
{
    /**
     * Return product colors options
     *
     * @param int $productId Product Id
     * @return array
     */
    public function getProductColors($productId)
    {
        $colorAttributeCode = $attributeCode = Mage::getStoreConfig('gomage_designer/navigation/color_attribute');
        $attribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $colorAttributeCode);
        $statusAttribute = Mage::getSingleton('catalog/product_status')->getProductAttribute('status');
        $storeId = Mage::app()->getStore()->getId();
        if (!$attribute->getId()) {
            return array();
        }

        $select = $this->_getReadAdapter()->select()
            ->from(array('relations' => $this->getMainTable()), array())
            ->joinInner(
                array('attribute' => $attribute->getBackendTable()),
                "attribute.attribute_id = {$attribute->getId()} AND attribute.entity_id = relations.product_id",
                array()
            )->joinInner(
                array('attr_option' => $this->getTable('eav/attribute_option')),
                'attr_option.option_id = attribute.value',
                array('option_id')
            )->joinInner(
                array('stock' => $this->getTable('cataloginventory/stock_status')),
                'relations.product_id = stock.product_id',
                array()
            )->joinInner(
                array('status' => $statusAttribute->getBackendTable()),
                "status.attribute_id = {$statusAttribute->getId()} AND status.entity_id = relations.product_id",
                array()
            )->joinLeft(
                array('attr_value_store' => $this->getTable('eav/attribute_option_value')),
                "attr_option.option_id = attr_value_store.option_id and attr_value_store.store_id = {$storeId}",
                array()
            )->joinLeft(
                array('attr_value_default' => $this->getTable('eav/attribute_option_value')),
                "attr_option.option_id = attr_value_default.option_id and attr_value_default.store_id = 0",
                array()
            )->joinLeft(
                array('attr_value_image' => $this->getTable('gomage_designer/attribute_option')),
                "attr_option.option_id = attr_value_image.option_id",
                array('image' => 'attr_value_image.filename')
            )->columns(array(
                'value' => "IF (attr_value_store.value IS NOT NULL, attr_value_default.value, attr_value_store.value)"
            ))
            ->where("relations.parent_id = ?", $productId)
            ->where('stock.stock_status = ?', 1)
            ->where("status.value IN (?)", Mage::getSingleton('catalog/product_status')->getSaleableStatusIds())
            ->order('attr_option.sort_order')
            ->group('attr_option.option_id');

        $options = $this->_getReadAdapter()->fetchAll($select);

        return $options;
    }
}
