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

class GoMage_ProductDesigner_Model_Mysql4_Catalog_Product extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product
{
    /**
     * Return assigned images for specific stores
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int|array $storeIds
     * @return array
     *
     */
    public function getAssignedImages($product, $storeIds)
    {
        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }

        $mainTable = $product->getResource()->getAttribute('image')
            ->getBackend()
            ->getTable();
        $read      = $this->_getReadAdapter();
        $select    = $read->select()
            ->from(
                array('images' => $mainTable),
                array('value as filepath', 'store_id')
            )
            ->joinLeft(
                array('attr' => $this->getTable('eav/attribute')),
                'images.attribute_id = attr.attribute_id',
                array('attribute_code')
            )
            ->where('entity_id = ?', $product->getId())
            ->where('store_id IN (?)', $storeIds)
            ->where('attribute_code IN (?)', array('small_image', 'thumbnail', 'image'));

        $images = $read->fetchAll($select);
        return $images;
    }
}
