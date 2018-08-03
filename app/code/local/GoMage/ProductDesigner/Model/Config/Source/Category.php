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
class GoMage_ProductDesigner_Model_Config_Source_Category
{
    public function toOptionArray()
    {
        $collection = Mage::getResourceModel('catalog/category_collection');

        $collection->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active')
            ->setOrder('path')
            ->load();

        $options = array(array(
            'label' => Mage::helper('adminhtml')->__('-- Please Select a Category --'),
            'value' => ''
        ));
        foreach ($collection as $category) {
            if ($category->getIsActive() && $category->getId() != Mage_Catalog_Model_Category::TREE_ROOT_ID) {
                $options[] = array(
                    'label' => str_repeat('-', $category->getLevel() - 1) . $category->getName(),
                    'value' => $category->getId()
                );
            }
        }

        return $options;
    }
}
