<?php 
/**
 * GoMage.com extension for Magento
 *
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
 * GoMage ProductDesigner navigation filters block
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @subpackage Block
 * @author     Roman Bublik <rb@gomage.com>
 */
class GoMage_ProductDesigner_Block_Designer_Navigation_Filters extends Mage_Core_Block_Template
{
    /**
     * Return available filter options
     *
     * @param string $filter Filter Name
     * @return Mage_Catalog_Model_Mysql_Category_Collection|null|Varien_Data_Collection
     */
    public function getAvailableFilterOptions($filter)
    {
        $filters = Mage::getSingleton('gmpd/navigation')->getFilterOptions($filter, $this->getRequest());
        return $filters;
    }

    /**
     * Return available filters
     *
     * @return array
     */
    public function getAvailableFilters()
    {
        $filters = array();
        $items = Mage::getSingleton('gmpd/navigation')->getAvailableFilters();
        foreach ($items as $item) {
            $filters[$item->getAttributeCode()] = $item->getFrontendLabel();
        }

        return array_merge(Mage::getSingleton('gmpd/navigation')->getAdditionalFilters(), $filters);
    }

    /**
     * Is filter option selected
     *
     * @param string $filter Filter Name
     * @param int    $value  Option value
     * @return bool
     */
    public function isFilterOptionSelected($filter, $value)
    {
        return $this->getRequest()->getParam($filter) == $value ? true : false;
    }
}
