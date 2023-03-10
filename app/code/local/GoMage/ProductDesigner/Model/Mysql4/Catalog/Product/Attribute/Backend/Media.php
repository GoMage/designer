<?php
/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2018 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use/
 * @version      Release: 2.6.0
 * @since        Available since Release 1.0.0
 */

class GoMage_ProductDesigner_Model_Mysql4_Catalog_Product_Attribute_Backend_Media
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Backend_Media
{
    /**
     * Load gallery images for product
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Catalog_Model_Product_Attribute_Backend_Media $object
     * @return array
     */
    public function loadGallery($product, $object)
    {
        $adapter = $this->_getReadAdapter();

        $eventObjectWrapper = new Varien_Object(
            array(
                'product' => $product,
                'backend_attribute' => $object
            )
        );
        Mage::dispatchEvent(
            $this->_eventPrefix . '_load_gallery_before',
            array('event_object_wrapper' => $eventObjectWrapper)
        );

        if ($eventObjectWrapper->hasProductIdsOverride()) {
            $productIds = $eventObjectWrapper->getProductIdsOverride();
        } else {
            $productIds = array($product->getId());
        }

        // Select gallery images for product
        $select = $adapter->select()
            ->from(
                array('main'=>$this->getMainTable()),
                array('value_id', 'value AS file', 'product_id' => 'entity_id')
            )
            ->joinLeft(
                array('value' => $this->getTable(self::GALLERY_VALUE_TABLE)),
                $adapter->quoteInto('main.value_id = value.value_id AND value.store_id = ?', (int)$product->getStoreId()),
                array('label','position','disabled', 'color', 'design_area')
            )
            ->joinLeft( // Joining default values
                array('default_value' => $this->getTable(self::GALLERY_VALUE_TABLE)),
                'main.value_id = default_value.value_id AND default_value.store_id = 0',
                array(
                    'label_default' => 'label',
                    'position_default' => 'position',
                    'disabled_default' => 'disabled',
                    'color_default' => 'color',
                    'design_area_default' => 'design_area'
                )
            )
            ->where('main.attribute_id = ?', $object->getAttribute()->getId())
            ->where('main.entity_id IN (?)', $productIds)
            ->order('IF(value.position IS NULL, default_value.position, value.position) ASC');

        $result = $adapter->fetchAll($select);
        $this->_removeDuplicates($result);
        return $result;
    }
}
