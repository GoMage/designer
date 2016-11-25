<?php
/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2016 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use/
 * @version      Release: 2.4.0
 * @since        Available since Release 1.0.0
 */

class GoMage_ProductDesigner_Block_Designer_Navigation_Product_List extends Mage_Catalog_Block_Product_List
{
    protected $_productCollection;
    /**
     * Returns product collection.
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection|Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getProductCollection()
    {
        if(is_null($this->_productCollection)) {
            $this->_productCollection = Mage::getSingleton('gomage_designer/navigation')->getProductCollection();
        }
        return $this->_productCollection;
    }
}
