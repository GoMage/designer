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
 * @subpackage Model
 * @author     Roman Bublik <rb@gomage.com>
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
