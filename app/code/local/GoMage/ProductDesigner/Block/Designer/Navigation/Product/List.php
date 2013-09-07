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
class GoMage_ProductDesigner_Block_Designer_Navigation_Product_List extends Mage_Catalog_Block_Product_List
{
    /**
     * Returns product collection.
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection|Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getProductCollection()
    {
        /* @var $collection     Mage_Catalog_Model_Resource_Product_Collection */
        if(is_null($this->_productCollection)) {
            $this->_productCollection = Mage::getSingleton('gmpd/navigation')->getProductCollection();
        }
        return $this->_productCollection;
    }
}